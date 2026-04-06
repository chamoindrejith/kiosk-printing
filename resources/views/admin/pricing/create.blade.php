@extends('layouts.admin')

@section('title', 'Add Pricing Rule')

@section('content')
<h2>Add Pricing Rule</h2>

<form method="POST" action="{{ route('admin.pricing.store') }}">
    @csrf
    <div class="mb-3">
        <label class="form-label">Name</label>
        <input type="text" class="form-control" name="name" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Paper Size</label>
        <select class="form-select" name="paper_size" required>
            <option value="A4">A4</option>
            <option value="A5">A5</option>
            <option value="Letter">Letter</option>
            <option value="Legal">Legal</option>
        </select>
    </div>
    <div class="mb-3">
        <label class="form-label">Color Mode</label>
        <select class="form-select" name="color_mode" required>
            <option value="color">Color</option>
            <option value="bw">Black & White</option>
        </select>
    </div>
    <div class="mb-3">
        <label class="form-label">Duplex Mode</label>
        <select class="form-select" name="duplex_mode" required>
            <option value="simplex">Simplex (Single-sided)</option>
            <option value="duplex">Duplex (Double-sided)</option>
        </select>
    </div>
    <div class="mb-3">
        <label class="form-label">Price per Sheet (LKR)</label>
        <input type="number" class="form-control" name="price_per_sheet" step="0.01" min="0" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Printer (Optional)</label>
        <select class="form-select" name="printer_id">
            <option value="">Global (All Printers)</option>
            @foreach($printers as $printer)
                <option value="{{ $printer->id }}">{{ $printer->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="mb-3">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="is_active" value="1" checked>
            <label class="form-check-label">Active</label>
        </div>
    </div>
    <button type="submit" class="btn btn-primary">Create Rule</button>
    <a href="{{ route('admin.pricing.index') }}" class="btn btn-secondary">Cancel</a>
</form>
@endsection