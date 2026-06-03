<button class="btn btn-link topbar-btn position-relative dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" title="Stock Alerts">
    <i class="fas fa-bell"></i>
    @if($lowStockCount > 0 || $mediumStockCount > 0)
        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-{{ $lowStockCount > 0 ? 'danger' : 'warning' }}" style="font-size:0.6rem; padding:0.2rem 0.4rem;">
            {{ ($lowStockCount + $mediumStockCount) > 9 ? '9+' : ($lowStockCount + $mediumStockCount) }}
        </span>
    @endif
</button>
<ul class="dropdown-menu dropdown-menu-end p-0 stock-alert-menu" style="width:320px; max-width:90vw;">
    <li class="dropdown-header d-flex justify-content-between align-items-center px-3 py-2" style="font-weight:600; background:#f8f9fa; border-radius:8px 8px 0 0;">
        <span><i class="fas fa-exclamation-triangle text-warning me-1"></i> Stock Status</span>
        @if($lowStockCount > 0)
            <span class="badge bg-danger">{{ $lowStockCount }} low</span>
        @endif
        @if($mediumStockCount > 0)
            <span class="badge bg-warning text-dark">{{ $mediumStockCount }} medium</span>
        @endif
    </li>
    @forelse($lowStockItems as $item)
        <li>
            <a class="dropdown-item px-3 py-2" href="{{ route('products.edit', $item->id) }}" style="border-bottom:1px solid #f0f0f0;">
                <div class="d-flex justify-content-between">
                    <strong class="small">{{ $item->name }}</strong>
                    <span class="badge bg-danger">{{ $item->current_stock }}</span>
                </div>
                <small class="text-muted">Low stock</small>
            </a>
        </li>
    @empty
        @forelse($mediumStockItems as $item)
        <li>
            <a class="dropdown-item px-3 py-2" href="{{ route('products.edit', $item->id) }}" style="border-bottom:1px solid #f0f0f0;">
                <div class="d-flex justify-content-between">
                    <strong class="small">{{ $item->name }}</strong>
                    <span class="badge bg-warning text-dark">{{ $item->current_stock }}</span>
                </div>
                <small class="text-muted">Medium stock</small>
            </a>
        </li>
        @empty
        <li class="px-3 py-3 text-center text-muted small">
            <i class="fas fa-check-circle text-success fa-2x mb-2"></i><br>
            All stock levels are healthy
        </li>
        @endforelse
    @endforelse
    @if($lowStockCount + $mediumStockCount > 5)
        <li>
            <a class="dropdown-item text-center small py-2 text-primary fw-semibold" href="{{ route('products.index') }}">
                View all ({{ $lowStockCount + $mediumStockCount }}) <i class="fas fa-arrow-right ms-1"></i>
            </a>
        </li>
    @endif
</ul>

<style>
    .stock-alert-menu .dropdown-header { border-bottom: 1px solid #e9ecef; }
    .dark .stock-alert-menu .dropdown-header, .dark-mode .stock-alert-menu .dropdown-header { background: #1e2126 !important; border-color: #3a3d45; }
</style>
