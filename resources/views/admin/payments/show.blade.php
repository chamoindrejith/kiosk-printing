@extends('layouts.admin')

@section('title', 'Payment #' . $payment->id)

@section('content')
<h2>Payment #{{ $payment->id }}</h2>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">Details</div>
            <div class="card-body">
                <table class="table">
                    <tr><td>Reference</td><td>{{ $payment->reference }}</td></tr>
                    <tr><td>Job ID</td><td>#{{ $payment->print_job_id }}</td></tr>
                    <tr><td>Gateway</td><td>{{ $payment->gateway }}</td></tr>
                    <tr><td>Gateway Payment ID</td><td>{{ $payment->gateway_payment_id ?? '-' }}</td></tr>
                    <tr><td>Amount</td><td>LKR {{ number_format($payment->amount, 2) }}</td></tr>
                    <tr><td>Status</td><td><span class="badge bg-{{ $payment->status === 'successful' ? 'success' : ($payment->status === 'failed' ? 'danger' : 'warning') }}">{{ $payment->status }}</span></td></tr>
                    <tr><td>QR Code</td><td>{{ $payment->qr_code ?? '-' }}</td></tr>
                    <tr><td>Created</td><td>{{ $payment->created_at }}</td></tr>
                    <tr><td>Expires</td><td>{{ $payment->expires_at ?? '-' }}</td></tr>
                    <tr><td>Paid At</td><td>{{ $payment->paid_at ?? '-' }}</td></tr>
                </table>
            </div>
        </div>
    </div>
</div>

@if($payment->events->count() > 0)
<div class="card mt-4">
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
                @foreach($payment->events as $event)
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