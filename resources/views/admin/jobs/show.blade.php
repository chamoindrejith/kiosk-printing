@extends('layouts.admin')

@section('title', 'Job #' . $job->id)

@section('content')
<h2>Job #{{ $job->id }}</h2>

<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">Details</div>
            <div class="card-body">
                <table class="table">
                    <tr><td>Printer</td><td>{{ $job->printer->name ?? '-' }}</td></tr>
                    <tr><td>File</td><td>{{ $job->original_filename }}</td></tr>
                    <tr><td>Original Pages</td><td>{{ $job->original_page_count }}</td></tr>
                    <tr><td>Effective Pages</td><td>{{ $job->effective_page_count ?? '-' }}</td></tr>
                    <tr><td>Sheets</td><td>{{ $job->sheet_count ?? '-' }}</td></tr>
                    <tr><td>Total Price</td><td>LKR {{ number_format($job->total_price ?? 0, 2) }}</td></tr>
                    <tr><td>Status</td><td><span class="badge bg-{{ $job->status === 'completed' ? 'success' : ($job->status === 'failed' ? 'danger' : 'primary') }}">{{ $job->status }}</span></td></tr>
                    <tr><td>Created</td><td>{{ $job->created_at }}</td></tr>
                    @if($job->error_message)
                    <tr><td>Error</td><td class="text-danger">{{ $job->error_message }}</td></tr>
                    @endif
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">Options</div>
            <div class="card-body">
                <table class="table">
                    <tr><td>Color</td><td>{{ $job->color ? 'Yes' : 'No' }}</td></tr>
                    <tr><td>Duplex</td><td>{{ $job->duplex ? 'Yes' : 'No' }}</td></tr>
                    <tr><td>Paper Size</td><td>{{ $job->paper_size }}</td></tr>
                    <tr><td>Copies</td><td>{{ $job->copies }}</td></tr>
                    <tr><td>Page Range</td><td>{{ $job->page_range ?? 'All' }}</td></tr>
                </table>
            </div>
        </div>
    </div>
</div>

@if($job->pages->count() > 0)
<div class="card mb-4">
    <div class="card-header">Pages ({{ $job->pages->count() }})</div>
    <div class="card-body">
        <table class="table table-sm">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Page</th>
                    <th>Copy</th>
                    <th>Status</th>
                    <th>Confirmed</th>
                </tr>
            </thead>
            <tbody>
                @foreach($job->pages as $page)
                <tr>
                    <td>{{ $page->sequence_order }}</td>
                    <td>{{ $page->page_number }}</td>
                    <td>{{ $page->copy_number }}</td>
                    <td><span class="badge bg-{{ $page->status === 'confirmed' ? 'success' : ($page->status === 'failed' ? 'danger' : 'secondary') }}">{{ $page->status }}</span></td>
                    <td>{{ $page->confirmed_at?->format('H:i:s') ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

<div class="mb-4">
    @if(in_array($job->status, ['failed', 'cancelled']))
        <a href="{{ route('admin.jobs.retry', $job) }}" class="btn btn-success">Retry Job</a>
    @endif
    @if(in_array($job->status, ['queued', 'dispatching', 'printing', 'paused']))
        <a href="{{ route('admin.jobs.cancel', $job) }}" class="btn btn-danger" onclick="return confirm('Cancel this job?')">Cancel Job</a>
    @endif
</div>

@if($job->events->count() > 0)
<div class="card">
    <div class="card-header">Event History</div>
    <div class="card-body">
        <table class="table table-sm">
            <thead>
                <tr>
                    <th>Time</th>
                    <th>Type</th>
                    <th>Payload</th>
                </tr>
            </thead>
            <tbody>
                @foreach($job->events as $event)
                <tr>
                    <td>{{ $event->created_at }}</td>
                    <td>{{ $event->event_type }}</td>
                    <td><small>{{ json_encode($event->payload) }}</small></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif
@endsection