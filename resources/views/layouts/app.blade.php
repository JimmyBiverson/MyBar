<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="app()" :class="{ 'dark': darkMode }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'MyBar POS')) - {{ config('app.name') }}</title>

    <link rel="manifest" href="/manifest.json">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    @stack('styles')

    <style>
        :root {
            --sidebar-width: 260px;
            --sidebar-collapsed-width: 70px;
            --topbar-height: 60px;
            --primary: #7367f0;
            --primary-dark: #5e50ee;
        }
        * { font-family: 'Poppins', sans-serif; }
        body {
            background: #f8f9fa;
            min-height: 100vh;
        }
        .dark body, .dark-mode body {
            background: #1a1d21;
            color: #b2b9c5;
        }
        .app-layout { display: flex; min-height: 100vh; }
        .main-content {
            margin-left: var(--sidebar-width);
            flex: 1;
            transition: margin-left 0.3s ease;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        .sidebar-collapsed .main-content { margin-left: var(--sidebar-collapsed-width); }
        .page-content { padding: 1.5rem; flex: 1; }
        .dark .card, .dark-mode .card {
            background: #2a2d33;
            border-color: #3a3d45;
            color: #b2b9c5;
        }
        .dark .table, .dark-mode .table { color: #b2b9c5; }
        .dark .table-hover tbody tr:hover, .dark-mode .table-hover tbody tr:hover { color: #e0e0e0; }
        .dark .modal-content, .dark-mode .modal-content {
            background: #2a2d33;
            color: #b2b9c5;
        }
        .dark .btn-close, .dark-mode .btn-close { filter: invert(1); }
        .dark .form-control, .dark-mode .form-control,
        .dark .form-select, .dark-mode .form-select {
            background: #1e2126;
            border-color: #3a3d45;
            color: #b2b9c5;
        }
        .dark .form-control:focus, .dark-mode .form-control:focus,
        .dark .form-select:focus, .dark-mode .form-select:focus {
            background: #1e2126;
            color: #fff;
        }
        .dark .text-muted, .dark-mode .text-muted { color: #6c757d !important; }
        .dark .table-striped > tbody > tr:nth-of-type(odd), .dark-mode .table-striped > tbody > tr:nth-of-type(odd) { color: #b2b9c5; }
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
            margin-bottom: 1.25rem;
        }
        .card-header {
            background: transparent;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            padding: 1rem 1.25rem;
            font-weight: 600;
        }
        .btn { border-radius: 8px; font-weight: 500; }
        .btn-primary { background: var(--primary); border-color: var(--primary); }
        .btn-primary:hover { background: var(--primary-dark); border-color: var(--primary-dark); }
        .badge { font-weight: 500; border-radius: 6px; }
        .page-title { font-size: 1.5rem; font-weight: 600; margin-bottom: 1.5rem; }
        .breadcrumb-plugins { display: flex; gap: 0.5rem; align-items: center; }
        .sidebar-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.5);
            z-index: 1039;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s ease;
        }
        .sidebar-backdrop.show {
            opacity: 1;
            pointer-events: auto;
        }
        @media (max-width: 991px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease !important;
                z-index: 1050 !important;
            }
            .mobile-sidebar-open .sidebar {
                transform: translateX(0);
            }
            .sidebar-collapsed .sidebar { width: var(--sidebar-width) !important; }
            .main-content { margin-left: 0 !important; }
            .page-content { padding: 1rem; }
            .sidebar-close {
                display: flex !important;
            }
        }
        @media (max-width: 576px) {
            .page-content { padding: 0.75rem; }
            .page-title { font-size: 1.25rem; }
            .card-body { padding: 0.75rem; }
            .card-header { padding: 0.6rem 0.75rem; font-size: 0.85rem; }
            .table { font-size: 0.8rem; }
            .table td, .table th { padding: 0.4rem 0.5rem; }
            .topbar { padding: 0 0.6rem; }
            .topbar-btn { font-size: 1rem; padding: 0.3rem 0.4rem; }
            .navbar .badge, .notification-menu, .stock-alert-menu { font-size: 0.75rem; }
            h4.page-title { font-size: 1.1rem; }
            .btn-sm { font-size: 0.75rem; padding: 0.25rem 0.5rem; }
        }
        @media (hover: hover) {
            .product-card:hover { border-color: #7367f0; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(115,103,240,0.15); }
        }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #c1c1c1; border-radius: 3px; }
        .dark ::-webkit-scrollbar-thumb { background: #555; }
    </style>
</head>
<body>
    <div id="offlineBanner" class="d-none bg-warning text-dark text-center py-1 small fw-semibold">
        <i class="fas fa-wifi-slash me-1"></i> You are currently offline. Some features may be limited.
    </div>

    <div class="sidebar-backdrop" :class="{ show: mobileSidebarOpen }" @click="mobileSidebarOpen = false"></div>

    <div class="app-layout" :class="{ 'sidebar-collapsed': sidebarCollapsed, 'mobile-sidebar-open': mobileSidebarOpen }">
        @include('partials.sidebar')

        <div class="main-content">
            @include('partials.navbar')

            <div class="page-content">
                <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-start align-items-sm-center mb-3 gap-2">
                    <h4 class="page-title mb-0">@yield('page-title')</h4>
                    <div class="breadcrumb-plugins">@yield('breadcrumb-plugins')</div>
                </div>

                @yield('content')
            </div>

            <footer class="text-center py-3 text-muted small">
                &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
            </footer>
        </div>
    </div>

    <!-- Toast notifications -->
    <div x-data="toastHandler()" x-init="init()" class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 9999;">
        <template x-for="(toast, i) in toasts" :key="i">
            <div class="toast show align-items-center border-0 mb-2" :class="'text-bg-' + toast.type" role="alert">
                <div class="d-flex">
                    <div class="toast-body" x-text="toast.message"></div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" @click="toasts.splice(i, 1)"></button>
                </div>
            </div>
        </template>
    </div>

    <script>
        function toastHandler() {
            return {
                toasts: [],
                init() {
                    @if (session('success'))
                        this.addToast('success', '{{ session('success') }}');
                    @endif
                    @if (session('error'))
                        this.addToast('danger', '{{ session('error') }}');
                    @endif
                    @if (session('warning'))
                        this.addToast('warning', '{{ session('warning') }}');
                    @endif
                },
                addToast(type, message) {
                    this.toasts.push({ type, message });
                    setTimeout(() => {
                        this.toasts.shift();
                    }, 5000);
                }
            }
        }

        function app() {
            return {
                darkMode: localStorage.getItem('darkMode') === 'true',
                sidebarCollapsed: localStorage.getItem('sidebarCollapsed') === 'true',
                mobileSidebarOpen: false,
                init() {
                    if (this.darkMode) {
                        document.documentElement.classList.add('dark');
                        document.body.classList.add('dark-mode');
                    }
                    this.checkMobile();
                    window.addEventListener('resize', () => this.checkMobile());
                    window.addEventListener('toggle-sidebar', () => this.toggleSidebar());
                },
                checkMobile() {
                    if (window.innerWidth > 991 && this.mobileSidebarOpen) {
                        this.mobileSidebarOpen = false;
                    }
                },
                toggleDarkMode() {
                    this.darkMode = !this.darkMode;
                    localStorage.setItem('darkMode', this.darkMode);
                    document.documentElement.classList.toggle('dark', this.darkMode);
                    document.body.classList.toggle('dark-mode', this.darkMode);
                },
                toggleSidebar() {
                    if (window.innerWidth <= 991) {
                        this.mobileSidebarOpen = !this.mobileSidebarOpen;
                    } else {
                        this.sidebarCollapsed = !this.sidebarCollapsed;
                        localStorage.setItem('sidebarCollapsed', this.sidebarCollapsed);
                    }
                },
                formatCurrency(amount) {
                    const s = window.currencySettings || { symbol: 'UGX', position: 'before', thousand_separator: ',', decimal_separator: '.', decimal_digits: 0 };
                    const val = parseFloat(amount) || 0;
                    const formatted = val.toFixed(s.decimal_digits || 0)
                        .replace(/\B(?=(\d{3})+(?!\d))/g, s.thousand_separator || ',');
                    return s.position === 'before'
                        ? s.symbol + ' ' + formatted
                        : formatted + ' ' + s.symbol;
                }
            }
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>

    @stack('scripts')
</body>
</html>
