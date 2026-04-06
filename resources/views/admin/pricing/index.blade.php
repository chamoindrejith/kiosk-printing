@extends('layouts.admin')

@section('title', 'Pricing')

@section('content')
<div class="d-flex justify-content-between mb-4">
    <h2>Pricing Rules</h2>
    <a href="{{ route('admin.pricing.create') }}" class="btn btn-primary">Add Pricing Rule</a>
</div>

<table class="table">
    <thead>
        <tr>
            <th>Name</th>
            <th>Paper Size</th>
            <th>Color Mode</th>
            <th>Duplex</th>
            <th>Price/Sheet</th>
            <th>Printer</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach($pricingRules as $rule)
        <tr>
            <td>{{ $rule->name }}</td>
            <td>{{ $rule->paper_size }}</td>
            <td>{{ $rule->color_mode === 'color' ? 'Color' : 'B&W' }}</td>
            <td>{{ $rule->duplex_mode === 'duplex' ? 'Yes' : 'No' }}</td>
            <td>LKR {{ number_format($rule->price_per_sheet, 2) }}</td>
            <td>{{ $rule->printer->name ?? 'Global' }}</td>
            <td>
                @if($rule->is_active)
                    <span class="badge bg-success">Active</span>
                @else
                    <span class="badge bg-secondary">Inactive</span>
                @endif
            </td>
            <td>
                <a href="{{ route('admin.pricing.edit', $rule) }}" class="btn btn-sm btn-primary">Edit</a>
                <form action="{{ route('admin.pricing.destroy', $rule) }}" method="POST" class="d-inline">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete?')">Delete</button>
                </form>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection