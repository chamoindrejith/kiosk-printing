<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin - Print Kiosk')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; }
        .sidebar { min-height: 100vh; background: #212529; }
        .sidebar a { color: #adb5bd; text-decoration: none; padding: 10px 15px; display: block; border-radius: 4px; }
        .sidebar a:hover, .sidebar a.active { background: #495057; color: white; }
        .stat-card { border-radius: 8px; padding: 20px; }
    </style>
    @yield('styles')
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-2 sidebar p-3">
                <h4 class="text-white mb-4"><i class="bi bi-printer"></i> Print Admin</h4>
                <nav>
                    <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                    <a href="{{ route('admin.printers.index') }}" class="{{ request()->routeIs('admin.printers.index') ? 'active' : '' }}">
                        <i class="bi bi-print"></i> Printers
                    </a>
                    <a href="{{ route('admin.pricing.index') }}" class="{{ request()->routeIs('admin.pricing.index') ? 'active' : '' }}">
                        <i class="bi bi-currency-dollar"></i> Pricing
                    </a>
                    <a href="{{ route('admin.jobs.index') }}" class="{{ request()->routeIs('admin.jobs.index') ? 'active' : '' }}">
                        <i class="bi bi-file-earmark-text"></i> Jobs
                    </a>
                    <a href="{{ route('admin.payments.index') }}" class="{{ request()->routeIs('admin.payments.index') ? 'active' : '' }}">
                        <i class="bi bi-credit-card"></i> Payments
                    </a>
                    <a href="{{ route('admin.reports') }}" class="{{ request()->routeIs('admin.reports') ? 'active' : '' }}">
                        <i class="bi bi-graph-up"></i> Reports
                    </a>
                </nav>
            </div>
            <div class="col-md-10 p-4">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif
                @yield('content')
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @yield('scripts')
</body>
</html>