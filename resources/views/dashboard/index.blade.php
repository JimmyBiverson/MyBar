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
        <div class="col-xl-8">
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
        <div class="col-xl-4">
            <div class="card">
                <div class="card-header"><i class="fas fa-chart-pie me-2"></i>Today's Payment Methods</div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Method</th>
                                    <th class="text-end">Count</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($paymentMethods ?? [] as $pm)
                                <tr>
                                    <td>{{ ucwords(str_replace('_', ' ', $pm->payment_method)) }}</td>
                                    <td class="text-end">{{ $pm->count }}</td>
                                    <td class="text-end">{{ number_format($pm->total, 0) }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="3" class="text-center text-muted py-3">No payments today</td></tr>
                                @endforelse
                            </tbody>
                        </table>
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
                <div class="card-header"><i class="fas fa-exclamation-circle me-2 text-danger"></i>Low Stock Alerts</div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Product</th>
                                    <th class="text-end">Stock</th>
                                    <th class="text-end">Reorder</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($lowStockProducts ?? [] as $product)
                                <tr>
                                    <td>{{ $product->name }}</td>
                                    <td class="text-end"><span class="badge bg-danger">{{ $product->current_stock }}</span></td>
                                    <td class="text-end">{{ $product->reorder_level }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="3" class="text-center text-muted py-3">All stock levels are healthy</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer text-center p-2">
                    <a href="{{ route('products.index', ['low_stock' => 1]) }}" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
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
            },
            renderChart(labels, data) {
                const ctx = document.getElementById('salesChart');
                if (!ctx) return;

                if (this.chart) {
                    this.chart.destroy();
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
                            pointRadius: 3,
                            pointBackgroundColor: '#7367f0',
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
