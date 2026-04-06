@extends('layouts.admin')

@section('title', 'Print Jobs')

@section('content')
<h2>Print Jobs</h2>

<form method="GET" class="mb-4">
    <div class="row">
        <div class="col-md-2">
            <select name="status" class="form-select">
                <option value="">All Status</option>
                @foreach(App\Models\PrintJob::STATUSES as $status)
                    <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>{{ $status }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}" placeholder="From">
        </div>
        <div class="col-md-2">
            <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}" placeholder="To">
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary">Filter</button>
        </div>
    </div>
</form>

<table class="table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Printer</th>
            <th>File</th>
            <th>Pages</th>
            <th>Amount</th>
            <th>Status</th>
            <th>Created</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach($jobs as $job)
        <tr>
            <td>#{{ $job->id }}</td>
            <td>{{ $job->printer->name ?? '-' }}</td>
            <td>{{ $job->original_filename }}</td>
            <td>{{ $job->effective_page_count ?? $job->original_page_count }}</td>
            <td>LKR {{ number_format($job->total_price ?? 0, 2) }}</td>
            <td><span class="badge bg-{{ $job->status === 'completed' ? 'success' : ($job->status === 'failed' ? 'danger' : 'primary') }}">{{ $job->status }}</span></td>
            <td>{{ $job->created_at->format('Y-m-d H:i') }}</td>
            <td>
                <a href="{{ route('admin.jobs.show', $job) }}" class="btn btn-sm btn-info">View</a>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

{{ $jobs->links() }}
@endsection