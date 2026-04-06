@extends('layouts.kiosk')

@section('title', 'Confirm Print')

@section('content')
@include('kiosk._steps', ['currentStep' => 5, 'printerCode' => $printerCode])

<div class="card">
    <div class="card-body">
        <h5 class="card-title">Confirm Print</h5>
        
        <div class="alert alert-success">
            <strong>Payment Successful!</strong> Your payment has been confirmed.
        </div>

        <table class="table">
            <tr>
                <td>Total Paid</td>
                <td><strong>LKR {{ number_format($printJob->total_price, 2) }}</strong></td>
            </tr>
            <tr>
                <td>Sheets</td>
                <td>{{ $printJob->sheet_count }}</td>
            </tr>
            <tr>
                <td>Printer</td>
                <td>{{ $printer->name }}</td>
            </tr>
        </table>

        <form action="{{ route('kiosk.confirm', ['printerCode' => $printerCode]) }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-success w-100 btn-lg">
                <i class="bi bi-printer"></i> Start Printing
            </button>
        </form>

        <div class="mt-3 text-center">
            <a href="{{ route('kiosk.price', ['printerCode' => $printerCode]) }}" class="text-muted">&larr; Back to Pricing</a>
        </div>
    </div>
</div>
@endsection