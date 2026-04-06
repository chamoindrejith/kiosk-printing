@extends('layouts.kiosk')

@section('title', 'Print Options')

@section('content')
@include('kiosk._steps', ['currentStep' => 2, 'printerCode' => $printerCode])

<div class="card">
    <div class="card-body">
        <h5 class="card-title">Print Options</h5>
        <p class="text-muted">File: {{ $printJob->original_filename }} ({{ $printJob->original_page_count }} pages)</p>
        
        <form action="{{ route('kiosk.configure', ['printerCode' => $printerCode]) }}" method="POST">
            @csrf
            
            <div class="mb-3">
                <label class="form-label">Color Mode</label>
                <div class="btn-group w-100" role="group">
                    <input type="radio" class="btn-check" name="color" id="color1" value="1" {{ old('color', $printJob->color ?? true) ? 'checked' : '' }}>
                    <label class="btn btn-outline-primary" for="color1">Color</label>
                    <input type="radio" class="btn-check" name="color" id="color0" value="0" {{ old('color', $printJob->color ?? true) ? '' : 'checked' }}>
                    <label class="btn btn-outline-primary" for="color0">Black & White</label>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Print Sides</label>
                <div class="btn-group w-100" role="group">
                    <input type="radio" class="btn-check" name="duplex" id="duplex0" value="0" {{ old('duplex', !$printJob->duplex) ? 'checked' : '' }}>
                    <label class="btn btn-outline-primary" for="duplex0">Single Sided</label>
                    <input type="radio" class="btn-check" name="duplex" id="duplex1" value="1" {{ old('duplex', $printJob->duplex) ? 'checked' : '' }}>
                    <label class="btn btn-outline-primary" for="duplex1">Double Sided</label>
                </div>
            </div>

            <div class="mb-3">
                <label for="paper_size" class="form-label">Paper Size</label>
                <select class="form-select" id="paper_size" name="paper_size" required>
                    <option value="A4" {{ old('paper_size', $printJob->paper_size ?? 'A4') === 'A4' ? 'selected' : '' }}>A4</option>
                    <option value="A5" {{ old('paper_size', $printJob->paper_size ?? 'A4') === 'A5' ? 'selected' : '' }}>A5</option>
                    <option value="Letter" {{ old('paper_size', $printJob->paper_size ?? 'A4') === 'Letter' ? 'selected' : '' }}>Letter</option>
                    <option value="Legal" {{ old('paper_size', $printJob->paper_size ?? 'A4') === 'Legal' ? 'selected' : '' }}>Legal</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="copies" class="form-label">Copies</label>
                <input type="number" class="form-control" id="copies" name="copies" value="{{ old('copies', $printJob->copies ?? 1) }}" min="1" max="10" required>
            </div>

            <div class="mb-3">
                <label for="page_range" class="form-label">Page Range (optional)</label>
                <input type="text" class="form-control" id="page_range" name="page_range" value="{{ old('page_range', $printJob->page_range ?? '') }}" placeholder="e.g., 1-3,5,7-9">
                <div class="form-text">Leave empty to print all pages</div>
            </div>

            <button type="submit" class="btn btn-primary w-100">Continue to Pricing</button>
        </form>

        <div class="mt-3 text-center">
            <a href="{{ route('kiosk.landing', ['printerCode' => $printerCode]) }}" class="text-muted">&larr; Start Over</a>
        </div>
    </div>
</div>
@endsection