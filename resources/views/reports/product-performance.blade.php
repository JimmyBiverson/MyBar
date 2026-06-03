<div class="card report-card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-trophy me-2"></i>Product Performance Report</span>
        <small class="text-muted">{{ $data['report_period'] ?? '' }}</small>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover table-sm">
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
                    @forelse($data['products'] ?? [] as $key => $item)
                    @php $prod = $item->product ?? null; @endphp
                    <tr>
                        <td>{{ $key + 1 }}</td>
                        <td class="fw-medium">{{ $prod->name ?? 'N/A' }}</td>
                        <td>{{ $prod->category->name ?? 'N/A' }}</td>
                        <td class="text-center">
                            @if($prod)
                            <span class="badge bg-{{ $prod->stock_status === 'low' ? 'danger' : ($prod->stock_status === 'medium' ? 'warning' : 'success') }}">
                                {{ ucfirst($prod->stock_status) }}
                            </span>
                            @else
                            <span class="text-muted small">N/A</span>
                            @endif
                        </td>
                        <td class="text-end">{{ $prod->current_stock ?? 0 }}</td>
                        <td class="text-end">{{ (int) ($item->total_qty ?? 0) }}</td>
                        <td class="text-end">{{ number_format((float) ($item->total_revenue ?? 0), 0) }}</td>
                        <td class="text-end">{{ number_format((float) ($item->total_cost ?? 0), 0) }}</td>
                        <td class="text-end" style="color:{{ ($item->total_profit ?? 0) >= 0 ? '#2e7d32' : '#c62828' }}">
                            {{ number_format((float) ($item->total_profit ?? 0), 0) }}
                        </td>
                        <td class="text-end">{{ $item->margin ?? 0 }}%</td>
                    </tr>
                    @empty
                    <tr><td colspan="10" class="text-center py-3 text-muted">No product data for this period</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
