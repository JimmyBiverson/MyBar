<header class="topbar">
    <div class="topbar-left">
        <button class="btn btn-link topbar-btn" @click="toggleSidebar()" id="sidebarToggle">
            <i class="fas fa-bars"></i>
        </button>
        <div class="branch-selector dropdown">
            <button class="btn btn-link topbar-btn dropdown-toggle" data-bs-toggle="dropdown">
                <i class="fas fa-store-alt me-1"></i>
                <span>{{ session('branch_name', 'Main Branch') }}</span>
            </button>
            <ul class="dropdown-menu">
                @foreach($branches ?? [] as $branch)
                    <li><a class="dropdown-item" href="{{ route('branch.switch', $branch->id) }}">{{ $branch->name }}</a></li>
                @endforeach
            </ul>
        </div>
    </div>

    <div class="topbar-right">
        <span id="offlineBadge" class="d-none badge bg-warning text-dark">
            <i class="fas fa-wifi-slash me-1"></i> Offline
        </span>

        <div class="dropdown stock-alert-dropdown">
            @include('partials.stock-alert')
        </div>

        <div class="dropdown notification-dropdown">
            <button class="btn btn-link topbar-btn position-relative dropdown-toggle" data-bs-toggle="dropdown" title="Notifications">
                <i class="fas fa-bell"></i>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary" style="font-size:0.6rem; padding:0.2rem 0.4rem; display:none;" id="notificationBadge">0</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end p-2 notification-menu" style="width:300px; max-width:90vw;">
                <li class="dropdown-header px-2 py-1 fw-semibold small">Recent Notifications</li>
                <li><hr class="dropdown-divider my-1"></li>
                <li class="px-2 py-2 text-center text-muted small">
                    <i class="fas fa-check-circle text-success mb-1"></i><br>
                    No new notifications
                </li>
            </ul>
        </div>

        <button class="btn btn-link topbar-btn" @click="toggleDarkMode()" title="Toggle Dark Mode">
            <i class="fas" :class="darkMode ? 'fa-sun' : 'fa-moon'"></i>
        </button>

        <div class="dropdown user-dropdown">
            <button class="btn btn-link topbar-btn dropdown-toggle" data-bs-toggle="dropdown">
                <div class="user-avatar">
                    <i class="fas fa-user-circle fa-lg"></i>
                </div>
                <span class="user-name d-none d-md-inline">{{ auth()->user()->name ?? 'User' }}</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li>
                    <a class="dropdown-item" href="{{ route('profile.show') }}">
                        <i class="fas fa-user me-2"></i> Profile
                    </a>
                </li>
                @can('settings.access')
                <li>
                    <a class="dropdown-item" href="{{ route('settings.index') }}">
                        <i class="fas fa-cog me-2"></i> Settings
                    </a>
                </li>
                @endcan
                <li><hr class="dropdown-divider"></li>
                <li>
                    <a class="dropdown-item text-danger" href="{{ route('logout') }}"
                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="fas fa-sign-out-alt me-2"></i> Logout
                    </a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                        @csrf
                    </form>
                </li>
            </ul>
        </div>
    </div>

    <style>
        .topbar {
            height: var(--topbar-height);
            background: #fff;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 1.25rem;
            position: sticky;
            top: 0;
            z-index: 1030;
            box-shadow: 0 1px 4px rgba(0,0,0,0.04);
        }
        .dark .topbar, .dark-mode .topbar {
            background: #2a2d33;
            border-color: #3a3d45;
        }
        .topbar-left, .topbar-right {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .topbar-btn {
            color: #6c757d;
            font-size: 1.15rem;
            padding: 0.4rem 0.6rem;
            text-decoration: none;
            border-radius: 8px;
        }
        .topbar-btn:hover { background: rgba(0,0,0,0.04); color: #495057; }
        .dark .topbar-btn, .dark-mode .topbar-btn { color: #b2b9c5; }
        .dark .topbar-btn:hover, .dark-mode .topbar-btn:hover { background: rgba(255,255,255,0.05); }
        .dark .dropdown-menu, .dark-mode .dropdown-menu {
            background: #2a2d33;
            border-color: #3a3d45;
        }
        .dark .dropdown-item, .dark-mode .dropdown-item { color: #b2b9c5; }
        .dark .dropdown-item:hover, .dark-mode .dropdown-item:hover { background: #1e2126; color: #fff; }
        .dark .dropdown-divider, .dark-mode .dropdown-divider { border-color: #3a3d45; }
        .user-avatar { display: inline-flex; align-items: center; }
        .user-name { margin-left: 0.5rem; font-weight: 500; font-size: 0.9rem; }
        .branch-selector .dropdown-toggle { font-size: 0.9rem; }
        .branch-selector .dropdown-toggle::after { display: none; }
        .notification-dropdown .dropdown-toggle::after { display: none; }
        @media (max-width: 768px) {
            .branch-selector span { display: none; }
        }
    </style>
</header>
