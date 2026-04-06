<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessPrintJob;
use App\Models\PrintJob;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JobController extends Controller
{
    public function index(Request $request): View
    {
        $query = PrintJob::with(['printer', 'payment']);

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('printer_id')) {
            $query->where('printer_id', $request->input('printer_id'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        $jobs = $query->orderByDesc('created_at')->paginate(20);

        return view('admin.jobs.index', ['jobs' => $jobs]);
    }

    public function show(PrintJob $job): View
    {
        $job->load(['printer', 'payment', 'pages', 'events']);

        return view('admin.jobs.show', ['job' => $job]);
    }

    public function update(Request $request, PrintJob $job): RedirectResponse
    {
        if ($request->input('action') === 'resume' && in_array($job->status, ['paused', 'failed'])) {
            $job->update(['status' => 'queued']);
            dispatch(new ProcessPrintJob($job->id));

            return redirect()->route('admin.jobs.show', $job)->with('success', 'Job resumed');
        }

        return back();
    }

    public function retry(PrintJob $job): RedirectResponse
    {
        if (in_array($job->status, ['failed', 'cancelled'])) {
            $job->pages()->update(['status' => 'pending', 'confirmed_at' => null]);
            $job->update([
                'status' => 'queued',
                'last_confirmed_page' => 0,
                'error_message' => null,
            ]);
            dispatch(new ProcessPrintJob($job->id));

            return redirect()->route('admin.jobs.show', $job)->with('success', 'Job queued for retry');
        }

        return back()->with('error', 'Job cannot be retried in current state');
    }

    public function cancel(PrintJob $job): RedirectResponse
    {
        if (in_array($job->status, ['queued', 'dispatching', 'printing', 'paused'])) {
            $job->update(['status' => 'cancelled']);
            $job->recordEvent('cancelled_by_admin');

            return redirect()->route('admin.jobs.show', $job)->with('success', 'Job cancelled');
        }

        return back()->with('error', 'Job cannot be cancelled in current state');
    }
}
