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
                        <th class="text-center">Stock Status</th>
                        <th class="text-end">Stock</th>
                        <th class="text-end">Qty Sold</th>
                        <th class="text-end">Revenue</th>
                        <th class="text-end">Cost</th>
                        <th class="text-end">Profit</th>
                        <th class="text-end">Margin</th>
                    </tr>
                </thead>
                <tbody>
                    @php $productItems = $products ?? $data['products'] ?? []; @endphp
                    @forelse($productItems as $key => $product)
                    @php $prod = $product->product ?? null; @endphp
                    <tr>
                        <td>{{ $key + 1 }}</td>
                        <td class="fw-medium">{{ $prod->name ?? $product['name'] ?? 'N/A' }}</td>
                        <td>{{ $prod->category->name ?? $product['category'] ?? 'N/A' }}</td>
                        <td class="text-center">
                            @if($prod)
                            <span class="badge bg-{{ $prod->stock_status === 'low' ? 'danger' : ($prod->stock_status === 'medium' ? 'warning' : 'success') }}">
                                {{ ucfirst($prod->stock_status) }}
                            </span>
                            @else
                            <span class="text-muted">N/A</span>
                            @endif
                        </td>
                        <td class="text-end">{{ $prod->current_stock ?? 0 }}</td>
                        <td class="text-end">{{ $product->total_qty ?? $product['total_qty'] ?? 0 }}</td>
                        <td class="text-end">{{ number_format($product->total_revenue ?? $product['total_revenue'] ?? 0) }}</td>
                        <td class="text-end">{{ number_format($product->total_cost ?? $product['total_cost'] ?? 0) }}</td>
                        <td class="text-end" style="color:{{ ($product->total_profit ?? $product['total_profit'] ?? 0) >= 0 ? '#2e7d32' : '#c62828' }}">
                            {{ number_format($product->total_profit ?? $product['total_profit'] ?? 0) }}
                        </td>
                        <td class="text-end">{{ $product->margin ?? $product['margin'] ?? 0 }}%</td>
                    </tr>
                    @empty
                    <tr><td colspan="10" class="text-center py-3 text-muted">No product data</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
