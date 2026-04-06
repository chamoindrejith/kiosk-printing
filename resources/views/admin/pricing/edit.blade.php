@extends('layouts.admin')

@section('title', 'Edit Pricing Rule')

@section('content')
<h2>Edit Pricing Rule</h2>

<form method="POST" action="{{ route('admin.pricing.update', $pricingRule) }}">
    @csrf @method('PUT')
    <div class="mb-3">
        <label class="form-label">Name</label>
        <input type="text" class="form-control" name="name" value="{{ $pricingRule->name }}" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Paper Size</label>
        <select class="form-select" name="paper_size" required>
            <option value="A4" {{ $pricingRule->paper_size === 'A4' ? 'selected' : '' }}>A4</option>
            <option value="A5" {{ $pricingRule->paper_size === 'A5' ? 'selected' : '' }}>A5</option>
            <option value="Letter" {{ $pricingRule->paper_size === 'Letter' ? 'selected' : '' }}>Letter</option>
            <option value="Legal" {{ $pricingRule->paper_size === 'Legal' ? 'selected' : '' }}>Legal</option>
        </select>
    </div>
    <div class="mb-3">
        <label class="form-label">Color Mode</label>
        <select class="form-select" name="color_mode" required>
            <option value="color" {{ $pricingRule->color_mode === 'color' ? 'selected' : '' }}>Color</option>
            <option value="bw" {{ $pricingRule->color_mode === 'bw' ? 'selected' : '' }}>Black & White</option>
        </select>
    </div>
    <div class="mb-3">
        <label class="form-label">Duplex Mode</label>
        <select class="form-select" name="duplex_mode" required>
            <option value="simplex" {{ $pricingRule->duplex_mode === 'simplex' ? 'selected' : '' }}>Simplex</option>
            <option value="duplex" {{ $pricingRule->duplex_mode === 'duplex' ? 'selected' : '' }}>Duplex</option>
        </select>
    </div>
    <div class="mb-3">
        <label class="form-label">Price per Sheet (LKR)</label>
        <input type="number" class="form-control" name="price_per_sheet" step="0.01" min="0" value="{{ $pricingRule->price_per_sheet }}" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Printer</label>
        <select class="form-select" name="printer_id">
            <option value="">Global</option>
            @foreach($printers as $printer)
                <option value="{{ $printer->id }}" {{ $pricingRule->printer_id == $printer->id ? 'selected' : '' }}>{{ $printer->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="mb-3">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="is_active" value="1" {{ $pricingRule->is_active ? 'checked' : '' }}>
            <label class="form-check-label">Active</label>
        </div>
    </div>
    <button type="submit" class="btn btn-primary">Update Rule</button>
    <a href="{{ route('admin.pricing.index') }}" class="btn btn-secondary">Cancel</a>
</form>
@endsection