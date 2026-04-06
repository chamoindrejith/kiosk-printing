@extends('layouts.admin')

@section('title', 'Add Printer')

@section('content')
<h2>Add Printer</h2>

<form method="POST" action="{{ route('admin.printers.store') }}">
    @csrf
    <div class="mb-3">
        <label class="form-label">Printer Code</label>
        <input type="text" class="form-control" name="code" required>
        <small>Unique code for QR URL (e.g., 'demo' = /kiosk/demo)</small>
    </div>
    <div class="mb-3">
        <label class="form-label">Name</label>
        <input type="text" class="form-control" name="name" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Location</label>
        <input type="text" class="form-control" name="location">
    </div>
    <div class="mb-3">
        <label class="form-label">IP Address</label>
        <input type="text" class="form-control" name="ip_address" placeholder="192.168.1.100">
    </div>
    <div class="mb-3">
        <label class="form-label">Port</label>
        <input type="number" class="form-control" name="port" value="631">
    </div>
    <div class="mb-3">
        <label class="form-label">Protocol</label>
        <select class="form-select" name="protocol">
            <option value="ipp">IPP</option>
            <option value="raw">Raw (Port 9100)</option>
            <option value="http">HTTP</option>
        </select>
    </div>
    <div class="mb-3">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="is_active" value="1" checked>
            <label class="form-check-label">Active</label>
        </div>
    </div>
    <button type="submit" class="btn btn-primary">Create Printer</button>
    <a href="{{ route('admin.printers.index') }}" class="btn btn-secondary">Cancel</a>
</form>
@endsection