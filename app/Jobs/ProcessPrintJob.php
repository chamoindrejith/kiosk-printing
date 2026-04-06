<?php

namespace App\Jobs;

use App\Models\PrintJob;
use App\Services\Printers\GenericWifiPrinterAdapter;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessPrintJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 900;

    private int $printJobId;

    public function __construct(int $printJobId)
    {
        $this->printJobId = $printJobId;
    }

    public function tries(): int
    {
        return (int) config('printers.max_retries', 3);
    }

    public function backoff(): int
    {
        return (int) config('printers.poll_interval', 5);
    }

    public function handle(GenericWifiPrinterAdapter $printerAdapter): void
    {
        $printJob = PrintJob::with(['printer', 'pages'])->find($this->printJobId);

        if (! $printJob) {
            Log::error("Print job not found: {$this->printJobId}");

            return;
        }

        if (! in_array($printJob->status, ['queued', 'dispatching', 'printing', 'paused'])) {
            Log::warning('Print job in invalid state for processing', [
                'job_id' => $printJob->id,
                'status' => $printJob->status,
            ]);

            return;
        }

        $filePath = storage_path("app/{$printJob->file_path}");

        if (! is_file($filePath) || ! is_readable($filePath)) {
            $message = "Print file missing or unreadable: {$filePath}";

            Log::error($message, ['job_id' => $printJob->id]);

            $printJob->update([
                'status' => 'failed',
                'error_message' => $message,
            ]);
            $printJob->recordEvent('dispatch_failed', ['error' => $message]);

            return;
        }

        $printer = $printJob->printer;

        if (! $printerAdapter->isReachable($printer)) {
            $printJob->update(['status' => 'paused', 'error_message' => 'Printer unreachable']);
            $printJob->recordEvent('printer_unreachable');

            return;
        }

        $printJob->update(['status' => 'dispatching']);

        try {
            $options = [
                'color' => $printJob->color,
                'duplex' => $printJob->duplex,
                'paper_size' => $printJob->paper_size,
                'copies' => $printJob->copies,
                'page_range' => $printJob->page_range,
            ];

            $externalJobId = $printerAdapter->submitJob($printer, $filePath, $options);

            $printJob->recordEvent('job_dispatched', ['external_job_id' => $externalJobId]);

            // For raw protocol printers (true fire-and-forget), mark as completed immediately
            if ($printer->protocol === 'raw' || (int) ($printer->port ?? 631) === 9100) {
                $printJob->update([
                    'external_job_id' => $externalJobId,
                    'status' => 'completed',
                    'last_confirmed_page' => $printJob->effective_page_count,
                    'printed_at' => now(),
                ]);
                $printJob->recordEvent('job_completed');

                return;
            }

            // For printers where we can poll status, use polling
            if ($this->canPollJobStatus($externalJobId)) {
                $printJob->update([
                    'external_job_id' => $externalJobId,
                    'status' => 'printing',
                    'error_message' => null,
                ]);

                $this->pollForCompletion($printJob->fresh(['printer', 'pages']), $printerAdapter);

                return;
            }

            // For jobs where polling isn't available (socket_* or printjob_*),
            // keep in printing state with a note that status monitoring is unavailable
            $printJob->update([
                'external_job_id' => $externalJobId,
                'status' => 'printing',
                'error_message' => 'Status monitoring unavailable for this printer',
            ]);
            $printJob->recordEvent('job_dispatched_no_polling', [
                'external_job_id' => $externalJobId,
                'job_type' => $this->getJobIdType($externalJobId),
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to dispatch print job', [
                'job_id' => $printJob->id,
                'error' => $e->getMessage(),
            ]);

            $printJob->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            $printJob->recordEvent('dispatch_failed', ['error' => $e->getMessage()]);
        }
    }

    private function pollForCompletion(PrintJob $printJob, GenericWifiPrinterAdapter $printerAdapter): void
    {
        $attempts = 0;
        $maxAttempts = (int) config('printers.status_max_polls', 30);
        $delay = (int) config('printers.poll_interval', 5);
        $unknownStatusLimit = (int) config('printers.unknown_status_limit', 3);
        $unknownStatusCount = 0;

        while ($attempts < $maxAttempts) {
            sleep($delay);
            $attempts++;

            $status = $printerAdapter->getJobStatus($printJob->printer, $printJob->external_job_id);

            if (($status['status'] ?? 'unknown') === 'unknown') {
                $unknownStatusCount++;

                if ($unknownStatusCount >= $unknownStatusLimit) {
                    $printJob->update([
                        'status' => 'paused',
                        'error_message' => 'Printer status endpoint unavailable',
                    ]);
                    $printJob->recordEvent('job_status_unavailable', [
                        'attempts' => $attempts,
                        'job_id' => $printJob->external_job_id,
                    ]);

                    return;
                }
            } else {
                $unknownStatusCount = 0;
            }

            $this->updatePageProgress($printJob, $status);

            if (in_array($status['status'], ['completed', 'failed', 'cancelled'])) {
                if ($status['status'] === 'completed') {
                    $printJob->update([
                        'status' => 'completed',
                        'last_confirmed_page' => $printJob->effective_page_count,
                        'printed_at' => now(),
                    ]);
                    $printJob->recordEvent('job_completed');
                } else {
                    $printJob->update([
                        'status' => 'failed',
                        'error_message' => 'Print job failed on printer',
                    ]);
                    $printJob->recordEvent('job_failed', ['status' => $status]);
                }

                return;
            }

            Log::debug('Print job progress', [
                'job_id' => $printJob->id,
                'status' => $status['status'],
                'progress' => $status['progress'] ?? 0,
                'attempt' => $attempts,
            ]);
        }

        $printJob->update(['status' => 'paused', 'error_message' => 'Timeout waiting for completion']);
        $printJob->recordEvent('job_timeout');
    }

    private function canPollJobStatus(string $externalJobId): bool
    {
        // Socket jobs from raw printing cannot be polled
        if (str_starts_with($externalJobId, 'socket_')) {
            return false;
        }

        // lp_ job IDs come from CUPS/lp which can be polled via lpstat
        if (str_starts_with($externalJobId, 'lp_')) {
            return true;
        }

        // UUID/random fallback IDs from direct IPP responses are not generally pollable.
        // These indicate the printer didn't return a recognized job ID.
        if (str_starts_with($externalJobId, 'printjob_')) {
            return false;
        }

        // Numeric job IDs from IPP responses are typically pollable
        return true;
    }

    private function updatePageProgress(PrintJob $printJob, array $status): void
    {
        $printerAdapter = app(GenericWifiPrinterAdapter::class);

        if ($printerAdapter->supportsPageConfirmation()) {
            return;
        }

        $progress = $status['progress'] ?? 0;
        $totalPages = $printJob->effective_page_count;

        if ($totalPages > 0 && $progress > 0) {
            $confirmedPages = (int) ceil(($progress / 100) * $totalPages);

            $printJob->pages()
                ->where('status', 'pending')
                ->where('sequence_order', '<=', $confirmedPages)
                ->update([
                    'status' => 'confirmed',
                    'confirmed_at' => now(),
                ]);

            $printJob->update(['last_confirmed_page' => $confirmedPages]);
        }
    }

    public function failed(\Throwable $exception): void
    {
        $printJob = PrintJob::find($this->printJobId);

        if ($printJob) {
            $printJob->update([
                'status' => 'failed',
                'error_message' => $exception->getMessage(),
            ]);
            $printJob->recordEvent('job_failed', ['error' => $exception->getMessage()]);
        }
    }

    private function getJobIdType(string $jobId): string
    {
        if (str_starts_with($jobId, 'socket_')) {
            return 'raw_socket';
        }
        if (str_starts_with($jobId, 'lp_')) {
            return 'cups_queue';
        }
        if (str_starts_with($jobId, 'printjob_')) {
            return 'unknown_fallback';
        }
        return 'ipp_numeric';
    }
}
