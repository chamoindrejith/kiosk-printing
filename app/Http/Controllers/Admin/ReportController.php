<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PrintJob;
use App\Models\Payment;
use App\Models\Printer;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $dateFrom = $request->input('date_from', now()->startOfMonth()->toDateString());
        $dateTo = $request->input('date_to', now()->endOfMonth()->toDateString());

        $revenueByDay = Payment::selectRaw('DATE(paid_at) as date, SUM(amount) as total')
            ->whereBetween('paid_at', [$dateFrom, $dateTo])
            ->where('status', 'successful')
            ->groupByRaw('DATE(paid_at)')
            ->orderBy('date')
            ->get();

        $revenueByPrinter = PrintJob::selectRaw('printer_id, SUM(total_price) as total')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->whereNotNull('total_price')
            ->groupBy('printer_id')
            ->with('printer')
            ->get();

        $jobsByStatus = PrintJob::selectRaw('status, COUNT(*) as count')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->groupBy('status')
            ->pluck('count', 'status');

        $totalRevenue = Payment::whereBetween('paid_at', [$dateFrom, $dateTo])
            ->where('status', 'successful')
            ->sum('amount');

        $totalJobs = PrintJob::whereBetween('created_at', [$dateFrom, $dateTo])->count();
        $completedJobs = PrintJob::whereBetween('created_at', [$dateFrom, $dateTo])
            ->where('status', 'completed')->count();
        $failedJobs = PrintJob::whereBetween('created_at', [$dateFrom, $dateTo])
            ->where('status', 'failed')->count();

        return view('admin.reports.index', [
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'revenueByDay' => $revenueByDay,
            'revenueByPrinter' => $revenueByPrinter,
            'jobsByStatus' => $jobsByStatus,
            'totalRevenue' => $totalRevenue,
            'totalJobs' => $totalJobs,
            'completedJobs' => $completedJobs,
            'failedJobs' => $failedJobs,
        ]);
    }
}