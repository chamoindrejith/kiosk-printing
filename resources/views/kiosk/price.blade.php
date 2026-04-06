@extends('layouts.kiosk')

@section('title', 'Price Preview')

@section('content')
@include('kiosk._steps', ['currentStep' => 3, 'printerCode' => $printerCode])

<div class="card">
    <div class="card-body">
        <h5 class="card-title">Price Summary</h5>
        
        <table class="table">
            <tr>
                <td>File</td>
                <td>{{ $printJob->original_filename }}</td>
            </tr>
            <tr>
                <td>Original Pages</td>
                <td>{{ $printJob->original_page_count }}</td>
            </tr>
            <tr>
                <td>Effective Pages</td>
                <td>{{ $pricing['effective_pages'] }}</td>
            </tr>
            <tr>
                <td>Sheet Count</td>
                <td>{{ $pricing['sheet_count'] }}</td>
            </tr>
            <tr>
                <td>Copies</td>
                <td>{{ $printJob->copies }}</td>
            </tr>
            <tr>
                <td>Paper Size</td>
                <td>{{ $printJob->paper_size }}</td>
            </tr>
            <tr>
                <td>Color</td>
                <td>{{ $printJob->color ? 'Color' : 'Black & White' }}</td>
            </tr>
            <tr>
                <td>Duplex</td>
                <td>{{ $printJob->duplex ? 'Double Sided' : 'Single Sided' }}</td>
            </tr>
            <tr>
                <td>Unit Price</td>
                <td>LKR {{ number_format($pricing['unit_price'], 2) }} per sheet</td>
            </tr>
            <tr class="table-primary">
                <td><strong>Total</strong></td>
                <td><strong>LKR {{ number_format($pricing['total_price'], 2) }}</strong></td>
            </tr>
        </table>

        <form action="{{ route('kiosk.payment.initiate', ['printerCode' => $printerCode]) }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-primary w-100">Proceed to Payment</button>
        </form>

        <div class="mt-3 text-center">
            <a href="{{ route('kiosk.options', ['printerCode' => $printerCode]) }}" class="text-muted">&larr; Back to Options</a>
        </div>
    </div>
</div>
@endsection