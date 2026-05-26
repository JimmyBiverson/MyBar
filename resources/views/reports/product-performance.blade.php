<div class="card">
    <div class="card-header"><i class="fas fa-trophy me-2"></i>Product Performance Report</div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Product</th>
                        <th>Category</th>
                        <th class="text-end">Qty Sold</th>
                        <th class="text-end">Revenue</th>
                        <th class="text-end">Cost</th>
                        <th class="text-end">Profit</th>
                        <th class="text-end">Margin</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data['products'] ?? [] as $key => $product)
                    <tr>
                        <td>{{ $key + 1 }}</td>
                        <td class="fw-medium">{{ $product->name ?? $product['name'] ?? 'N/A' }}</td>
                        <td>{{ $product->category->name ?? $product['category'] ?? 'N/A' }}</td>
                        <td class="text-end">{{ $product->total_qty ?? $product['total_qty'] ?? 0 }}</td>
                        <td class="text-end">{{ number_format($product->total_revenue ?? $product['total_revenue'] ?? 0) }}</td>
                        <td class="text-end">{{ number_format($product->total_cost ?? $product['total_cost'] ?? 0) }}</td>
                        <td class="text-end" style="color:{{ ($product->total_profit ?? $product['total_profit'] ?? 0) >= 0 ? '#2e7d32' : '#c62828' }}">
                            {{ number_format($product->total_profit ?? $product['total_profit'] ?? 0) }}
                        </td>
                        <td class="text-end">{{ $product->margin ?? $product['margin'] ?? 0 }}%</td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center py-3 text-muted">No product data</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
