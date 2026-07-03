<nav class="sidebar" x-data="sidebarNav()" :class="{ collapsed: $root.closest('.sidebar-collapsed') }">
    <div class="sidebar-brand">
        <a href="{{ route('dashboard') }}" class="d-flex align-items-center gap-2">
            @if(\App\Models\Setting::get('site_logo'))
                <img src="{{ \App\Models\Setting::get('site_logo') }}" alt="logo" style="max-height: 30px; max-width: 30px; object-fit: contain;">
            @else
                <i class="fas fa-glass-cheers" style="color: var(--primary);"></i>
            @endif
            <span class="brand-text">{{ \App\Models\Setting::get('business_name', 'MyBar') }}</span>
        </a>
        <button class="btn btn-link sidebar-close d-none" @click="$dispatch('toggle-sidebar')" style="color:#fff;font-size:1.2rem;padding:0;margin-left:auto;">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <div class="sidebar-menu">
        <div class="menu-section">
            <div class="menu-label">Main</div>
            <a href="{{ route('dashboard') }}" class="menu-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="fas fa-th-large"></i>
                <span>Dashboard</span>
            </a>
            @if(auth()->user()?->isAdmin() || auth()->user()?->isManager())
            <a href="{{ route('tables.index') }}" class="menu-item {{ request()->routeIs('tables.*') ? 'active' : '' }}">
                <i class="fas fa-chair"></i>
                <span>Tables</span>
            </a>
            @endif
            @if(auth()->user()?->isAdmin() || auth()->user()?->isManager() || auth()->user()?->isCashier())
            <a href="{{ route('pos.index') }}" class="menu-item {{ request()->routeIs('pos.*') ? 'active' : '' }}">
                <i class="fas fa-cash-register"></i>
                <span>POS Terminal</span>
            </a>
            @endif
            @if(auth()->user()?->isAdmin() || auth()->user()?->isManager() || auth()->user()?->isCashier() || auth()->user()?->isAccountant())
            <a href="{{ route('billing.index') }}" class="menu-item {{ request()->routeIs('billing.*') ? 'active' : '' }}">
                <i class="fas fa-receipt"></i>
                <span>Sales</span>
            </a>
            @endif
        </div>

        @if(auth()->user()?->isWaiter())
        <div class="menu-section">
            <div class="menu-label">Waiter</div>
            <a href="{{ route('waiter.index') }}" class="menu-item {{ request()->routeIs('waiter.index') ? 'active' : '' }}">
                <i class="fas fa-concierge-bell"></i>
                <span>Dashboard</span>
            </a>
            <a href="{{ route('waiter.orders') }}" class="menu-item {{ request()->routeIs('waiter.orders') ? 'active' : '' }}">
                <i class="fas fa-clipboard-list"></i>
                <span>My Orders</span>
            </a>
            <a href="{{ route('waiter.orders.create') }}" class="menu-item {{ request()->routeIs('waiter.orders.create') ? 'active' : '' }}">
                <i class="fas fa-plus-circle"></i>
                <span>New Order</span>
            </a>
        </div>
        @endif

        @if(auth()->user()?->isAdmin() || auth()->user()?->isManager() || auth()->user()?->isStoreKeeper())
        <div class="menu-section">
            <div class="menu-label">Inventory</div>
            <a href="{{ route('products.index') }}" class="menu-item {{ request()->routeIs('products.*') ? 'active' : '' }}">
                <i class="fas fa-box"></i>
                <span>Products</span>
            </a>
            <a href="{{ route('categories.index') }}" class="menu-item {{ request()->routeIs('categories.*') ? 'active' : '' }}">
                <i class="fas fa-tags"></i>
                <span>Categories</span>
            </a>
            <a href="{{ route('units.index') }}" class="menu-item {{ request()->routeIs('units.*') ? 'active' : '' }}">
                <i class="fas fa-ruler"></i>
                <span>Units</span>
            </a>
            <a href="{{ route('batches.index') }}" class="menu-item {{ request()->routeIs('batches.*') ? 'active' : '' }}">
                <i class="fas fa-layer-group"></i>
                <span>Batches</span>
            </a>
            <a href="{{ route('purchases.index') }}" class="menu-item {{ request()->routeIs('purchases.*') ? 'active' : '' }}">
                <i class="fas fa-truck-loading"></i>
                <span>Purchases</span>
            </a>
        </div>
        @endif

        @if(auth()->user()?->isAdmin() || auth()->user()?->isManager() || auth()->user()?->isCashier() || auth()->user()?->isStoreKeeper() || auth()->user()?->isAccountant())
        <div class="menu-section">
            <div class="menu-label">People</div>
            <a href="{{ route('suppliers.index') }}" class="menu-item {{ request()->routeIs('suppliers.*') ? 'active' : '' }}">
                <i class="fas fa-handshake"></i>
                <span>Suppliers</span>
            </a>
            <a href="{{ route('customers.index') }}" class="menu-item {{ request()->routeIs('customers.*') ? 'active' : '' }}">
                <i class="fas fa-users"></i>
                <span>Customers</span>
            </a>
        </div>
        @endif

        @if(auth()->user()?->isAdmin() || auth()->user()?->isManager() || auth()->user()?->isCashier() || auth()->user()?->isKitchen())
        <div class="menu-section">
            <div class="menu-label">Operations</div>
            <a href="{{ route('orders.index') }}" class="menu-item {{ request()->routeIs('orders.*') ? 'active' : '' }}">
                <i class="fas fa-clipboard-list"></i>
                <span>Orders</span>
                <span class="badge bg-warning ms-auto" x-show="pendingOrdersCount > 0" x-text="pendingOrdersCount"></span>
            </a>
            @if(auth()->user()?->isKitchen())
            <a href="{{ route('kitchen.index') }}" class="menu-item {{ request()->routeIs('kitchen.*') ? 'active' : '' }}">
                <i class="fas fa-utensils"></i>
                <span>Kitchen Display</span>
            </a>
            @endif
            @if(auth()->user()?->isAdmin() || auth()->user()?->isManager() || auth()->user()?->isCashier() || auth()->user()?->isAccountant())
            <a href="{{ route('expenses.index') }}" class="menu-item {{ request()->routeIs('expenses.*') ? 'active' : '' }}">
                <i class="fas fa-money-bill-wave"></i>
                <span>Expenses</span>
            </a>
            @endif
        </div>
        @endif

        @if(auth()->user()?->isAdmin() || auth()->user()?->isManager() || auth()->user()?->isAccountant() || auth()->user()?->isStoreKeeper())
        <div class="menu-section">
            <div class="menu-label">Reports</div>
            <a href="{{ route('reports.index') }}" class="menu-item {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                <i class="fas fa-chart-bar"></i>
                <span>Reports</span>
            </a>
        </div>
        @endif

        @if(auth()->user()?->isAdmin() || auth()->user()?->isManager())
        <div class="menu-section">
            <div class="menu-label">{{ auth()->user()?->isManager() ? 'Administration' : 'Administration' }}</div>
            @if(auth()->user()?->isAdmin() || auth()->user()?->isManager())
            <a href="{{ route('users.index') }}" class="menu-item {{ request()->routeIs('users.*') ? 'active' : '' }}">
                <i class="fas fa-user-shield"></i>
                <span>Users & Roles</span>
            </a>
            <a href="{{ route('activities.index') }}" class="menu-item {{ request()->routeIs('activities.*') ? 'active' : '' }}">
                <i class="fas fa-history"></i>
                <span>Activity Logs</span>
            </a>
            @endif
            <a href="{{ route('settings.index') }}" class="menu-item {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                <i class="fas fa-cog"></i>
                <span>Settings</span>
            </a>
        </div>
        @endif

        <!-- Mobile App Card -->
        <div class="m-3 p-3 rounded text-center border-0" style="background: rgba(255,255,255,0.05); color: #fff;">
            <i class="fas fa-mobile-alt mb-2" style="font-size: 1.5rem; color: var(--primary);"></i>
            <h6 class="small fw-semibold mb-1">Mobile App</h6>
            <p class="x-small text-muted mb-2" style="font-size: 0.72rem; line-height: 1.2;">
                <i class="fas fa-qrcode me-1"></i> Scan to open on your phone
            </p>
            <div class="mb-2 d-flex justify-content-center">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data={{ urlencode(url('/')) }}"
                     alt="QR Code" style="width:90px;height:90px;border-radius:6px;">
            </div>
            <a href="{{ route('apk.download') }}" class="btn btn-sm btn-primary w-100">
                <i class="fas fa-download me-1"></i> Download Android App
            </a>
        </div>

        <!-- Desktop App Card -->
        <div id="pwa-install-card" class="m-3 p-3 rounded border-0" style="background: rgba(255,255,255,0.05); color: #fff;">
            <div class="text-center mb-2">
                <i class="fas fa-laptop" style="font-size: 1.3rem; color: var(--primary);"></i>
            </div>
            <h6 class="small fw-semibold text-center mb-1">Desktop App</h6>
            <p class="x-small text-muted text-center mb-2" style="font-size: 0.72rem; line-height: 1.2;">
                Install as app or create a shortcut with your custom branding icon.
            </p>
            <button id="pwa-install-btn" class="btn btn-sm btn-outline-light w-100 mb-1" style="display:none;">
                <i class="fas fa-download me-1"></i> Install as App
            </button>
            <a href="{{ route('desktop.shortcut') }}" class="btn btn-sm btn-outline-light w-100">
                <i class="fas fa-external-link-alt me-1"></i> Create Desktop Shortcut
            </a>
        </div>

        <!-- Offline Sync Status Card -->
        <div id="offline-sync-card" class="m-3 p-3 rounded border-0" style="background: rgba(255,255,255,0.05); color: #fff;" x-data="offlineSync()">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="small fw-semibold">Connection</span>
                <span class="badge" :class="isOnline ? 'bg-success' : 'bg-danger'">
                    <i class="fas" :class="isOnline ? 'fa-wifi' : 'fa-wifi-slash'"></i>
                    <span x-text="isOnline ? 'Online' : 'Offline'"></span>
                </span>
            </div>
            <template x-if="offlineCount > 0">
                <div>
                    <p class="x-small mb-2 text-warning" style="font-size: 0.72rem; line-height: 1.2;">
                        <i class="fas fa-exclamation-triangle me-1"></i>
                        <span x-text="offlineCount + ' transaction(s) pending sync.'"></span>
                    </p>
                    <button class="btn btn-sm btn-outline-warning w-100" @click="syncData()" :disabled="syncing || !isOnline">
                        <i class="fas" :class="syncing ? 'fa-spinner fa-spin' : 'fa-sync-alt'"></i>
                        <span x-text="syncing ? 'Syncing...' : 'Sync Now'"></span>
                    </button>
                </div>
            </template>
            <template x-if="offlineCount === 0">
                <p class="x-small mb-0 text-muted" style="font-size: 0.72rem;">
                    <i class="fas fa-check-circle text-success me-1"></i> All data is synced.
                </p>
            </template>
        </div>
    </div>

    <style>
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: linear-gradient(180deg, #1e1e2f 0%, #2a2a40 100%);
            color: #a2a3b7;
            z-index: 1040;
            transition: width 0.3s ease;
            overflow-y: auto;
            overflow-x: hidden;
        }
        .sidebar-collapsed .sidebar { width: var(--sidebar-collapsed-width); }
        .sidebar-brand {
            padding: 1rem;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }
        .sidebar-brand a {
            color: #fff;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 1.25rem;
            font-weight: 700;
        }
        .sidebar-brand i { font-size: 1.5rem; color: #7367f0; }
        .sidebar-collapsed .sidebar-brand .brand-text { display: none; }
        .sidebar-menu { padding: 0.75rem 0; }
        .menu-label {
            padding: 0.75rem 1.25rem 0.25rem;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #6c6d8a;
            font-weight: 600;
        }
        .sidebar-collapsed .menu-label { display: none; }
        .menu-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.6rem 1.25rem;
            color: #a2a3b7;
            text-decoration: none;
            transition: all 0.2s;
            border-left: 3px solid transparent;
            white-space: nowrap;
        }
        .menu-item:hover {
            color: #fff;
            background: rgba(115,103,240,0.08);
        }
        .menu-item.active {
            color: #fff;
            background: rgba(115,103,240,0.15);
            border-left-color: #7367f0;
        }
        .menu-item i { width: 20px; text-align: center; font-size: 1rem; }
        .sidebar-collapsed .menu-item span { display: none; }
        .sidebar-collapsed .menu-item { justify-content: center; padding: 0.6rem 0; }
        .sidebar::-webkit-scrollbar { width: 3px; }
        .sidebar::-webkit-scrollbar-thumb { background: #3a3a50; border-radius: 3px; }
        .sidebar-collapsed #pwa-install-card,
        .sidebar-collapsed #offline-sync-card,
        .sidebar-collapsed .sidebar-menu > div.m-3:first-of-type {
            display: none !important;
        }
    </style>

    <script>
        function sidebarNav() {
            return {
                pendingOrdersCount: 0,
                init() {
                    const sidebar = this.$el;
                    const observer = new ResizeObserver(() => {
                        sidebar.classList.toggle('collapsed', document.querySelector('.sidebar-collapsed') !== null);
                    });
                    observer.observe(document.querySelector('.app-layout'));
                    this.fetchPendingCount();
                },
                async fetchPendingCount() {
                    try {
                        const resp = await fetch('{{ route('pos.pending-count') }}');
                        const data = await resp.json();
                        this.pendingOrdersCount = data.count || 0;
                    } catch(e) { this.pendingOrdersCount = 0; }
                    setTimeout(() => this.fetchPendingCount(), 15000);
                }
            }
        }
    </script>
</nav>