@extends('layouts.admin')

@section('title', 'Dashboard')

@section('styles')
<style>
    .dashboard-shell {
        background: linear-gradient(180deg, #f8fbff 0%, #f4f7fb 100%);
        border-radius: 24px;
        padding: 24px;
    }

    .hero-card {
        background: linear-gradient(135deg, #0f172a 0%, #1d4ed8 100%);
        color: #fff;
        border: 0;
        border-radius: 24px;
        overflow: hidden;
        box-shadow: 0 20px 50px rgba(15, 23, 42, 0.18);
    }

    .hero-pill {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 6px 12px;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.12);
        font-size: 0.85rem;
    }

    .metric-card {
        border: 0;
        border-radius: 20px;
        box-shadow: 0 12px 28px rgba(15, 23, 42, 0.08);
    }

    .metric-icon {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
    }

    .surface-card {
        border: 0;
        border-radius: 20px;
        box-shadow: 0 12px 28px rgba(15, 23, 42, 0.08);
    }

    .status-chip {
        min-width: 88px;
        text-align: center;
    }

    .soft-table tbody tr:hover {
        background: rgba(37, 99, 235, 0.04);
    }
</style>
@endsection

@section('content')
<div class="dashboard-shell">
    <div class="card hero-card mb-4">
        <div class="card-body p-4 p-md-5">
            <div class="d-flex flex-column flex-md-row justify-content-between gap-4 align-items-md-center">
                <div>
                    <div class="hero-pill mb-3">
                        <i class="bi bi-lightning-charge-fill"></i>
                        Print Admin Overview
                    </div>
                    <h2 class="display-6 mb-2">Operational dashboard</h2>
                    <p class="mb-0 text-white-50">Monitor printer health, live jobs, and revenue from one place.</p>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <a href="{{ route('admin.jobs.index') }}" class="btn btn-light btn-lg">
                        <i class="bi bi-file-earmark-text me-1"></i> View Jobs
                    </a>
                    <a href="{{ route('admin.printers.index') }}" class="btn btn-outline-light btn-lg">
                        <i class="bi bi-printer me-1"></i> Manage Printers
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card metric-card h-100">
                <div class="card-body d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted small mb-1">Total Printers</div>
                        <h3 class="mb-1">{{ $stats['total_printers'] }}</h3>
                        <div class="text-success small">{{ $stats['active_printers'] }} active</div>
                    </div>
                    <div class="metric-icon bg-primary-subtle text-primary"><i class="bi bi-printer"></i></div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card metric-card h-100">
                <div class="card-body d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted small mb-1">Total Jobs</div>
                        <h3 class="mb-1">{{ $stats['total_jobs'] }}</h3>
                        <div class="text-muted small">All-time submissions</div>
                    </div>
                    <div class="metric-icon bg-success-subtle text-success"><i class="bi bi-file-earmark-text"></i></div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card metric-card h-100">
                <div class="card-body d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted small mb-1">Jobs Printing</div>
                        <h3 class="mb-1">{{ $stats['printing_jobs'] }}</h3>
                        <div class="text-muted small">Queued or in progress</div>
                    </div>
                    <div class="metric-icon bg-warning-subtle text-warning"><i class="bi bi-arrow-repeat"></i></div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card metric-card h-100">
                <div class="card-body d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted small mb-1">Revenue Today</div>
                        <h3 class="mb-1">LKR {{ number_format($stats['revenue_today'], 0) }}</h3>
                        <div class="text-muted small">Average job LKR {{ number_format($stats['average_job_value'], 2) }}</div>
                    </div>
                    <div class="metric-icon bg-info-subtle text-info"><i class="bi bi-cash-coin"></i></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        @foreach($statusCards as $card)
            <div class="col-12 col-sm-6 col-xl-2">
                <div class="card surface-card h-100">
                    <div class="card-body">
                        <div class="text-muted small mb-2">{{ $card['label'] }}</div>
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="mb-0">{{ $card['count'] }}</h4>
                            <span class="badge text-bg-{{ $card['color'] }} status-chip">Live</span>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row g-4 mb-4">
        <div class="col-12 col-lg-8">
            <div class="card surface-card h-100">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-1">Recent Jobs</h5>
                            <p class="text-muted small mb-0">Latest uploads, print status, and pricing</p>
                        </div>
                        <a href="{{ route('admin.jobs.index') }}" class="btn btn-sm btn-outline-primary">All jobs</a>
                    </div>
                </div>
                <div class="card-body px-4 pb-4">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0 soft-table">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Printer</th>
                                    <th>File</th>
                                    <th>Status</th>
                                    <th class="text-end">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentJobs as $job)
                                    <tr>
                                        <td>#{{ $job->id }}</td>
                                        <td>{{ $job->printer->name ?? '-' }}</td>
                                        <td class="text-truncate" style="max-width: 220px;">{{ $job->original_filename }}</td>
                                        <td>
                                            <span class="badge text-bg-{{ $job->status === 'completed' ? 'success' : ($job->status === 'failed' ? 'danger' : ($job->status === 'printing' ? 'info' : 'secondary')) }}">
                                                {{ $job->status }}
                                            </span>
                                        </td>
                                        <td class="text-end">LKR {{ number_format($job->total_price ?? 0, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">No jobs yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-4">
            <div class="card surface-card h-100 mb-4">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <h5 class="mb-1">Printer Health</h5>
                    <p class="text-muted small mb-0">All synced printers from the system and database</p>
                </div>
                <div class="card-body px-4 pb-4">
                    <div class="list-group list-group-flush">
                        @forelse($syncedPrinters as $printer)
                            <div class="list-group-item px-0 d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-semibold">{{ $printer->name }}</div>
                                            <div class="text-muted small">{{ $printer->code }} · {{ $printer->ip_address ?? 'No IP' }}</div>
                                            <div class="text-muted small">Queue: {{ $printer->capabilities['cups_queue'] ?? 'Not set' }}</div>
                                </div>
                                <span class="badge text-bg-{{ $printer->is_active ? 'success' : 'secondary' }}">
                                    {{ $printer->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                        @empty
                            <div class="text-muted">No printers configured.</div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="card surface-card">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <h5 class="mb-1">Quick Summary</h5>
                    <p class="text-muted small mb-0">Operational snapshot</p>
                </div>
                <div class="card-body px-4 pb-4">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Pending Jobs</span>
                        <strong>{{ $stats['pending_jobs'] }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Completed Today</span>
                        <strong>{{ $stats['completed_today'] }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Failed Jobs</span>
                        <strong>{{ $stats['failed_jobs'] }}</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Total Revenue</span>
                        <strong>LKR {{ number_format($stats['revenue_total'], 2) }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection