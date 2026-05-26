<div class="card">
    <div class="card-header"><i class="fas fa-sun me-2"></i>Daily Sales Report</div>
    <div class="card-body">
        <div class="row g-3 mb-3">
            <div class="col-md-3">
                <div class="border rounded p-3 text-center">
                    <small class="text-muted">Total Sales</small>
                    <h4 class="mb-0 text-primary">{{ number_format($data['total_sales'] ?? 0) }}</h4>
                </div>
            </div>
            <div class="col-md-3">
                <div class="border rounded p-3 text-center">
                    <small class="text-muted">Transactions</small>
                    <h4 class="mb-0">{{ $data['total_transactions'] ?? 0 }}</h4>
                </div>
            </div>
            <div class="col-md-3">
                <div class="border rounded p-3 text-center">
                    <small class="text-muted">Avg. Per Transaction</small>
                    <h4 class="mb-0 text-success">{{ number_format($data['average_per_transaction'] ?? 0) }}</h4>
                </div>
            </div>
            <div class="col-md-3">
                <div class="border rounded p-3 text-center">
                    <small class="text-muted">Total Discounts</small>
                    <h4 class="mb-0 text-danger">{{ number_format($data['total_discounts'] ?? 0) }}</h4>
                </div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr><th>Date</th><th>Invoice #</th><th>Customer</th><th>Items</th><th class="text-end">Total</th><th class="text-end">Payment</th></tr>
                </thead>
                <tbody>
                    @forelse($data['bills'] ?? [] as $bill)
                    <tr>
                        <td>{{ $bill->created_at->format('d M Y') }}</td>
                        <td>#{{ $bill->invoice_no ?? $bill->id }}</td>
                        <td>{{ $bill->customer->name ?? 'Walk-in' }}</td>
                        <td>{{ $bill->items->count() }}</td>
                        <td class="text-end">{{ number_format($bill->total, 0) }}</td>
                        <td class="text-end">{{ number_format($bill->paid, 0) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center py-3 text-muted">No data</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
