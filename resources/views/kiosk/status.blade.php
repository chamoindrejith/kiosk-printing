@extends('layouts.kiosk')

@section('title', 'Print Status')

@section('content')
<div class="text-center mb-4">
    <h4>Print Job Status</h4>
</div>

<div class="card">
    <div class="card-body">
        <div id="job-status" class="alert alert-info" data-status="{{ $printJob->status }}">
            {{ ucfirst($printJob->status) }}
        </div>

        <table class="table">
            <tr>
                <td>Job ID</td>
                <td>#{{ $printJob->id }}</td>
            </tr>
            <tr>
                <td>Status</td>
                <td><span class="badge bg-{{ $printJob->status === 'completed' ? 'success' : ($printJob->status === 'failed' ? 'danger' : 'primary') }}">{{ $printJob->status }}</span></td>
            </tr>
            <tr>
                <td>File</td>
                <td>{{ $printJob->original_filename }}</td>
            </tr>
            <tr>
                <td>Sheets</td>
                <td>{{ $printJob->sheet_count }}</td>
            </tr>
            <tr>
                <td>Pages Confirmed</td>
                <td>{{ $printJob->last_confirmed_page }} / {{ $printJob->effective_page_count }}</td>
            </tr>
            @if($printJob->error_message)
            <tr>
                <td>Error</td>
                <td class="text-danger">{{ $printJob->error_message }}</td>
            </tr>
            @endif
        </table>

        <button id="refresh-status" class="btn btn-primary w-100 mb-2" data-job-id="{{ $printJob->id }}" data-printer-code="{{ $printerCode }}">
            Refresh Status
        </button>

        @if(in_array($printJob->status, ['queued', 'dispatching', 'printing', 'paused']))
        <form action="{{ route('kiosk.cancel', ['printerCode' => $printerCode]) }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-danger w-100" onclick="return confirm('Cancel this print job?')">
                Cancel Print
            </button>
        </form>
        @endif

        <div class="mt-3 text-center">
            <a href="{{ route('kiosk.landing', ['printerCode' => $printerCode]) }}" class="text-muted">&larr; New Print Job</a>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    (function () {
        const statusEl = document.getElementById('job-status');
        const refreshButton = document.getElementById('refresh-status');
        const activeStatuses = ['queued', 'dispatching', 'printing', 'paused'];

        function reloadIfActive() {
            const currentStatus = (statusEl.dataset.status || '').toLowerCase();

            if (activeStatuses.includes(currentStatus)) {
                window.location.reload();
            }
        }

        refreshButton.addEventListener('click', function () {
            window.location.reload();
        });

        setTimeout(reloadIfActive, 3000);
        setInterval(reloadIfActive, 5000);
    })();
</script>
@endsection