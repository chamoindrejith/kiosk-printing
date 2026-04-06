@extends('layouts.kiosk')

@section('title', 'Payment')

@section('content')
@include('kiosk._steps', ['currentStep' => 4, 'printerCode' => $printerCode])

<div class="text-center mb-4">
    <h4>Scan to Pay</h4>
    <p class="text-muted">Total: LKR {{ number_format($payment->amount, 2) }}</p>
    <p class="text-muted">Reference: {{ $payment->reference }}</p>
</div>

<div class="card">
    <div class="card-body text-center">
        <div class="qr-placeholder p-4 bg-light rounded mb-3" style="max-width: 250px; margin: 0 auto;">
            <div style="font-size: 100px;">📱</div>
            <p class="mb-0">QR Code</p>
            <small class="text-muted">{{ $payment->qr_code }}</small>
        </div>
        
        <div class="mb-3">
            <span class="badge bg-warning">Expires: {{ $payment->expires_at->format('H:i:s') }}</span>
        </div>

        <div id="payment-status" class="alert alert-info">
            Waiting for payment...
        </div>

        <button id="check-payment" class="btn btn-primary" data-printer-code="{{ $printerCode }}">
            Check Payment Status
        </button>
    </div>
</div>

<div class="mt-3 text-center">
    <small class="text-muted">For testing: <a href="{{ route('kiosk.mock-pay', ['printerCode' => $printerCode, 'paymentId' => $payment->gateway_payment_id]) }}">Simulate Payment</a></small>
</div>

<div class="mt-3 text-center">
    <a href="{{ route('kiosk.price', ['printerCode' => $printerCode]) }}" class="text-muted">&larr; Back to Pricing</a>
</div>

@include('kiosk.scripts')
@endsection