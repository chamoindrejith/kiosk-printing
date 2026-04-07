@extends('layouts.admin')

@section('title', 'Edit Printer')

@section('content')
<h2>Edit Printer</h2>

<form method="POST" action="{{ route('admin.printers.update', $printer) }}">
    @csrf @method('PUT')
    <div class="mb-3">
        <label class="form-label">Printer Code</label>
        <input type="text" class="form-control" name="code" value="{{ $printer->code }}" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Name</label>
        <input type="text" class="form-control" name="name" value="{{ $printer->name }}" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Location</label>
        <input type="text" class="form-control" name="location" value="{{ $printer->location }}">
    </div>
    <div class="mb-3">
        <label class="form-label">IP Address <span class="text-danger">*</span></label>
        <input type="text" class="form-control @error('ip_address') is-invalid @enderror" name="ip_address" value="{{ old('ip_address', $printer->ip_address) }}" required>
        @error('ip_address')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="mb-3">
        <label class="form-label">Port</label>
        <input type="number" class="form-control @error('port') is-invalid @enderror" name="port" value="{{ old('port', $printer->port) }}" min="1" max="65535">
        @error('port')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <small class="text-muted">Default: 631</small>
    </div>
    <div class="mb-3">
        <label class="form-label">Protocol</label>
        <select class="form-select @error('protocol') is-invalid @enderror" name="protocol">
            <option value="">Default (IPP)</option>
            <option value="ipp" {{ old('protocol', $printer->protocol) === 'ipp' ? 'selected' : '' }}>IPP</option>
            <option value="raw" {{ old('protocol', $printer->protocol) === 'raw' ? 'selected' : '' }}>Raw</option>
            <option value="http" {{ old('protocol', $printer->protocol) === 'http' ? 'selected' : '' }}>HTTP</option>
        </select>
        @error('protocol')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <small class="text-muted">IPP is recommended for most network printers</small>
    </div>
    <div class="mb-3">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="is_active" value="1" {{ $printer->is_active ? 'checked' : '' }}>
            <label class="form-check-label">Active</label>
        </div>
    </div>
    <button type="submit" class="btn btn-primary">Update Printer</button>
    <a href="{{ route('admin.printers.index') }}" class="btn btn-secondary">Cancel</a>
</form>
@endsection