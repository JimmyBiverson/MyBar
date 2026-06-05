<div class="card report-card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-calendar-alt me-2"></i>Monthly Sales Report</span>
        <small class="text-muted">{{ $data['report_month'] ?? '' }}</small>
    </div>
    <div class="card-body">
        <div class="row g-3 mb-3">
            <div class="col-md-4 col-6">
                <div class="stat-box text-center">
                    <small class="text-muted">Total Sales</small>
                    <h4 class="mb-0 text-primary">{{ number_format($data['total_sales'] ?? 0) }}</h4>
                </div>
            </div>
            <div class="col-md-4 col-6">
                <div class="stat-box text-center">
                    <small class="text-muted">Transactions</small>
                    <h4 class="mb-0">{{ $data['total_transactions'] ?? 0 }}</h4>
                </div>
            </div>
            <div class="col-md-4 col-6">
                <div class="stat-box text-center">
                    <small class="text-muted">Avg. Daily Sales</small>
                    <h4 class="mb-0 text-success">{{ number_format($data['average_daily'] ?? 0) }}</h4>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-6 col-6">
                <div class="stat-box text-center" style="background:#fbe9e7">
                    <small class="text-muted">Total Expenses</small>
                    <h4 class="mb-0 text-danger">{{ number_format($data['total_expenses'] ?? 0) }}</h4>
                </div>
            </div>
            <div class="col-md-6 col-6">
                <div class="stat-box text-center" style="background:{{ ($data['net_profit'] ?? 0) >= 0 ? '#e8f5e9' : '#fbe9e7' }}">
                    <small class="text-muted">Net Profit / Loss</small>
                    <h4 class="mb-0" style="color:{{ ($data['net_profit'] ?? 0) >= 0 ? '#2e7d32' : '#c62828' }}">
                        {{ number_format($data['net_profit'] ?? 0) }}
                    </h4>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover table-sm">
                <thead class="table-light">
                    <tr><th>Date</th><th class="text-end">Sales</th></tr>
                </thead>
                <tbody>
                    @forelse($data['monthly_data'] ?? [] as $row)
                    <tr>
                        <td>{{ $row->date ?? 'N/A' }}</td>
                        <td class="text-end">{{ number_format((float) ($row->total_sum ?? $row->total ?? 0), 0) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="2" class="text-center py-3 text-muted">No data for this month</td></tr>
                    @endforelse
                </tbody>
                @if(count($data['monthly_data'] ?? []) > 0)
                <tfoot>
                    <tr class="fw-bold">
                        <td>TOTAL</td>
                        <td class="text-end">{{ number_format(collect($data['monthly_data'])->sum(fn($r) => $r->total_sum ?? $r->total ?? 0), 0) }}</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>
