<!DOCTYPE html>
<html lang="id" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sistem Absensi QR Code')</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <!-- DataTables CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.bootstrap5.min.css">
    
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #0d6efd;
            --secondary-color: #6c757d;
            --success-color: #198754;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --light-color: #f8f9fa;
            --dark-color: #212529;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding-bottom: 60px; /* For bottom navigation */
        }
        
        .navbar-brand {
            font-weight: 600;
        }
        
        .card {
            border-radius: 12px;
            border: none;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 1.5rem;
        }
        
        .card-header {
            border-radius: 12px 12px 0 0 !important;
            font-weight: 600;
        }
        
        .btn {
            border-radius: 8px;
            font-weight: 500;
            padding: 0.5rem 1.5rem;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-success {
            background-color: var(--success-color);
            border-color: var(--success-color);
        }
        
        .btn-warning {
            background-color: var(--warning-color);
            border-color: var(--warning-color);
            color: var(--dark-color);
        }
        
        .btn-danger {
            background-color: var(--danger-color);
            border-color: var(--danger-color);
        }
        
        .stat-card {
            text-align: center;
            padding: 1.5rem;
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .stat-label {
            font-size: 0.9rem;
            color: var(--secondary-color);
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .attendance-status {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }
        
        .status-hadir { background-color: #d1e7dd; color: #0f5132; }
        .status-izin { background-color: #fff3cd; color: #856404; }
        .status-tidak-hadir { background-color: #f8d7da; color: #721c24; }
        .status-pending { background-color: #cff4fc; color: #055160; }
        
        /* Mobile-first adjustments */
        @media (max-width: 768px) {
            .container {
                padding-left: 12px;
                padding-right: 12px;
            }
            
            .container.py-4 {
                padding-top: 1rem !important;
                padding-bottom: 1rem !important;
            }
            
            .card {
                margin-bottom: 0.75rem;
                border-radius: 10px;
            }
            
            .card-header {
                padding: 0.75rem 1rem;
                font-size: 0.95rem;
            }
            
            .card-body {
                padding: 1rem;
            }
            
            .btn {
                width: 100%;
                margin-bottom: 0.5rem;
                padding: 0.625rem 1rem;
                font-size: 0.95rem;
            }
            
            .btn-group .btn {
                width: auto;
                margin-bottom: 0;
            }
            
            .table-responsive {
                font-size: 0.85rem;
                margin: 0 -1rem;
                padding: 0 1rem;
                width: calc(100% + 2rem);
            }
            
            .table {
                margin-bottom: 0.5rem;
            }
            
            .table th, .table td {
                padding: 0.5rem 0.375rem;
            }
            
            .stat-number {
                font-size: 1.75rem;
            }
            
            .stat-label {
                font-size: 0.8rem;
            }
            
            .alert {
                margin-bottom: 0.75rem;
                padding: 0.75rem 1rem;
                font-size: 0.9rem;
            }
            
            .modal-dialog {
                margin: 0.5rem;
            }
            
            .modal-content {
                border-radius: 12px;
            }
            
            .form-control, .form-select {
                padding: 0.625rem 0.75rem;
                font-size: 0.95rem;
            }
            
            h2.h4 {
                font-size: 1.25rem;
                margin-bottom: 0.5rem;
            }
            
            .text-muted {
                font-size: 0.85rem;
            }
            
            .row {
                margin-left: -0.5rem;
                margin-right: -0.5rem;
            }
            
            .row > [class*="col-"] {
                padding-left: 0.5rem;
                padding-right: 0.5rem;
            }
            
            .mb-4 {
                margin-bottom: 1rem !important;
            }
            
            .mt-4 {
                margin-top: 1rem !important;
            }
            
            .py-4 {
                padding-top: 1rem !important;
                padding-bottom: 1rem !important;
            }
        }
        
        /* Extra small devices (phones, less than 576px) */
        @media (max-width: 575.98px) {
            .container {
                padding-left: 10px;
                padding-right: 10px;
            }
            
            .stat-card {
                padding: 1rem 0.5rem;
            }
            
            .stat-number {
                font-size: 1.5rem;
            }
            
            .table-responsive {
                font-size: 0.8rem;
            }
            
            .bottom-nav-item i {
                font-size: 1.25rem;
            }
            
            .bottom-nav-label {
                font-size: 0.7rem;
            }
        }
        
        /* Prevent horizontal overflow */
        body {
            overflow-x: hidden;
            max-width: 100vw;
        }
        
        .container {
            max-width: 100%;
        }
        
        /* Bottom navigation for mobile */
        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: white;
            border-top: 1px solid #dee2e6;
            padding: 0.5rem 0;
            z-index: 1000;
            display: none;
        }
        
        .bottom-nav-item {
            text-align: center;
            color: var(--secondary-color);
            text-decoration: none;
            padding: 0.5rem;
            flex: 1;
        }
        
        .bottom-nav-item.active {
            color: var(--primary-color);
        }
        
        .bottom-nav-item i {
            font-size: 1.5rem;
            display: block;
            margin-bottom: 0.25rem;
        }
        
        .bottom-nav-label {
            font-size: 0.75rem;
        }
        
        @media (max-width: 768px) {
            .bottom-nav {
                display: flex;
            }
            
            body {
                padding-bottom: 70px;
            }
        }
        
        /* QR Scanner styles */
        #qr-reader {
            width: 100%;
            max-width: 500px;
            margin: 0 auto;
        }
        
        #qr-reader__dashboard_section {
            padding: 1rem;
        }
        
        /* Chart container */
        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
        }
    </style>
    
    <!-- Print Styles -->
    <style media="print">
        @page {
            size: A4;
            margin: 0;
        }
        
        body {
            margin: 0;
            padding: 20px;
        }
        
        .navbar, .bottom-nav, .alert, .card-header:not(.print-header), 
        .card-body > .row:not(.print-row), .btn, hr {
            display: none !important;
        }
        
        .card {
            border: none !important;
            box-shadow: none !important;
            margin: 0 !important;
        }
        
        .print-header {
            background: white !important;
            color: black !important;
            border-bottom: 2px solid #000;
        }
        
        .print-row {
            display: flex !important;
        }
        
        .text-center {
            text-align: center !important;
        }
        
        .mb-3 {
            margin-bottom: 1rem !important;
        }
        
        .mb-0 {
            margin-bottom: 0 !important;
        }
        
        .mt-3 {
            margin-top: 1rem !important;
        }
        
        svg {
            max-width: 300px !important;
            height: auto !important;
        }
    </style>
    
    @stack('styles')
</head>
<body>
    <!-- Top Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="{{ route('dashboard.' . (Auth::user()->role ?? 'siswa')) }}">
                <i class="bi bi-qr-code-scan"></i> Absensi QR
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    @auth
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle"></i> {{ Auth::user()->name }}
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('dashboard.' . Auth::user()->role) }}">
                                    <i class="bi bi-speedometer2"></i> Dashboard
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}" id="logout-form">
                                        @csrf
                                        <a class="dropdown-item" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                            <i class="bi bi-box-arrow-right"></i> Logout
                                        </a>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @else
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}">Login</a>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="container py-4">
        <!-- Flash Messages -->
        <div id="flash-messages">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            
            @if(session('warning'))
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill"></i> {{ session('warning') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            
            @if(session('info'))
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    <i class="bi bi-info-circle-fill"></i> {{ session('info') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
        </div>
        
        @yield('content')
    </main>

    <!-- Bottom Navigation (Mobile Only) -->
    @auth
    <nav class="bottom-nav">
        <a href="{{ route('dashboard.' . Auth::user()->role) }}" class="bottom-nav-item {{ request()->routeIs('dashboard.*') ? 'active' : '' }}">
            <i class="bi bi-speedometer2"></i>
            <span class="bottom-nav-label">Dashboard</span>
        </a>
        
        @if(Auth::user()->role === 'siswa')
        <a href="{{ route('attendance.scanner') }}" class="bottom-nav-item {{ request()->routeIs('attendance.*') ? 'active' : '' }}">
            <i class="bi bi-qr-code-scan"></i>
            <span class="bottom-nav-label">Scan QR</span>
        </a>
        
        <a href="{{ route('permission.create') }}" class="bottom-nav-item {{ request()->routeIs('permission.create') ? 'active' : '' }}">
            <i class="bi bi-clipboard-plus"></i>
            <span class="bottom-nav-label">Izin</span>
        </a>
        @endif
        
        @if(in_array(Auth::user()->role, ['admin', 'guru']))
        <a href="{{ route('monitoring.index') }}" class="bottom-nav-item {{ request()->routeIs('monitoring.*') ? 'active' : '' }}">
            <i class="bi bi-graph-up"></i>
            <span class="bottom-nav-label">Monitoring</span>
        </a>
        
        <a href="{{ route('permission.index') }}" class="bottom-nav-item {{ request()->routeIs('permission.index') ? 'active' : '' }}">
            <i class="bi bi-clipboard-check"></i>
            <span class="bottom-nav-label">Verifikasi</span>
        </a>
        @endif
        
        @if(Auth::user()->role === 'admin')
        <a href="{{ route('qr.generate') }}" class="bottom-nav-item {{ request()->routeIs('qr.*') ? 'active' : '' }}">
            <i class="bi bi-qr-code"></i>
            <span class="bottom-nav-label">QR Code</span>
        </a>
        @endif
    </nav>
    @endauth

    <!-- Bootstrap 5 JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- DataTables -->
    <script type="text/javascript" src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/responsive/2.4.1/js/responsive.bootstrap5.min.js"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <script>
        // Initialize DataTables
        $(document).ready(function() {
            $('.data-table').DataTable({
                responsive: true,
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json'
                }
            });
            
            // Start session keep-alive
            startSessionKeepAlive();
            
            // Start CSRF token refresh
            startCsrfTokenRefresh();
        });
        
        // Auto-dismiss alerts after 5 seconds
        setTimeout(function() {
            $('.alert').alert('close');
        }, 5000);
        
        // CSRF token setup for AJAX
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        
        // Session keep-alive function - ping server every 5 minutes
        function startSessionKeepAlive() {
            setInterval(function() {
                $.ajax({
                    url: '{{ route("dashboard." . (Auth::user()->role ?? "siswa")) }}',
                    method: 'GET',
                    data: { keep_alive: true },
                    success: function(response) {
                        console.log('Session kept alive:', new Date().toLocaleTimeString());
                    },
                    error: function(xhr, status, error) {
                        console.log('Session keep-alive failed:', error);
                    }
                });
            }, 300000); // 5 minutes = 300000 ms
        }
        
        // CSRF token refresh function - refresh every 30 minutes
        function startCsrfTokenRefresh() {
            setInterval(function() {
                $.ajax({
                    url: '{{ route("login") }}', // Use login page to get fresh CSRF token
                    method: 'GET',
                    data: { csrf_refresh: true },
                    success: function(response) {
                        // Extract new CSRF token from response
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(response, 'text/html');
                        const newToken = doc.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                        
                        if (newToken) {
                            // Update meta tag
                            $('meta[name="csrf-token"]').attr('content', newToken);
                            
                            // Update AJAX headers
                            $.ajaxSetup({
                                headers: {
                                    'X-CSRF-TOKEN': newToken
                                }
                            });
                            
                            // Update all forms with CSRF token
                            $('input[name="_token"]').val(newToken);
                            
                            console.log('CSRF token refreshed:', new Date().toLocaleTimeString());
                        }
                    },
                    error: function(xhr, status, error) {
                        console.log('CSRF token refresh failed:', error);
                    }
                });
            }, 1800000); // 30 minutes = 1800000 ms
        }
        
        // Handle form submissions with CSRF token retry
        $(document).on('submit', 'form', function(e) {
            const form = $(this);
            const originalSubmit = form.find('button[type="submit"], input[type="submit"]');
            
            // Skip AJAX for login and user creation forms to allow normal submission
            if (form.attr('action') && (
                form.attr('action').includes('/login') || 
                form.attr('action').includes('/users/store') ||
                form.attr('action').includes('/users/update')
            )) {
                console.log('Form detected for normal submission:', form.attr('action'));
                return true; // Allow normal form submission
            }
            
            // Store original button text and disable
            const originalHtml = originalSubmit.html();
            originalSubmit.prop('disabled', true).html('<i class="bi bi-arrow-clockwise spin"></i> Processing...');
            
            // Add retry logic for CSRF token errors
            const submitForm = function(retryCount = 0) {
                $.ajax({
                    url: form.attr('action'),
                    method: form.attr('method'),
                    data: form.serialize(),
                    success: function(response) {
                        // Handle success
                        if (response.redirect) {
                            window.location.href = response.redirect;
                        } else if (response.success) {
                            // Show success message
                            showToast('success', response.success);
                            setTimeout(() => {
                                if (response.reload) {
                                    location.reload();
                                }
                            }, 1500);
                        } else {
                            // If no JSON response, assume normal form submission worked
                            originalSubmit.prop('disabled', false).html(originalHtml);
                        }
                    },
                    error: function(xhr, status, error) {
                        if (xhr.status === 419) { // CSRF token mismatch
                            if (retryCount < 2) {
                                // Try to refresh CSRF token and retry
                                refreshCsrfTokenAndRetry(form, retryCount + 1);
                            } else {
                                showToast('error', 'Session expired. Please refresh the page and try again.');
                                originalSubmit.prop('disabled', false).html(originalHtml);
                            }
                        } else {
                            showToast('error', 'An error occurred. Please try again.');
                            originalSubmit.prop('disabled', false).html(originalHtml);
                        }
                    }
                });
            };
            
            // Start submission
            submitForm();
            
            // Prevent default form submission
            e.preventDefault();
        });
        
        // Function to refresh CSRF token and retry form submission
        function refreshCsrfTokenAndRetry(form, retryCount) {
            $.ajax({
                url: '{{ route("login") }}',
                method: 'GET',
                data: { csrf_refresh: true },
                success: function(response) {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(response, 'text/html');
                    const newToken = doc.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                    
                    if (newToken) {
                        // Update token
                        $('meta[name="csrf-token"]').attr('content', newToken);
                        $.ajaxSetup({
                            headers: {
                                'X-CSRF-TOKEN': newToken
                            }
                        });
                        form.find('input[name="_token"]').val(newToken);
                        
                        // Retry form submission
                        setTimeout(() => {
                            form.submit();
                        }, 500);
                    }
                }
            });
        }
        
        // Toast notification function
        function showToast(type, message) {
            const toastClass = type === 'success' ? 'text-bg-success' : 'text-bg-danger';
            const icon = type === 'success' ? 'bi-check-circle' : 'bi-exclamation-triangle';
            
            const toast = $(`
                <div class="toast align-items-center ${toastClass} border-0 position-fixed bottom-0 end-0 m-3" role="alert">
                    <div class="d-flex">
                        <div class="toast-body">
                            <i class="bi ${icon}"></i> ${message}
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                    </div>
                </div>
            `);
            
            $('body').append(toast);
            const bsToast = new bootstrap.Toast(toast[0]);
            bsToast.show();
            
            setTimeout(() => {
                toast.remove();
            }, 5000);
        }
        
        // Add spin animation for loading icons
        const style = document.createElement('style');
        style.textContent = `
            .spin {
                animation: spin 1s linear infinite;
            }
            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
            .toast {
                z-index: 9999;
            }
        `;
        document.head.appendChild(style);
    </script>
    
    @stack('scripts')
</body>
</html>
    
