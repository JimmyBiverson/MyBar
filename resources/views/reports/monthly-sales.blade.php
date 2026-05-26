<div class="card">
    <div class="card-header"><i class="fas fa-calendar-alt me-2"></i>Monthly Sales Report</div>
    <div class="card-body">
        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <div class="border rounded p-3 text-center">
                    <small class="text-muted">Total Sales</small>
                    <h4 class="mb-0 text-primary">{{ number_format($data['total_sales'] ?? 0) }}</h4>
                </div>
            </div>
            <div class="col-md-4">
                <div class="border rounded p-3 text-center">
                    <small class="text-muted">Total Transactions</small>
                    <h4 class="mb-0">{{ $data['total_transactions'] ?? 0 }}</h4>
                </div>
            </div>
            <div class="col-md-4">
                <div class="border rounded p-3 text-center">
                    <small class="text-muted">Average Daily Sales</small>
                    <h4 class="mb-0 text-success">{{ number_format($data['average_daily'] ?? 0) }}</h4>
                </div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr><th>Month</th><th>Sales</th><th>Transactions</th><th class="text-end">Avg/Day</th></tr>
                </thead>
                <tbody>
                    @forelse($data['monthly_data'] ?? [] as $row)
                    <tr>
                        <td>{{ $row['month'] ?? $row->month ?? 'N/A' }}</td>
                        <td>{{ number_format($row['total'] ?? $row->total ?? 0) }}</td>
                        <td>{{ $row['count'] ?? $row->count ?? 0 }}</td>
                        <td class="text-end">{{ number_format($row['average'] ?? $row->average ?? 0) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="text-center py-3 text-muted">No data</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
