<div class="card">
    <div class="card-header"><i class="fas fa-file-invoice-dollar me-2"></i>Profit & Loss Report</div>
    <div class="card-body">
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="border rounded p-3 text-center bg-success-subtle">
                    <small class="text-muted">Total Revenue</small>
                    <h4 class="mb-0 text-success">{{ number_format($data['total_revenue'] ?? 0) }}</h4>
                </div>
            </div>
            <div class="col-md-4">
                <div class="border rounded p-3 text-center bg-danger-subtle">
                    <small class="text-muted">Total Expenses</small>
                    <h4 class="mb-0 text-danger">{{ number_format($data['total_expenses'] ?? 0) }}</h4>
                </div>
            </div>
            <div class="col-md-4">
                <div class="border rounded p-3 text-center" style="background:{{ ($data['net_profit'] ?? 0) >= 0 ? '#e8f5e9' : '#fbe9e7' }}">
                    <small class="text-muted">Net Profit / Loss</small>
                    <h4 class="mb-0" style="color:{{ ($data['net_profit'] ?? 0) >= 0 ? '#2e7d32' : '#c62828' }}">
                        {{ number_format($data['net_profit'] ?? 0) }}
                    </h4>
                </div>
            </div>
        </div>

        <h6 class="fw-semibold mb-2">Revenue Breakdown</h6>
        <div class="table-responsive mb-3">
            <table class="table table-hover table-sm">
                <thead class="table-light">
                    <tr><th>Source</th><th class="text-end">Amount</th><th class="text-end">% of Total</th></tr>
                </thead>
                <tbody>
                    <tr><td>Product Sales</td><td class="text-end">{{ number_format($data['product_sales'] ?? 0) }}</td><td class="text-end">{{ $data['product_sales_percent'] ?? 0 }}%</td></tr>
                    <tr><td>Service Charges</td><td class="text-end">{{ number_format($data['service_charges'] ?? 0) }}</td><td class="text-end">{{ $data['service_charges_percent'] ?? 0 }}%</td></tr>
                    <tr class="fw-bold"><td>Total Revenue</td><td class="text-end text-success">{{ number_format($data['total_revenue'] ?? 0) }}</td><td class="text-end">100%</td></tr>
                </tbody>
            </table>
        </div>

        <h6 class="fw-semibold mb-2">Expense Breakdown</h6>
        <div class="table-responsive">
            <table class="table table-hover table-sm">
                <thead class="table-light">
                    <tr><th>Category</th><th class="text-end">Amount</th></tr>
                </thead>
                <tbody>
                    @forelse($data['expense_categories'] ?? [] as $cat)
                    <tr><td>{{ $cat['name'] ?? $cat->name ?? 'N/A' }}</td><td class="text-end text-danger">{{ number_format($cat['total'] ?? $cat->total ?? 0) }}</td></tr>
                    @empty
                    <tr><td colspan="2" class="text-center text-muted py-2">No expenses</td></tr>
                    @endforelse
                    <tr class="fw-bold"><td>Total Expenses</td><td class="text-end text-danger">{{ number_format($data['total_expenses'] ?? 0) }}</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
