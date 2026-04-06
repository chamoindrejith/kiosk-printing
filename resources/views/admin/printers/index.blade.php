@extends('layouts.admin')

@section('title', 'Printers')

@section('content')
<div class="d-flex justify-content-between mb-4">
    <h2>Printers</h2>
    <a href="{{ route('admin.printers.create') }}" class="btn btn-primary">Add Printer</a>
</div>

<table class="table">
    <thead>
        <tr>
            <th>Code</th>
            <th>Name</th>
            <th>Location</th>
            <th>IP Address</th>
            <th>CUPS Queue</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach($printers as $printer)
        <tr>
            <td>{{ $printer->code }}</td>
            <td>{{ $printer->name }}</td>
            <td>{{ $printer->location ?? '-' }}</td>
            <td>{{ $printer->ip_address ?? '-' }}</td>
            <td>{{ $printer->capabilities['cups_queue'] ?? '-' }}</td>
            <td>
                @if($printer->is_active)
                    <span class="badge bg-success">Active</span>
                @else
                    <span class="badge bg-secondary">Inactive</span>
                @endif
            </td>
            <td>
                <a href="{{ route('admin.printers.edit', $printer) }}" class="btn btn-sm btn-primary">Edit</a>
                <form action="{{ route('admin.printers.destroy', $printer) }}" method="POST" class="d-inline">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete?')">Delete</button>
                </form>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection