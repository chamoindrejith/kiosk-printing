<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Print Kiosk')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; }
        .kiosk-container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .step-indicator { display: flex; justify-content: space-between; margin-bottom: 30px; }
        .step { flex: 1; text-align: center; position: relative; }
        .step-number { width: 30px; height: 30px; border-radius: 50%; background: #e9ecef; display: inline-flex; align-items: center; justify-content: center; font-weight: bold; font-size: 14px; }
        .step.active .step-number { background: #0d6efd; color: white; }
        .step.completed .step-number { background: #198754; color: white; cursor: pointer; }
        .step.disabled .step-number { background: #6c757d; color: #adb5bd; }
        .step.disabled .step-number, .step.disabled .step-label { pointer-events: none; }
        .step-label { font-size: 11px; color: #6c757d; margin-top: 4px; }
        .step.active .step-label { color: #0d6efd; font-weight: bold; }
    </style>
    @yield('styles')
</head>
<body>
    <div class="kiosk-container">
        @yield('content')
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @yield('scripts')
</body>
</html>