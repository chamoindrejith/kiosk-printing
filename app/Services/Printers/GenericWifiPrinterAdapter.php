<?php

namespace App\Services\Printers;

use App\Models\Printer;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class GenericWifiPrinterAdapter implements PrinterGatewayInterface
{
    private Client $client;
    private int $timeout;
    private int $connectTimeout;

    public function __construct()
    {
        $this->client = new Client([
            'timeout' => config('printers.timeout', 30),
            'connect_timeout' => config('printers.connect_timeout', 10),
        ]);
        $this->timeout = config('printers.timeout', 30);
        $this->connectTimeout = config('printers.connect_timeout', 10);
    }

    public function isReachable(Printer $printer): bool
    {
        $host = $printer->ip_address;
        $port = $printer->port ?? 631;

        if (!$host) {
            return false;
        }

        // Reachability should only check TCP connectivity. HTTP 401/403 still means the device is reachable.
        $socket = @fsockopen($host, $port, $errno, $errstr, $this->connectTimeout);
        if ($socket) {
            fclose($socket);
            return true;
        }

        Log::warning("Printer unreachable: {$printer->ip_address}", [
            'printer_id' => $printer->id,
            'port' => $port,
            'error' => $errstr ?? 'socket_open_failed',
        ]);
        return false;
    }

    public function getCapabilities(Printer $printer): array
    {
        return [
            'color' => true,
            'duplex' => true,
            'paper_sizes' => ['A4', 'A5', 'Letter', 'Legal'],
            'max_resolution' => 1200,
            'page_confirmation' => $this->supportsPageConfirmation(),
        ];
    }

    public function submitJob(Printer $printer, string $filePath, array $options): string
    {
        $host = $printer->ip_address;
        $port = $printer->port ?? 631;
        $ippPath = '/ipp/print';

        $pdfContent = file_get_contents($filePath);
        if ($pdfContent === false) {
            throw new \Exception("Failed to read PDF file: {$filePath}");
        }

        try {
            if ($printer->protocol === 'raw' || $port === 9100) {
                $allowRawPdf = (bool) config('printers.allow_raw_pdf', false);

                try {
                    // Prefer CUPS/lpr for PDFs to avoid sending unsupported raw PDF streams.
                    $jobId = $this->submitViaSystemPrint($printer, $filePath, $options);
                } catch (\Exception $e) {
                    Log::warning('System print failed for raw printer', [
                        'printer_id' => $printer->id,
                        'error' => $e->getMessage(),
                        'allow_raw_pdf' => $allowRawPdf,
                    ]);

                    if (!$allowRawPdf) {
                        throw new \Exception(
                            'Raw PDF printing is disabled. Configure printer for IPP/CUPS queue or set PRINTER_ALLOW_RAW_PDF=true to allow raw fallback.'
                        );
                    }

                    try {
                        $jobId = $this->submitRawJob($host, $port, $pdfContent);
                    } catch (\Exception $rawException) {
                        throw new \Exception(
                            'System print failed and raw fallback also failed: ' . $rawException->getMessage()
                        );
                    }

                    if (!$jobId) {
                        throw $e;
                    }
                }
            } else {
                try {
                    $response = $this->client->request('POST', "http://{$host}:{$port}{$ippPath}", [
                        'headers' => [
                            'Content-Type' => 'application/pdf',
                            'Content-Length' => strlen($pdfContent),
                            'User-Agent' => 'PrintKiosk/1.0',
                        ],
                        'body' => $pdfContent,
                        'query' => $this->buildPrintOptions($options),
                    ]);

                    $jobId = $this->extractJobId($response->getBody()->getContents());
                } catch (\Exception $e) {
                    // Many office printers reject direct IPP POST from custom clients.
                    // Fall back to system print utilities when available.
                    $jobId = $this->submitViaSystemPrint($printer, $filePath, $options, $e->getMessage());
                }
            }
            
            Log::info("Print job submitted", [
                'printer_id' => $printer->id,
                'job_id' => $jobId,
                'file' => $filePath,
            ]);

            return $jobId;
        } catch (\Exception $e) {
            Log::error("Failed to submit print job", [
                'printer_id' => $printer->id,
                'file' => $filePath,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function getJobStatus(Printer $printer, string $jobId): array
    {
        $host = $printer->ip_address;
        $port = $printer->port ?? 631;
        $printerName = $printer->name;

        try {
            $response = $this->client->request('GET', "http://{$host}:{$port}/jobs/{$jobId}", [
                'timeout' => $this->timeout,
            ]);

            return $this->parseJobStatus($response->getBody()->getContents());
        } catch (\Exception $e) {
            Log::warning("Failed to get job status", [
                'job_id' => $jobId,
                'error' => $e->getMessage(),
            ]);
            return ['status' => 'unknown', 'message' => $e->getMessage()];
        }
    }

    public function cancelJob(Printer $printer, string $jobId): bool
    {
        $host = $printer->ip_address;
        $port = $printer->port ?? 631;

        try {
            $this->client->request('DELETE', "http://{$host}:{$port}/jobs/{$jobId}");
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to cancel job", [
                'job_id' => $jobId,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    public function supportsPageConfirmation(): bool
    {
        return false;
    }

    private function buildPrintOptions(array $options): array
    {
        $query = [];
        
        if (isset($options['color']) && !$options['color']) {
            $query['print-color-mode'] = 'monochrome';
        }
        
        if (isset($options['duplex']) && $options['duplex']) {
            $query['sides'] = 'two-sided-long-edge';
        } else {
            $query['sides'] = 'one-sided';
        }

        if (isset($options['paper_size'])) {
            $query['media'] = $options['paper_size'];
        }

        if (isset($options['copies'])) {
            $query['copies'] = $options['copies'];
        }

        return $query;
    }

    private function extractJobId(string $response): string
    {
        if (preg_match('/<job id="(\d+)"/', $response, $matches)) {
            return $matches[1];
        }
        return uniqid('printjob_');
    }

    private function parseJobStatus(string $response): array
    {
        $status = 'unknown';
        $progress = 0;

        if (preg_match('/<job.*state="([^"]+)"/', $response, $matches)) {
            $stateMap = [
                'pending' => 'pending',
                'held' => 'held',
                'processing' => 'printing',
                'stopped' => 'paused',
                'completed' => 'completed',
                'cancelled' => 'cancelled',
                'aborted' => 'failed',
            ];
            $status = $stateMap[$matches[1]] ?? 'unknown';
        }

        if (preg_match('/<progress>(\d+)<\/progress>/', $response, $matches)) {
            $progress = (int) $matches[1];
        }

        return [
            'status' => $status,
            'progress' => $progress,
            'raw' => $response,
        ];
    }

    private function submitRawJob(string $host, int $port, string $data): string
    {
        $socket = @fsockopen($host, $port, $errno, $errstr, 10);
        
        if (!$socket) {
            throw new \Exception("Cannot connect to printer: {$errstr}");
        }

        stream_set_timeout($socket, 30);

    // Send raw PDF bytes directly; some printers reject/ignore PJL wrappers.
        fwrite($socket, $data);

        fclose($socket);

        Log::info("Raw print job sent to {$host}:{$port} via socket");
        
        return 'socket_' . uniqid();
    }

    private function submitViaSystemPrint(Printer $printer, string $filePath, array $options = [], string $previousError = ''): string
    {
        $cupsQueue = $this->resolveCupsQueue($printer);
        $fileArg = escapeshellarg($filePath);

        if ($cupsQueue !== null) {
            $lpArgs = $this->buildSystemPrintOptionArgs($options);
            
            if (!empty($options['page_range'])) {
                $lpArgs[] = sprintf('-P %s', escapeshellarg($options['page_range']));
            }
            
            $argsStr = !empty($lpArgs) ? ' ' . implode(' ', $lpArgs) : '';
            $localCmd = sprintf('lp -d %s%s %s 2>&1', escapeshellarg($cupsQueue), $argsStr, $fileArg);

            $output = [];
            $exitCode = 0;
            exec($localCmd, $output, $exitCode);

            if ($exitCode === 0) {
                Log::info('Print job sent via local CUPS queue', [
                    'printer_id' => $printer->id,
                    'queue' => $cupsQueue,
                    'output' => implode("\n", $output),
                ]);

                return 'lp_' . uniqid();
            }

            Log::warning('Local CUPS queue submission failed', [
                'printer_id' => $printer->id,
                'queue' => $cupsQueue,
                'output' => implode("\n", $output),
                'exit_code' => $exitCode,
            ]);
        }

        $host = $printer->ip_address;
        $port = $printer->port ?? 631;
        $hostArg = escapeshellarg("{$host}:{$port}");
        $queueArg = escapeshellarg('ipp/print/version=1.1');
        $systemPrintArgs = $this->buildSystemPrintOptionArgs($options);
        $argsStr = !empty($systemPrintArgs) ? ' ' . implode(' ', $systemPrintArgs) : '';

        $lpCmd = sprintf(
            'lp -h %s -d %s%s %s 2>&1',
            $hostArg,
            $queueArg,
            $argsStr,
            $fileArg
        );

        $output = [];
        $exitCode = 0;
        exec($lpCmd, $output, $exitCode);

        if ($exitCode === 0) {
            Log::info("Print job sent via lp/lpr to {$host}", [
                'printer_id' => $printer->id,
                'method' => 'lp',
                'output' => implode("\n", $output),
            ]);
            return 'lp_' . uniqid();
        }

        $lprCmd = sprintf(
            'lpr -H %s -P %s%s %s 2>&1',
            $hostArg,
            $queueArg,
            $argsStr,
            $fileArg
        );

        $output = [];
        $exitCode = 0;
        exec($lprCmd, $output, $exitCode);

        if ($exitCode === 0) {
            Log::info("Print job sent via lp/lpr to {$host}", [
                'printer_id' => $printer->id,
                'method' => 'lpr',
                'output' => implode("\n", $output),
            ]);
            return 'lp_' . uniqid();
        }

        $errorOutput = trim(implode("\n", $output));
        $message = "Print failed";
        if ($previousError !== '') {
            $message .= ". Direct IPP error: {$previousError}";
        }
        if ($errorOutput !== '') {
            $message .= ". System print error: {$errorOutput}";
        }

        throw new \Exception($message);
    }

    private function resolveCupsQueue(Printer $printer): ?string
    {
        $capabilities = is_array($printer->capabilities) ? $printer->capabilities : [];

        if (!empty($capabilities['cups_queue'])) {
            $configuredQueue = (string) $capabilities['cups_queue'];

            if ($this->cupsQueueExists($configuredQueue)) {
                return $configuredQueue;
            }

            try {
                return app(PrinterCupsQueueManager::class)->sync($printer);
            } catch (\Throwable $e) {
                Log::warning('Configured CUPS queue is invalid and auto-sync failed', [
                    'printer_id' => $printer->id,
                    'queue' => $configuredQueue,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $defaultQueue = config('printers.default_cups_queue');
        if (!empty($defaultQueue)) {
            if ($this->cupsQueueExists((string) $defaultQueue)) {
                return (string) $defaultQueue;
            }
        }

        try {
            return app(PrinterCupsQueueManager::class)->sync($printer);
        } catch (\Throwable $e) {
            Log::warning('Unable to resolve a CUPS queue for printer', [
                'printer_id' => $printer->id,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    private function buildSystemPrintOptionArgs(array $options): array
    {
        $args = [];

        if (!empty($options['copies'])) {
            $args[] = sprintf('-n %d', max(1, (int) $options['copies']));
        }

        if (array_key_exists('duplex', $options)) {
            $args[] = $options['duplex']
                ? '-o sides=two-sided-long-edge'
                : '-o sides=one-sided';
        }

        if (!empty($options['paper_size'])) {
            $args[] = sprintf('-o media=%s', escapeshellarg((string) $options['paper_size']));
        }

        return $args;
    }

    private function cupsQueueExists(string $queueName): bool
    {
        $output = [];
        $exitCode = 0;

        exec('lpstat -e 2>&1', $output, $exitCode);

        if ($exitCode !== 0) {
            return false;
        }

        return in_array($queueName, array_map('trim', $output), true);
    }
}