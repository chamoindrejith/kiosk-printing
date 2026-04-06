@extends('layouts.kiosk')

@section('title', 'Print Service')

@section('content')
@include('kiosk._steps', ['currentStep' => 1, 'printerCode' => $printerCode])

<div class="text-center mb-4">
    <h1>{{ $printer->name }}</h1>
    @if($printer->location)
        <p class="text-muted">{{ $printer->location }}</p>
    @endif
</div>

@if($existingJob)
<div class="card mb-3">
    <div class="card-body d-flex justify-content-between align-items-start">
        <div>
            <h5 class="card-title">Current Upload</h5>
            <p class="mb-1"><strong>{{ $existingJob->original_filename }}</strong></p>
            <p class="text-muted mb-2">{{ $existingJob->original_page_count }} pages</p>
            <a href="{{ route('kiosk.options', ['printerCode' => $printerCode]) }}" class="btn btn-outline-primary btn-sm">Continue to Options</a>
        </div>
        <form action="{{ route('kiosk.remove', ['printerCode' => $printerCode]) }}" method="POST">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-outline-danger btn-sm" onclick="return confirm('Remove this upload?')" title="Remove">
                <i class="bi bi-x-lg"></i>
            </button>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <h5 class="card-title">Upload New PDF (replace current)</h5>
        <form action="{{ route('kiosk.upload', ['printerCode' => $printerCode]) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <label for="pdf" class="form-label">Select PDF File</label>
                <input type="file" class="form-control" id="pdf" name="pdf" accept=".pdf" required>
                <div class="form-text">Maximum file size: 50MB. PDF only.</div>
            </div>
            @error('pdf')
                <div class="alert alert-danger">{{ $message }}</div>
            @enderror
            <button type="submit" class="btn btn-primary w-100">Upload PDF</button>
        </form>
    </div>
</div>
@else
<div class="card">
    <div class="card-body">
        <h5 class="card-title">Upload Your PDF</h5>
        <form action="{{ route('kiosk.upload', ['printerCode' => $printerCode]) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <label for="pdf" class="form-label">Select PDF File</label>
                <input type="file" class="form-control" id="pdf" name="pdf" accept=".pdf" required>
                <div class="form-text">Maximum file size: 50MB. PDF only.</div>
            </div>
            @error('pdf')
                <div class="alert alert-danger">{{ $message }}</div>
            @enderror
            <button type="submit" class="btn btn-primary w-100">Upload PDF</button>
        </form>

        <div class="mt-3 text-center">
            <small class="text-muted">Supported: PDF files only</small>
        </div>
    </div>
</div>
@endif

<div class="mt-3 text-center">
    <a href="{{ route('admin.dashboard') }}" class="text-muted small">Admin</a>
</div>
@endsection