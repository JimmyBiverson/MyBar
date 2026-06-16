<div class="card report-card">
    <div class="card-header"><i class="fas fa-boxes me-2"></i>Inventory Report</div>
    <div class="card-body">
        <div class="row g-3 mb-3">
            <div class="col-md-3 col-6">
                <div class="stat-box text-center">
                    <small class="text-muted">Total Products</small>
                    <h4 class="mb-0">{{ $data['total_products'] ?? 0 }}</h4>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-box text-center" style="background:#fbe9e7">
                    <small class="text-muted">Low Stock Items</small>
                    <h4 class="mb-0 text-danger">{{ $data['low_stock'] ?? 0 }}</h4>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-box text-center">
                    <small class="text-muted">Total Stock Value</small>
                    <h4 class="mb-0 text-primary">{{ formatCurrency($data['total_value'] ?? 0) }}</h4>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-box text-center">
                    <small class="text-muted">Avg. Cost Price</small>
                    <h4 class="mb-0">{{ formatCurrency($data['average_cost'] ?? 0) }}</h4>
                </div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover table-sm">
                <thead class="table-light">
                    <tr>
                        <th>Product</th>
                        <th>Category</th>
                        <th class="text-end">Stock</th>
                        <th class="text-end">Cost Price</th>
                        <th class="text-end">Selling Price</th>
                        <th class="text-end">Stock Value</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data['products'] ?? [] as $product)
                    <tr>
                        <td class="fw-medium">{{ $product->name }}</td>
                        <td>{{ $product->category->name ?? 'N/A' }}</td>
                        <td class="text-end">{{ $product->current_stock }}</td>
                        <td class="text-end">{{ formatCurrency((float) $product->cost_price) }}</td>
                        <td class="text-end">{{ formatCurrency((float) $product->selling_price) }}</td>
                        <td class="text-end">{{ formatCurrency((float) $product->current_stock * (float) $product->cost_price) }}</td>
                        <td>
                            <span class="badge bg-{{ $product->stock_status === 'low' ? 'danger' : ($product->stock_status === 'medium' ? 'warning' : 'success') }}">
                                {{ ucfirst($product->stock_status) }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center py-3 text-muted">No products found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
