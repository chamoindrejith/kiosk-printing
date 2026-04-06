@extends('layouts.admin')

@section('title', 'Payments')

@section('content')
<h2>Payments</h2>

<form method="GET" class="mb-4">
    <div class="row">
        <div class="col-md-2">
            <select name="status" class="form-select">
                <option value="">All Status</option>
                @foreach(App\Models\Payment::STATUSES as $status)
                    <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>{{ $status }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
        </div>
        <div class="col-md-2">
            <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
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
            <th>Reference</th>
            <th>Job</th>
            <th>Amount</th>
            <th>Status</th>
            <th>Created</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse($payments as $payment)
        <tr>
            <td>#{{ $payment->id }}</td>
            <td>{{ $payment->reference }}</td>
            <td>#{{ $payment->print_job_id }}</td>
            <td>LKR {{ number_format($payment->amount, 2) }}</td>
            <td><span class="badge bg-{{ $payment->status === 'successful' ? 'success' : ($payment->status === 'failed' ? 'danger' : ($payment->status === 'pending' ? 'warning' : ($payment->status === 'expired' ? 'secondary' : 'info'))) }}">{{ $payment->status }}</span></td>
            <td>{{ $payment->created_at->format('Y-m-d H:i') }}</td>
            <td>
                <a href="{{ route('admin.payments.show', $payment) }}" class="btn btn-sm btn-info">View</a>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="7" class="text-center text-muted py-4">No payments found.</td>
        </tr>
        @endforelse
    </tbody>
</table>

{{ $payments->links() }}
@endsection