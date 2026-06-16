<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="app()" :class="{ 'dark': darkMode }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', \App\Models\Setting::get('business_name', 'MyBar POS')) - {{ \App\Models\Setting::get('business_name', 'MyBar POS') }}</title>

    @if(\App\Models\Setting::get('favicon'))
        <link rel="icon" type="image/x-icon" href="{{ \App\Models\Setting::get('favicon') }}">
    @else
        <link rel="icon" type="image/x-icon" href="/favicon.ico">
    @endif

    <link rel="manifest" href="{{ route('manifest') }}">
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
            --primary: {{ \App\Models\Setting::get('accent_color', '#7367f0') }};
            --primary-dark: {{ \App\Models\Setting::get('accent_color_dark', '#5e50ee') }};
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
        
        /* Pagination styling - reduce arrow sizes */
        .pagination {
            --bs-pagination-font-size: 0.875rem;
            gap: 0.25rem;
        }
        .pagination .page-link {
            border-radius: 6px;
            border: 1px solid #dee2e6;
            color: #6c757d;
            padding: 0.375rem 0.75rem;
            font-size: 0.875rem;
        }
        .pagination .page-item:first-child .page-link,
        .pagination .page-item:last-child .page-link {
            font-size: 1rem;
            padding: 0.375rem 0.65rem;
        }
        .pagination .page-item.active .page-link {
            background-color: var(--primary);
            border-color: var(--primary);
            color: #fff;
        }
        .pagination .page-link:hover {
            background-color: #f8f9fa;
            border-color: #dee2e6;
            color: var(--primary);
        }
        .dark .pagination .page-link, .dark-mode .pagination .page-link {
            background: #2a2d33;
            border-color: #3a3d45;
            color: #b2b9c5;
        }
        .dark .pagination .page-item.active .page-link, .dark-mode .pagination .page-item.active .page-link {
            background-color: var(--primary);
            border-color: var(--primary);
            color: #fff;
        }
        .dark .pagination .page-link:hover, .dark-mode .pagination .page-link:hover {
            background-color: #3a3d45;
            border-color: #4a4d55;
        }
        
        /* Target Tailwind/Laravel default pagination SVG arrows and make them smaller */
        nav[role="navigation"] svg,
        .pagination svg,
        nav svg {
            width: 1rem !important;
            height: 1rem !important;
            max-width: 1rem !important;
            max-height: 1rem !important;
        }
        
        /* Mobile responsive pagination */
        @media (max-width: 576px) {
            .pagination {
                font-size: 0.75rem;
                gap: 0.15rem;
            }
            .pagination .page-link {
                padding: 0.25rem 0.5rem;
                font-size: 0.75rem;
            }
            .pagination .page-item:first-child .page-link,
            .pagination .page-item:last-child .page-link {
                font-size: 0.85rem;
                padding: 0.25rem 0.45rem;
            }
            /* Even smaller arrows on mobile */
            nav[role="navigation"] svg,
            .pagination svg,
            nav svg {
                width: 0.75rem !important;
                height: 0.75rem !important;
                max-width: 0.75rem !important;
                max-height: 0.75rem !important;
            }
        }
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
                &copy; {{ date('Y') }} {{ \App\Models\Setting::get('business_name', 'MyBar') }}. All rights reserved.
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
        window.currencySettings = {
            symbol: '{{ \App\Models\Setting::get('currency_symbol', 'UGX') }}',
            position: '{{ \App\Models\Setting::get('currency_position', 'before') }}',
            thousand_separator: '{{ \App\Models\Setting::get('thousand_separator', ',') }}',
            decimal_separator: '{{ \App\Models\Setting::get('decimal_separator', '.') }}',
            decimal_digits: {{ (int) \App\Models\Setting::get('decimal_digits', 0) }},
        };
    </script>
    <script>
        // Define caching fallbacks to prevent JavaScript crashes if app.js is not loaded
        window._chartCenterTextCounter = 0; // unique IDs for per-chart center-text plugins
        window.cacheAppData = window.cacheAppData || function(products, categories, settings) {
            try {
                if (products) localStorage.setItem('mybar_products', JSON.stringify(products));
                if (categories) localStorage.setItem('mybar_categories', JSON.stringify(categories));
                if (settings) localStorage.setItem('mybar_settings', JSON.stringify(settings));
            } catch (e) {
                console.warn('Failed to cache app data:', e);
            }
        };

        window.getCachedProducts = window.getCachedProducts || function() {
            try {
                const data = localStorage.getItem('mybar_products');
                return data ? JSON.parse(data) : [];
            } catch (e) {
                return [];
            }
        };

        window.getCachedCategories = window.getCachedCategories || function() {
            try {
                const data = localStorage.getItem('mybar_categories');
                return data ? JSON.parse(data) : [];
            } catch (e) {
                return [];
            }
        };

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

        // PWA & Service Worker Registration
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then(reg => console.log('Service Worker registered successfully:', reg.scope))
                    .catch(err => console.error('Service Worker registration failed:', err));
            });
        }

        // Global OfflineSyncManager
        window.OfflineSyncManager = {
            getTransactions() {
                try {
                    return JSON.parse(localStorage.getItem('mybar_offline_transactions') || '[]');
                } catch (e) {
                    return [];
                }
            },
            saveTransaction(tx) {
                const txs = this.getTransactions();
                txs.push(tx);
                localStorage.setItem('mybar_offline_transactions', JSON.stringify(txs));
                window.dispatchEvent(new CustomEvent('offline-sync-update', { detail: { count: txs.length } }));
            },
            clearTransactions() {
                localStorage.removeItem('mybar_offline_transactions');
                window.dispatchEvent(new CustomEvent('offline-sync-update', { detail: { count: 0 } }));
            },
            removeTransaction(index) {
                const txs = this.getTransactions();
                txs.splice(index, 1);
                localStorage.setItem('mybar_offline_transactions', JSON.stringify(txs));
                window.dispatchEvent(new CustomEvent('offline-sync-update', { detail: { count: txs.length } }));
            },
            async syncAll() {
                const txs = this.getTransactions();
                if (txs.length === 0) return 0;
                
                let successCount = 0;
                for (let i = 0; i < txs.length; i++) {
                    const tx = txs[i];
                    try {
                        const url = tx.type === 'order' ? '{{ route('waiter.orders.store') }}' : '{{ route('pos.payment') }}';
                        const resp = await fetch(url, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify(tx.data)
                        });
                        const data = await resp.json();
                        if (data.success || data.id) {
                            successCount++;
                        } else {
                            console.error('Failed to sync offline item:', data.message || 'unknown error');
                        }
                    } catch (e) {
                        console.error('Network error during offline sync:', e);
                        break; // Stop syncing if network error happens again
                    }
                }
                
                // Remove successfully synced items
                const currentTxs = this.getTransactions();
                currentTxs.splice(0, successCount);
                localStorage.setItem('mybar_offline_transactions', JSON.stringify(currentTxs));
                window.dispatchEvent(new CustomEvent('offline-sync-update', { detail: { count: currentTxs.length } }));
                
                return successCount;
            }
        };

        // PWA Install Event Handler
        let deferredPrompt;
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
            const installBtn = document.getElementById('pwa-install-btn');
            if (installBtn) {
                installBtn.style.display = '';
            }
        });

        document.addEventListener('DOMContentLoaded', () => {
            const installBtn = document.getElementById('pwa-install-btn');
            if (installBtn) {
                installBtn.addEventListener('click', async () => {
                    if (deferredPrompt) {
                        deferredPrompt.prompt();
                        const { outcome } = await deferredPrompt.userChoice;
                        if (outcome === 'accepted') {
                            console.log('User accepted the install prompt');
                        }
                        deferredPrompt = null;
                        installBtn.style.display = 'none';
                    }
                });
            }
        });

        // Offline Status Indicators
        window.addEventListener('online', () => {
            const banner = document.getElementById('offlineBanner');
            if (banner) banner.classList.add('d-none');
            // Auto sync when online
            setTimeout(() => {
                window.OfflineSyncManager.syncAll().then(count => {
                    if (count > 0) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Synced!',
                            text: `Successfully uploaded ${count} offline transaction(s).`,
                            timer: 3000,
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false
                        });
                    }
                });
            }, 1000);
        });

        window.addEventListener('offline', () => {
            const banner = document.getElementById('offlineBanner');
            if (banner) banner.classList.remove('d-none');
        });

        // Offline Sync Alpine Component
        function offlineSync() {
            return {
                isOnline: navigator.onLine,
                offlineCount: 0,
                syncing: false,
                init() {
                    this.offlineCount = window.OfflineSyncManager.getTransactions().length;
                    window.addEventListener('online', () => { this.isOnline = true; });
                    window.addEventListener('offline', () => { this.isOnline = false; });
                    window.addEventListener('offline-sync-update', (e) => {
                        this.offlineCount = e.detail ? e.detail.count : window.OfflineSyncManager.getTransactions().length;
                    });
                    
                    // Double check initial status
                    if (!this.isOnline) {
                        const banner = document.getElementById('offlineBanner');
                        if (banner) banner.classList.remove('d-none');
                    }
                },
                async syncData() {
                    this.syncing = true;
                    try {
                        const count = await window.OfflineSyncManager.syncAll();
                        if (count > 0) {
                            Swal.fire('Synced!', `Successfully synced ${count} items.`, 'success');
                        } else if (this.offlineCount > 0) {
                            Swal.fire('Partial Sync / Error', 'Some items could not be synced. Check connection.', 'warning');
                        }
                    } catch (e) {
                        Swal.fire('Sync Failed', 'Failed to synchronize data.', 'error');
                    } finally {
                        this.syncing = false;
                        this.offlineCount = window.OfflineSyncManager.getTransactions().length;
                    }
                }
            }
        }
    </script>

    {{-- Auto-lock after 30 min of inactivity --}}
    <script>
        (function() {
            let timeout;
            const INACTIVITY_MS = 1800000;

            function resetTimer() {
                if (timeout) clearTimeout(timeout);
                timeout = setTimeout(() => {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '{{ route('lock') }}';
                    const csrf = document.createElement('input');
                    csrf.type = 'hidden';
                    csrf.name = '_token';
                    csrf.value = '{{ csrf_token() }}';
                    form.appendChild(csrf);
                    document.body.appendChild(form);
                    form.submit();
                }, INACTIVITY_MS);
            }

            document.addEventListener('mousemove', resetTimer, { passive: true });
            document.addEventListener('keydown', resetTimer, { passive: true });
            document.addEventListener('click', resetTimer, { passive: true });
            document.addEventListener('touchstart', resetTimer, { passive: true });
            document.addEventListener('scroll', resetTimer, { passive: true });
            resetTimer();
        })();
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0/dist/chartjs-plugin-datalabels.min.js"></script>
    <script>
        // Register ChartDataLabels globally as soon as both libs are available
        if (typeof Chart !== 'undefined' && typeof ChartDataLabels !== 'undefined') {
            Chart.register(ChartDataLabels);
        }
    </script>

    @stack('scripts')

    {{-- Alpine must load LAST so x-init can see Chart.js already loaded --}}
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js" defer></script>
</body>
</html>
