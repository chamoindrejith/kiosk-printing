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
        <label class="form-label">IP Address</label>
        <input type="text" class="form-control" name="ip_address" value="{{ $printer->ip_address }}">
    </div>
    <div class="mb-3">
        <label class="form-label">Port</label>
        <input type="number" class="form-control" name="port" value="{{ $printer->port }}">
    </div>
    <div class="mb-3">
        <label class="form-label">Protocol</label>
        <select class="form-select" name="protocol">
            <option value="ipp" {{ $printer->protocol === 'ipp' ? 'selected' : '' }}>IPP</option>
            <option value="raw" {{ $printer->protocol === 'raw' ? 'selected' : '' }}>Raw</option>
            <option value="http" {{ $printer->protocol === 'http' ? 'selected' : '' }}>HTTP</option>
        </select>
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