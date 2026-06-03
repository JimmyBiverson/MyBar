@extends('layouts.app')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<div x-data="dashboardStats()" x-init="initChart()">
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-start border-primary border-4">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted mb-1 small">Today's Sales</p>
                        <h4 class="mb-0 fw-bold" x-text="formatCurrency(todaySales)"></h4>
                        <small class="text-success"><i class="fas fa-arrow-up me-1"></i> <span x-text="todaySalesPercent"></span>% vs yesterday</small>
                    </div>
                    <div class="icon-box bg-primary-subtle rounded-circle p-3">
                        <i class="fas fa-money-bill-trend-up fa-2x text-primary"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-start border-success border-4">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted mb-1 small">Monthly Sales</p>
                        <h4 class="mb-0 fw-bold" x-text="formatCurrency(monthlySales)"></h4>
                        <small class="text-success"><i class="fas fa-arrow-up me-1"></i> <span x-text="monthlySalesPercent"></span>% vs last month</small>
                    </div>
                    <div class="icon-box bg-success-subtle rounded-circle p-3">
                        <i class="fas fa-calendar-check fa-2x text-success"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-start border-warning border-4">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted mb-1 small">Expenses (This Month)</p>
                        <h4 class="mb-0 fw-bold" x-text="formatCurrency(monthlyExpenses)"></h4>
                        <small class="text-warning"><i class="fas fa-minus me-1"></i> <span x-text="expensePercent"></span>% of revenue</small>
                    </div>
                    <div class="icon-box bg-warning-subtle rounded-circle p-3">
                        <i class="fas fa-receipt fa-2x text-warning"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-start border-danger border-4">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted mb-1 small">Low Stock Items</p>
                        <h4 class="mb-0 fw-bold" x-text="lowStockCount"></h4>
                        <small class="text-danger"><i class="fas fa-exclamation-triangle me-1"></i> Needs attention</small>
                    </div>
                    <div class="icon-box bg-danger-subtle rounded-circle p-3">
                        <i class="fas fa-boxes fa-2x text-danger"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-chart-line me-2"></i>Sales Trend (<span x-text="salesPeriod"></span> Days)</span>
                    <select class="form-select form-select-sm w-auto" x-model="salesPeriod" @change="updateChart">
                        <option value="7">7 Days</option>
                        <option value="30" selected>30 Days</option>
                        <option value="90">90 Days</option>
                    </select>
                </div>
                <div class="card-body">
                    <canvas id="salesChart" height="280"></canvas>
                </div>
            </div>
        </div>
        <div class="col-xl-3">
            <div class="card">
                <div class="card-header"><i class="fas fa-chart-pie me-2"></i>Payment Methods (Today)</div>
                <div class="card-body">
                    <canvas id="paymentChart" height="240"></canvas>
                    <div class="mt-2 text-center text-muted small">
                        @forelse($paymentMethods ?? [] as $pm)
                            <span class="badge bg-secondary me-1">{{ ucwords(str_replace('_', ' ', $pm->payment_method)) }}: {{ number_format($pm->total, 0) }}</span>
                        @empty
                            No payments today
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3">
            <div class="card">
                <div class="card-header"><i class="fas fa-boxes me-2"></i>Stock Status</div>
                <div class="card-body">
                    <canvas id="stockChart" height="240"></canvas>
                    <div class="mt-2 text-center text-muted small">
                        <span class="badge bg-danger me-1">Low: {{ $stockStatusCounts['low'] ?? 0 }}</span>
                        <span class="badge bg-warning text-dark me-1">Medium: {{ $stockStatusCounts['medium'] ?? 0 }}</span>
                        <span class="badge bg-success me-1">Good: {{ $stockStatusCounts['good'] ?? 0 }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-xl-8">
            <div class="card">
                <div class="card-header"><i class="fas fa-crown me-2"></i>Top 5 Selling Products</div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Product</th>
                                    <th class="text-end">Qty Sold</th>
                                    <th class="text-end">Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topProducts ?? [] as $key => $product)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>{{ $product->product->name ?? $product['name'] ?? 'N/A' }}</td>
                                    <td class="text-end">{{ $product->total_qty ?? $product['total_qty'] ?? 0 }}</td>
                                    <td class="text-end">{{ number_format($product->total_revenue ?? 0, 0) }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="4" class="text-center text-muted py-3">No sales data yet</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card">
                <div class="card-header"><i class="fas fa-users me-2"></i>Top Customers (This Month)</div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Customer</th>
                                    <th class="text-end">Visits</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topCustomers ?? [] as $key => $tc)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>{{ $tc->customer->name ?? 'Walk-in' }}</td>
                                    <td class="text-end">{{ $tc->visit_count }}</td>
                                    <td class="text-end">{{ number_format($tc->total_spent, 0) }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="4" class="text-center text-muted py-3">No customer data this month</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-xl-8">
            <div class="card mb-3">
                <div class="card-header"><i class="fas fa-list me-2"></i>Recent Orders</div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Order #</th>
                                    <th>Table</th>
                                    <th>Waiter</th>
                                    <th>Items</th>
                                    <th class="text-end">Total</th>
                                    <th class="text-center">Status</th>
                                    <th>Bill</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentOrders ?? [] as $order)
                                <tr>
                                    <td><a href="{{ route('orders.show', $order->id) }}" class="fw-medium">#{{ $order->order_number }}</a></td>
                                    <td>{{ $order->table->name ?? 'Takeaway' }}</td>
                                    <td>{{ $order->waiter->name ?? 'N/A' }}</td>
                                    <td>{{ $order->items_count }}</td>
                                    <td class="text-end">{{ number_format($order->items->sum('subtotal'), 0) }}</td>
                                    <td class="text-center">
                                        @php
                                            $badgeMap = ['pending' => 'secondary', 'confirmed' => 'info', 'preparing' => 'warning', 'ready' => 'primary', 'served' => 'success', 'completed' => 'dark', 'cancelled' => 'danger'];
                                        @endphp
                                        <span class="badge bg-{{ $badgeMap[$order->status] ?? 'secondary' }}">
                                            {{ ucfirst($order->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($order->bill)
                                            <span class="badge bg-{{ $order->bill->payment_status === 'paid' ? 'success' : 'warning' }}">
                                                {{ $order->bill->payment_status === 'paid' ? 'Paid' : 'Unpaid' }}
                                            </span>
                                        @else
                                            <span class="text-muted small">—</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="7" class="text-center text-muted py-3">No active orders</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><i class="fas fa-clock-rotate-left me-2"></i>Paid Transactions</div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Bill #</th>
                                    <th>Customer</th>
                                    <th>Items</th>
                                    <th class="text-end">Total</th>
                                    <th class="text-end">Date</th>
                                    <th class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentTransactions ?? [] as $bill)
                                <tr>
                                    <td><a href="{{ route('billing.show', $bill->id) }}" class="fw-medium">#{{ $bill->bill_number }}</a></td>
                                    <td>{{ $bill->customer->name ?? 'Walk-in' }}</td>
                                    <td>{{ $bill->items_count ?? $bill->items->count() ?? 0 }}</td>
                                    <td class="text-end">{{ number_format($bill->total_amount ?? $bill->total ?? 0) }}</td>
                                    <td class="text-end"><small>{{ $bill->created_at->format('d M Y H:i') }}</small></td>
                                    <td class="text-center">
                                        <span class="badge bg-success">Paid</span>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="6" class="text-center text-muted py-3">No paid transactions yet</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card">
                <div class="card-header"><i class="fas fa-chart-pie me-2"></i>Sales by Category (This Month)</div>
                <div class="card-body">
                    <canvas id="categoryChart" height="240"></canvas>
                    <div class="mt-2 text-center text-muted small">
                        @forelse($categorySales ?? [] as $cs)
                            <span class="badge bg-secondary me-1">{{ $cs['category'] }}: {{ number_format($cs['total'], 0) }}</span>
                        @empty
                            No sales data this month
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    Chart.register(ChartDataLabels);

    function dashboardStats() {
        return {
            todaySales: {{ $todaySales ?? 0 }},
            todaySalesPercent: {{ $todaySalesPercent ?? 0 }},
            monthlySales: {{ $monthlySales ?? 0 }},
            monthlySalesPercent: {{ $monthlySalesPercent ?? 0 }},
            monthlyExpenses: {{ $monthlyExpenses ?? 0 }},
            expensePercent: {{ $expensePercent ?? 0 }},
            lowStockCount: {{ $lowStockCount ?? 0 }},
            salesPeriod: '30',
            chart: null,
            paymentChart: null,
            stockChart: null,
            categoryChart: null,
            formatCurrency(val) {
                const s = window.currencySettings || { symbol: 'UGX', position: 'before', thousand_separator: ',', decimal_separator: '.', decimal_digits: 0 };
                const formatted = Number(val).toFixed(s.decimal_digits || 0).replace(/\B(?=(\d{3})+(?!\d))/g, s.thousand_separator || ',');
                return s.position === 'before' ? s.symbol + ' ' + formatted : formatted + ' ' + s.symbol;
            },
            initChart() {
                window.cacheAppData(null, null, { todaySales: this.todaySales, monthlySales: this.monthlySales });
                this.renderChart(
                    @json($chartLabels ?? []),
                    @json($chartValues ?? [])
                );
                this.renderPieCharts();
            },
            centerDoughnutText(val) {
                return {
                    id: 'centerText',
                    afterDraw(chart) {
                        const { ctx, width, height } = chart;
                        const s = window.currencySettings || { symbol: 'UGX' };
                        const text = s.symbol + ' ' + Number(val).toLocaleString();
                        ctx.save();
                        ctx.textAlign = 'center';
                        ctx.textBaseline = 'middle';
                        ctx.font = 'bold 16px Arial';
                        ctx.fillStyle = '#666';
                        ctx.fillText(text, width / 2, height / 2);
                        ctx.restore();
                    }
                };
            },
            renderPieCharts() {
                const pmData = @json($paymentMethods ?? []);
                const pmEl = document.getElementById('paymentChart');
                if (pmEl) {
                    const pmLabels = pmData.length ? pmData.map(pm => pm.payment_method ? pm.payment_method.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase()) : 'N/A') : ['No Data'];
                    const pmValues = pmData.length ? pmData.map(pm => Number(pm.total)) : [1];
                    const pmColors = pmData.length ? ['#7367f0', '#28c76f', '#ff9f43', '#ea5455', '#00cfe8'] : ['#e0e0e0'];
                    const pmTotal = pmValues.reduce((a, b) => a + b, 0);
                    this.paymentChart = new Chart(pmEl, {
                        type: 'doughnut',
                        data: {
                            labels: pmLabels,
                            datasets: [{ data: pmValues, backgroundColor: pmColors }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            cutout: '65%',
                            plugins: {
                                legend: { position: 'bottom', labels: { boxWidth: 10, padding: 10, font: { size: 10 } } },
                                datalabels: {
                                    color: '#fff',
                                    font: { weight: 'bold', size: 11 },
                                    formatter: (val) => val > (pmTotal * 0.05) ? this.formatCurrency(val) : '',
                                    anchor: 'center',
                                    align: 'center',
                                }
                            }
                        },
                        plugins: [this.centerDoughnutText(pmTotal)]
                    });
                }

                const ssData = @json($stockStatusCounts ?? ['low' => 0, 'medium' => 0, 'good' => 0]);
                const ssEl = document.getElementById('stockChart');
                if (ssEl) {
                    const ssValues = [ssData.low, ssData.medium, ssData.good];
                    const hasSsData = ssValues.some(v => v > 0);
                    this.stockChart = new Chart(ssEl, {
                        type: 'doughnut',
                        data: {
                            labels: hasSsData ? ['Low', 'Medium', 'Good'] : ['No Data'],
                            datasets: [{
                                data: hasSsData ? ssValues : [1],
                                backgroundColor: hasSsData ? ['#ea5455', '#ff9f43', '#28c76f'] : ['#e0e0e0'],
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            cutout: '65%',
                            plugins: {
                                legend: { position: 'bottom', labels: { boxWidth: 10, padding: 10, font: { size: 10 } } },
                                datalabels: hasSsData ? {
                                    color: '#fff',
                                    font: { weight: 'bold', size: 12 },
                                    formatter: (val) => val,
                                    anchor: 'center',
                                    align: 'center',
                                } : { display: false }
                            }
                        },
                        plugins: [this.centerDoughnutText(hasSsData ? ssValues.reduce((a, b) => a + b, 0) : 0)]
                    });
                }

                const catData = @json($categorySales ?? []);
                const catEl = document.getElementById('categoryChart');
                if (catEl) {
                    const catColors = ['#7367f0', '#28c76f', '#ff9f43', '#ea5455', '#00cfe8', '#a8aaaf', '#ffd166'];
                    const hasCatData = catData.length > 0;
                    const catLabels = hasCatData ? catData.map(c => c.category) : ['No Data'];
                    const catValues = hasCatData ? catData.map(c => c.total) : [1];
                    const catBg = hasCatData ? catColors.slice(0, catData.length) : ['#e0e0e0'];
                    const catTotal = catValues.reduce((a, b) => a + b, 0);
                    this.categoryChart = new Chart(catEl, {
                        type: 'doughnut',
                        data: {
                            labels: catLabels,
                            datasets: [{ data: catValues, backgroundColor: catBg }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            cutout: '65%',
                            plugins: {
                                legend: { position: 'bottom', labels: { boxWidth: 10, padding: 10, font: { size: 10 } } },
                                datalabels: hasCatData ? {
                                    color: '#fff',
                                    font: { weight: 'bold', size: 11 },
                                    formatter: (val) => val > (catTotal * 0.05) ? this.formatCurrency(val) : '',
                                    anchor: 'center',
                                    align: 'center',
                                } : { display: false }
                            }
                        },
                        plugins: [this.centerDoughnutText(catTotal)]
                    });
                }
            },
            renderChart(labels, data) {
                const ctx = document.getElementById('salesChart');
                if (!ctx) return;

                if (this.chart) {
                    this.chart.destroy();
                }

                const allZero = data.every(v => Number(v) === 0);

                if (allZero) {
                    this.chart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: labels,
                            datasets: [{
                                label: 'Sales',
                                data: data,
                                borderColor: '#7367f0',
                                backgroundColor: 'rgba(115,103,240,0.1)',
                                fill: true,
                                tension: 0.4,
                                pointRadius: 0,
                                pointBackgroundColor: '#7367f0',
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false },
                                datalabels: { display: false },
                                emptyState: {
                                    text: 'No sales data for this period'
                                }
                            },
                            scales: {
                                y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.04)' } },
                                x: { grid: { display: false }, ticks: { maxTicksLimit: 10 } }
                            }
                        },
                        plugins: [{
                            id: 'emptyState',
                            afterDraw(chart) {
                                const { ctx, width, height, chartArea } = chart;
                                if (!chartArea) return;
                                const cfg = chart.options.plugins.emptyState || {};
                                const text = cfg.text || 'No data';
                                ctx.save();
                                ctx.textAlign = 'center';
                                ctx.textBaseline = 'middle';
                                ctx.font = '15px Arial';
                                ctx.fillStyle = '#999';
                                ctx.fillText(text, width / 2, (chartArea.top + chartArea.bottom) / 2);
                                ctx.restore();
                            }
                        }]
                    });
                    return;
                }

                this.chart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Sales',
                            data: data,
                            borderColor: '#7367f0',
                            backgroundColor: 'rgba(115,103,240,0.1)',
                            fill: true,
                            tension: 0.4,
                            pointRadius: 4,
                            pointBackgroundColor: '#7367f0',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: (ctx) => {
                                        const s = window.currencySettings || { symbol: 'UGX', position: 'before' };
                                        return s.symbol + ' ' + Number(ctx.parsed.y).toLocaleString();
                                    }
                                }
                            },
                            datalabels: {
                                display: (ctx) => Number(ctx.dataset.data[ctx.dataIndex]) > 0,
                                color: '#7367f0',
                                anchor: 'end',
                                align: 'end',
                                offset: 2,
                                font: { weight: 'bold', size: 10 },
                                formatter: (val) => {
                                    const s = window.currencySettings || { symbol: 'UGX' };
                                    return s.symbol + ' ' + Number(val).toLocaleString();
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: { color: 'rgba(0,0,0,0.04)' },
                                ticks: {
                                    callback: (val) => {
                                        const s = window.currencySettings || { symbol: 'UGX' };
                                        return s.symbol + ' ' + Number(val).toLocaleString();
                                    }
                                }
                            },
                            x: {
                                grid: { display: false },
                                ticks: { maxTicksLimit: 10 }
                            }
                        }
                    }
                });
            },
            async updateChart() {
                try {
                    const resp = await fetch('{{ route('dashboard.chart-data') }}?days=' + this.salesPeriod);
                    const data = await resp.json();
                    if (data.labels && data.values) {
                        this.renderChart(data.labels, data.values);
                    }
                } catch(e) {
                    console.error('Failed to load chart data', e);
                }
            }
        }
    }
</script>
@endpush
