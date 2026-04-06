@extends('layouts.admin')

@section('title', 'Reports')

@section('content')
<h2>Reports</h2>

<form method="GET" class="mb-4">
    <div class="row">
        <div class="col-md-3">
            <label class="form-label">From</label>
            <input type="date" name="date_from" class="form-control" value="{{ $dateFrom }}">
        </div>
        <div class="col-md-3">
            <label class="form-label">To</label>
            <input type="date" name="date_to" class="form-control" value="{{ $dateTo }}">
        </div>
        <div class="col-md-2">
            <label class="form-label">&nbsp;</label>
            <button type="submit" class="btn btn-primary w-100">Filter</button>
        </div>
    </div>
</form>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h5>LKR {{ number_format($totalRevenue, 2) }}</h5>
                <p class="mb-0">Total Revenue</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h5>{{ $totalJobs }}</h5>
                <p class="mb-0">Total Jobs</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <h5>{{ $completedJobs }}</h5>
                <p class="mb-0">Completed</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-danger text-white">
            <div class="card-body">
                <h5>{{ $failedJobs }}</h5>
                <p class="mb-0">Failed</p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">Revenue by Day</div>
            <div class="card-body">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($revenueByDay as $day)
                        <tr>
                            <td>{{ $day->date }}</td>
                            <td>LKR {{ number_format($day->total, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">Revenue by Printer</div>
            <div class="card-body">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Printer</th>
                            <th>Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($revenueByPrinter as $printer)
                        <tr>
                            <td>{{ $printer->printer->name ?? 'Unknown' }}</td>
                            <td>LKR {{ number_format($printer->total, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection