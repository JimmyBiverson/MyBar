<div class="card">
    <div class="card-header"><i class="fas fa-boxes me-2"></i>Inventory Report</div>
    <div class="card-body">
        <div class="row g-3 mb-3">
            <div class="col-md-3">
                <div class="border rounded p-3 text-center">
                    <small class="text-muted">Total Products</small>
                    <h4 class="mb-0">{{ $data['total_products'] ?? 0 }}</h4>
                </div>
            </div>
            <div class="col-md-3">
                <div class="border rounded p-3 text-center bg-danger-subtle">
                    <small class="text-muted">Low Stock</small>
                    <h4 class="mb-0 text-danger">{{ $data['low_stock'] ?? 0 }}</h4>
                </div>
            </div>
            <div class="col-md-3">
                <div class="border rounded p-3 text-center">
                    <small class="text-muted">Total Stock Value</small>
                    <h4 class="mb-0 text-primary">{{ number_format($data['total_value'] ?? 0) }}</h4>
                </div>
            </div>
            <div class="col-md-3">
                <div class="border rounded p-3 text-center">
                    <small class="text-muted">Avg. Cost Price</small>
                    <h4 class="mb-0">{{ number_format($data['average_cost'] ?? 0) }}</h4>
                </div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr><th>Product</th><th>Category</th><th class="text-end">Stock</th><th class="text-end">Cost Price</th><th class="text-end">Selling Price</th><th class="text-end">Stock Value</th><th>Status</th></tr>
                </thead>
                <tbody>
                    @forelse($data['products'] ?? [] as $product)
                    <tr>
                        <td>{{ $product->name }}</td>
                        <td>{{ $product->category->name ?? 'N/A' }}</td>
                        <td class="text-end">{{ $product->current_stock }}</td>
                        <td class="text-end">{{ number_format($product->cost_price, 0) }}</td>
                        <td class="text-end">{{ number_format($product->selling_price, 0) }}</td>
                        <td class="text-end">{{ number_format($product->current_stock * $product->cost_price, 0) }}</td>
                        <td>
                            <span class="badge bg-{{ $product->current_stock <= $product->reorder_level ? 'danger' : ($product->current_stock <= $product->reorder_level * 2 ? 'warning' : 'success') }}">
                                {{ $product->current_stock <= $product->reorder_level ? 'Low' : ($product->current_stock <= $product->reorder_level * 2 ? 'Medium' : 'Good') }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center py-3 text-muted">No products</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
