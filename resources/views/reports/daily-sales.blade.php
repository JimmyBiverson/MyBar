<div class="card report-card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-sun me-2"></i>Daily Sales Report</span>
        <small class="text-muted">{{ $data['report_date'] ?? '' }}</small>
    </div>
    <div class="card-body">
        <div class="row g-3 mb-3">
            <div class="col-md-3 col-6">
                <div class="stat-box text-center">
                    <small class="text-muted">Total Sales</small>
                    <h4 class="mb-0 text-primary">{{ formatCurrency($data['total_sales'] ?? 0) }}</h4>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-box text-center">
                    <small class="text-muted">Transactions</small>
                    <h4 class="mb-0">{{ $data['total_transactions'] ?? 0 }}</h4>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-box text-center">
                    <small class="text-muted">Avg. Per Transaction</small>
                    <h4 class="mb-0 text-success">{{ formatCurrency($data['average_per_transaction'] ?? 0) }}</h4>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-box text-center">
                    <small class="text-muted">Payment Methods</small>
                    <h4 class="mb-0 text-info">{{ count($data['payment_methods'] ?? []) }}</h4>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-6 col-6">
                <div class="stat-box text-center" style="background:#fbe9e7">
                    <small class="text-muted">Total Expenses</small>
                    <h4 class="mb-0 text-danger">{{ formatCurrency($data['total_expenses'] ?? 0) }}</h4>
                </div>
            </div>
            <div class="col-md-6 col-6">
                <div class="stat-box text-center" style="background:{{ ($data['net_profit'] ?? 0) >= 0 ? '#e8f5e9' : '#fbe9e7' }}">
                    <small class="text-muted">Net Profit / Loss</small>
                    <h4 class="mb-0" style="color:{{ ($data['net_profit'] ?? 0) >= 0 ? '#2e7d32' : '#c62828' }}">
                        {{ formatCurrency($data['net_profit'] ?? 0) }}
                    </h4>
                </div>
            </div>
        </div>

        @if(count($data['payment_methods'] ?? []) > 0)
        <div class="row g-2 mb-3">
            @foreach($data['payment_methods'] as $pm)
            <div class="col-md-3 col-6">
                <div class="border rounded p-2 text-center">
                    <small class="text-muted">{{ ucfirst($pm->payment_method) }}</small>
                    <h5 class="mb-0">{{ formatCurrency((float) $pm->total) }}</h5>
                    <small class="text-muted">{{ $pm->count }} transactions</small>
                </div>
            </div>
            @endforeach
        </div>
        @endif

        <div class="table-responsive">
            <table class="table table-hover table-sm">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Invoice</th>
                        <th>Customer</th>
                        <th>Items</th>
                        <th class="text-end">Total</th>
                        <th class="text-end">Paid</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data['bills'] ?? [] as $i => $bill)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>#{{ $bill->invoice_no ?? $bill->id }}</td>
                        <td>{{ $bill->customer->name ?? 'Walk-in' }}</td>
                        <td>{{ $bill->items_count }}</td>
                        <td class="text-end">{{ formatCurrency((float) $bill->total_amount) }}</td>
                        <td class="text-end">{{ formatCurrency((float) $bill->paid_amount) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center py-3 text-muted">No transactions found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
