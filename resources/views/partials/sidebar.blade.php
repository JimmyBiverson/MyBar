<nav class="sidebar" x-data="sidebarNav()" :class="{ collapsed: $root.closest('.sidebar-collapsed') }">
    <div class="sidebar-brand">
        <a href="{{ route('dashboard') }}">
            <i class="fas fa-glass-cheers"></i>
            <span class="brand-text">MyBar</span>
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
            @if(auth()->user()->isAdmin() || auth()->user()->isManager())
            <a href="{{ route('tables.index') }}" class="menu-item {{ request()->routeIs('tables.*') ? 'active' : '' }}">
                <i class="fas fa-chair"></i>
                <span>Tables</span>
            </a>
            @endif
            @if(auth()->user()->isAdmin() || auth()->user()->isManager() || auth()->user()->isCashier())
            <a href="{{ route('pos.index') }}" class="menu-item {{ request()->routeIs('pos.*') ? 'active' : '' }}">
                <i class="fas fa-cash-register"></i>
                <span>POS Terminal</span>
            </a>
            @endif
            @if(auth()->user()->isAdmin() || auth()->user()->isManager() || auth()->user()->isCashier() || auth()->user()->isAccountant())
            <a href="{{ route('billing.index') }}" class="menu-item {{ request()->routeIs('billing.*') ? 'active' : '' }}">
                <i class="fas fa-receipt"></i>
                <span>Sales</span>
            </a>
            @endif
        </div>

        @if(auth()->user()->isWaiter())
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

        @if(auth()->user()->isAdmin() || auth()->user()->isManager() || auth()->user()->isStoreKeeper())
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

        @if(auth()->user()->isAdmin() || auth()->user()->isManager() || auth()->user()->isCashier() || auth()->user()->isStoreKeeper() || auth()->user()->isAccountant())
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

        @if(auth()->user()->isAdmin() || auth()->user()->isManager() || auth()->user()->isCashier() || auth()->user()->isKitchen())
        <div class="menu-section">
            <div class="menu-label">Operations</div>
            <a href="{{ route('orders.index') }}" class="menu-item {{ request()->routeIs('orders.*') ? 'active' : '' }}">
                <i class="fas fa-clipboard-list"></i>
                <span>Orders</span>
            </a>
            @if(auth()->user()->isKitchen())
            <a href="{{ route('kitchen.index') }}" class="menu-item {{ request()->routeIs('kitchen.*') ? 'active' : '' }}">
                <i class="fas fa-utensils"></i>
                <span>Kitchen Display</span>
            </a>
            @endif
            @if(auth()->user()->isAdmin() || auth()->user()->isManager() || auth()->user()->isAccountant())
            <a href="{{ route('expenses.index') }}" class="menu-item {{ request()->routeIs('expenses.*') ? 'active' : '' }}">
                <i class="fas fa-money-bill-wave"></i>
                <span>Expenses</span>
            </a>
            @endif
        </div>
        @endif

        @if(auth()->user()->isAdmin() || auth()->user()->isManager() || auth()->user()->isAccountant() || auth()->user()->isStoreKeeper())
        <div class="menu-section">
            <div class="menu-label">Reports</div>
            <a href="{{ route('reports.index') }}" class="menu-item {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                <i class="fas fa-chart-bar"></i>
                <span>Reports</span>
            </a>
        </div>
        @endif

        @if(auth()->user()->isAdmin())
        <div class="menu-section">
            <div class="menu-label">Administration</div>
            <a href="{{ route('users.index') }}" class="menu-item {{ request()->routeIs('users.*') ? 'active' : '' }}">
                <i class="fas fa-user-shield"></i>
                <span>Users & Roles</span>
            </a>
            <a href="{{ route('activities.index') }}" class="menu-item {{ request()->routeIs('activities.*') ? 'active' : '' }}">
                <i class="fas fa-history"></i>
                <span>Activity Logs</span>
            </a>
            <a href="{{ route('settings.index') }}" class="menu-item {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                <i class="fas fa-cog"></i>
                <span>Settings</span>
            </a>
        </div>
        @endif
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
    </style>

    <script>
        function sidebarNav() {
            return {
                init() {
                    const sidebar = this.$el;
                    const observer = new ResizeObserver(() => {
                        sidebar.classList.toggle('collapsed', document.querySelector('.sidebar-collapsed') !== null);
                    });
                    observer.observe(document.querySelector('.app-layout'));
                }
            }
        }
    </script>
</nav>