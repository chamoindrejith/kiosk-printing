<?php

namespace App\Http\Controllers\Admin;

use App\Models\PrintJob;
use App\Models\Printer;
use App\Models\Payment;
use App\Services\Printers\PrinterCupsQueueManager;
use Illuminate\View\View;

class AdminController
{
    public function __construct(private readonly PrinterCupsQueueManager $printerQueueManager)
    {
    }

    public function dashboard(): View
    {
        $this->printerQueueManager->syncAll(Printer::orderBy('name')->get());

        $jobStatusCounts = PrintJob::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->all();

        $stats = [
            'total_printers' => Printer::count(),
            'active_printers' => Printer::where('is_active', true)->count(),
            'total_jobs' => PrintJob::count(),
            'pending_jobs' => PrintJob::whereIn('status', ['awaiting_payment', 'payment_pending', 'awaiting_confirmation'])->count(),
            'printing_jobs' => PrintJob::whereIn('status', ['queued', 'dispatching', 'printing'])->count(),
            'failed_jobs' => PrintJob::whereIn('status', ['failed', 'cancelled', 'expired'])->count(),
            'completed_today' => PrintJob::whereDate('printed_at', today())->count(),
            'revenue_today' => Payment::whereDate('paid_at', today())
                ->where('status', 'successful')
                ->sum('amount'),
            'revenue_total' => Payment::where('status', 'successful')->sum('amount'),
            'average_job_value' => PrintJob::whereNotNull('total_price')->avg('total_price') ?? 0,
        ];

        $recentJobs = PrintJob::with('printer')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        $syncedPrinters = Printer::query()
            ->orderBy('name')
            ->get();

        $statusCards = [
            ['label' => 'Awaiting Payment', 'count' => $jobStatusCounts['awaiting_payment'] ?? 0, 'color' => 'warning'],
            ['label' => 'Printing', 'count' => $jobStatusCounts['printing'] ?? 0, 'color' => 'info'],
            ['label' => 'Queued', 'count' => $jobStatusCounts['queued'] ?? 0, 'color' => 'primary'],
            ['label' => 'Completed', 'count' => $jobStatusCounts['completed'] ?? 0, 'color' => 'success'],
            ['label' => 'Failed', 'count' => $stats['failed_jobs'], 'color' => 'danger'],
        ];

        return view('admin.dashboard', [
            'stats' => $stats,
            'recentJobs' => $recentJobs,
            'syncedPrinters' => $syncedPrinters,
            'statusCards' => $statusCards,
        ]);
    }
}