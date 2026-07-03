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
                <div class="card-body position-relative">
                    <div x-show="chartLoading" class="position-absolute top-50 start-50 translate-middle" style="z-index:5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                    <div style="height: 280px; position: relative;">
                        <canvas id="salesChart" :class="{ 'opacity-25': chartLoading }"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3">
            <div class="card">
                <div class="card-header"><i class="fas fa-chart-pie me-2"></i>Payment Methods (Today)</div>
                <div class="card-body">
                    <div style="height: 220px; position: relative;">
                        <canvas id="paymentChart"></canvas>
                    </div>
                    <div class="mt-2 text-center text-muted small">
                        @forelse($paymentMethods ?? [] as $pm)
                            <span class="badge bg-secondary me-1">{{ ucwords(str_replace('_', ' ', $pm->payment_method)) }}: {{ formatCurrency($pm->total) }}</span>
                        @empty
                            No payments today
                        @endforelse
                    </div>
                    <hr class="my-2">
                    <div class="text-center small">
                        <div class="fw-semibold mb-1">Processed By</div>
                        <span class="badge bg-info me-1"><i class="fas fa-user-tie me-1"></i>Waiter: {{ $processorStats->waiter_count ?? 0 }} ({{ formatCurrency($processorStats->waiter_total ?? 0) }})</span>
                        <span class="badge bg-secondary me-1"><i class="fas fa-cash-register me-1"></i>Cashier: {{ $processorStats->cashier_count ?? 0 }} ({{ formatCurrency($processorStats->cashier_total ?? 0) }})</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3">
            <div class="card">
                <div class="card-header"><i class="fas fa-boxes me-2"></i>Stock Status</div>
                <div class="card-body">
                    <div style="height: 220px; position: relative;">
                        <canvas id="stockChart"></canvas>
                    </div>
                    <div class="mt-2 text-center text-muted small">
                        <span class="badge bg-danger me-1">Low: {{ $stockStatusCounts['low'] ?? 0 }}</span>
                        <span class="badge bg-warning text-dark me-1">Medium: {{ $stockStatusCounts['medium'] ?? 0 }}</span>
                        <span class="badge bg-success me-1">Good: {{ $stockStatusCounts['good'] ?? 0 }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(($lowStockProducts ?? [])->isNotEmpty() || ($mediumStockProducts ?? [])->isNotEmpty())
    <div class="row g-3 mb-4">
        <div class="col-xl-6">
            <div class="card border-start border-danger border-4 h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-exclamation-triangle text-danger me-2"></i>Low Stock Products</span>
                    <span class="badge bg-danger">{{ $stockStatusCounts['low'] ?? 0 }} items</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr><th>Product</th><th>Category</th><th class="text-end">Stock</th><th class="text-center">Status</th></tr>
                            </thead>
                            <tbody>
                                @forelse($lowStockProducts ?? [] as $p)
                                <tr>
                                    <td class="fw-medium">{{ $p->name }}</td>
                                    <td>{{ $p->category->name ?? 'N/A' }}</td>
                                    <td class="text-end text-danger fw-bold">{{ number_format($p->current_stock, 0) }}</td>
                                    <td class="text-center"><span class="badge bg-danger">Low</span></td>
                                </tr>
                                @empty
                                <tr><td colspan="4" class="text-center text-muted py-3">No low stock items</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card border-start border-warning border-4 h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-exclamation-circle text-warning me-2"></i>Medium Stock Products</span>
                    <span class="badge bg-warning text-dark">{{ $stockStatusCounts['medium'] ?? 0 }} items</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr><th>Product</th><th>Category</th><th class="text-end">Stock</th><th class="text-center">Status</th></tr>
                            </thead>
                            <tbody>
                                @forelse($mediumStockProducts ?? [] as $p)
                                <tr>
                                    <td class="fw-medium">{{ $p->name }}</td>
                                    <td>{{ $p->category->name ?? 'N/A' }}</td>
                                    <td class="text-end text-warning fw-bold">{{ number_format($p->current_stock, 0) }}</td>
                                    <td class="text-center"><span class="badge bg-warning text-dark">Medium</span></td>
                                </tr>
                                @empty
                                <tr><td colspan="4" class="text-center text-muted py-3">No medium stock items</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

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
                                    <td class="text-end">{{ formatCurrency($product->total_revenue ?? 0) }}</td>
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
                                    <td class="text-end">{{ formatCurrency($tc->total_spent) }}</td>
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
                                    <td class="text-end">{{ formatCurrency($order->items->sum('subtotal')) }}</td>
                                    <td class="text-center">
                                        @php
                                            $badgeMap = ['pending' => 'secondary', 'confirmed' => 'info', 'preparing' => 'warning', 'ready' => 'primary', 'served' => 'success', 'completed' => 'dark', 'cancelled' => 'danger'];
                                        @endphp
                                        <span class="badge bg-{{ $badgeMap[$order->status] ?? 'secondary' }}">
                                            {{ ucfirst($order->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($order->status === 'cancelled')
                                            <span class="text-muted small">—</span>
                                        @elseif($order->bill && $order->bill->payment_status === 'paid')
                                            <span class="badge bg-success">Paid</span>
                                        @else
                                            <a href="{{ route('pos.index', ['order_id' => $order->id]) }}" class="btn btn-sm btn-primary py-0 px-2" style="font-size: 0.75rem;" title="Process Payment via POS">
                                                <i class="fas fa-credit-card me-1"></i> Pay
                                            </a>
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
                                    <th>Processed By</th>
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
                                    <td>
                                        <i class="fas fa-{{ $bill->processed_by_role === 'waiter' ? 'user-tie' : 'cash-register' }} text-muted me-1"></i>
                                        {{ $bill->cashier->name ?? $bill->waiter->name ?? 'N/A' }}
                                        <span class="badge bg-{{ $bill->processed_by_role === 'waiter' ? 'info' : 'secondary' }}">{{ ucfirst($bill->processed_by_role ?? 'cashier') }}</span>
                                    </td>
                                    <td class="text-end">{{ formatCurrency($bill->total_amount ?? $bill->total ?? 0) }}</td>
                                    <td class="text-end"><small>{{ $bill->created_at->format('d M Y H:i') }}</small></td>
                                    <td class="text-center">
                                        <span class="badge bg-success">Paid</span>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="7" class="text-center text-muted py-3">No paid transactions yet</td></tr>
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
                    <div style="height: 220px; position: relative;">
                        <canvas id="categoryChart"></canvas>
                    </div>
                    <div class="mt-2 text-center text-muted small">
                        @forelse($categorySales ?? [] as $cs)
                            <span class="badge bg-secondary me-1">{{ $cs['category'] }}: {{ formatCurrency($cs['total']) }}</span>
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
    function dashboardStats() {
        return {
            chartAvailable: typeof Chart !== 'undefined',
            todaySales: {{ $todaySales ?? 0 }},
            todaySalesPercent: {{ $todaySalesPercent ?? 0 }},
            monthlySales: {{ $monthlySales ?? 0 }},
            monthlySalesPercent: {{ $monthlySalesPercent ?? 0 }},
            monthlyExpenses: {{ $monthlyExpenses ?? 0 }},
            expensePercent: {{ $expensePercent ?? 0 }},
            lowStockCount: {{ $lowStockCount ?? 0 }},
            salesPeriod: '30',
            chartLoading: false,
            chart: null,
            paymentChart: null,
            stockChart: null,
            categoryChart: null,
            abbreviate(val) {
                const n = Number(val);
                if (n >= 1000000) return (n / 1000000).toFixed(1) + 'M';
                if (n >= 1000) return (n / 1000).toFixed(1) + 'K';
                return n.toFixed(0);
            },
            formatCurrency(val) {
                const s = window.currencySettings || { symbol: 'UGX', position: 'before', thousand_separator: ',', decimal_separator: '.', decimal_digits: 0 };
                const formatted = Number(val).toFixed(s.decimal_digits || 0).replace(/\B(?=(\d{3})+(?!\d))/g, s.thousand_separator || ',');
                return s.position === 'before' ? s.symbol + ' ' + formatted : formatted + ' ' + s.symbol;
            },
            initChart() {
                if (!this.chartAvailable) { console.warn('Chart.js not loaded'); return; }
                this.$nextTick(() => {
                    this.renderSalesChart(@json($chartLabels ?? []), @json($chartValues ?? []));
                    this.renderPaymentChart();
                    this.renderStockChart();
                    this.renderCategoryChart();
                });
            },
            makeCenterTextPlugin(text) {
                // Use a unique id so Chart.js won't warn about duplicate plugin ids
                const uid = 'centerText_' + (++window._chartCenterTextCounter);
                return {
                    id: uid,
                    afterDraw(chart) {
                        const { ctx, width, height } = chart;
                        ctx.save();
                        ctx.textAlign = 'center';
                        ctx.textBaseline = 'middle';
                        ctx.font = 'bold 13px Arial';
                        ctx.fillStyle = '#555';
                        ctx.fillText(text, width / 2, height / 2);
                        ctx.restore();
                    }
                };
            },
            renderPaymentChart() {
                const el = document.getElementById('paymentChart');
                if (!el) return;
                if (this.paymentChart) { this.paymentChart.destroy(); this.paymentChart = null; }
                const raw = @json($paymentMethods ?? []);
                const has = raw.length > 0;
                const labels = has ? raw.map(p => (p.payment_method||'N/A').replace(/_/g,' ').replace(/\b\w/g,c=>c.toUpperCase())) : ['No Payments Today'];
                const values = has ? raw.map(p => Number(p.total)) : [1];
                const colors = has ? ['{{ \App\Models\Setting::get('accent_color', '#7367f0') }}','#28c76f','#ff9f43','#ea5455','#00cfe8','#fd7e14'] : ['#e0e0e0'];
                const total = has ? values.reduce((a,b)=>a+b,0) : 0;
                const self = this;
                try {
                    this.paymentChart = new Chart(el, {
                        type: 'doughnut',
                        data: { labels, datasets: [{ data: values, backgroundColor: colors, borderWidth: 2, borderColor: '#fff' }] },
                        options: {
                            responsive: true, maintainAspectRatio: false, cutout: '68%',
                            plugins: {
                                legend: { position: 'bottom', labels: { boxWidth: 10, padding: 8, font: { size: 10 } } },
                                tooltip: { callbacks: { label: ctx => has ? ' ' + self.formatCurrency(ctx.raw) : 'No data' } },
                                datalabels: has ? {
                                    color: '#fff', font: { weight: 'bold', size: 10 },
                                    formatter: v => v > total * 0.07 ? self.abbreviate(v) : '',
                                    anchor: 'center', align: 'center'
                                } : { display: false }
                            }
                        },
                        plugins: [this.makeCenterTextPlugin(has ? self.formatCurrency(total) : 'No Data')]
                    });
                } catch(e) { console.error('paymentChart', e); }
            },
            renderStockChart() {
                const el = document.getElementById('stockChart');
                if (!el) return;
                if (this.stockChart) { this.stockChart.destroy(); this.stockChart = null; }
                const ss = @json($stockStatusCounts ?? ['low' => 0, 'medium' => 0, 'good' => 0]);
                const values = [Number(ss.low||0), Number(ss.medium||0), Number(ss.good||0)];
                const total = values.reduce((a,b)=>a+b,0);
                const has = total > 0;
                try {
                    this.stockChart = new Chart(el, {
                        type: 'doughnut',
                        data: {
                            labels: has ? ['Low','Medium','Good'] : ['No Products'],
                            datasets: [{ data: has ? values : [1], backgroundColor: has ? ['#ea5455','#ff9f43','#28c76f'] : ['#e0e0e0'], borderWidth: 2, borderColor: '#fff' }]
                        },
                        options: {
                            responsive: true, maintainAspectRatio: false, cutout: '68%',
                            plugins: {
                                legend: { position: 'bottom', labels: { boxWidth: 10, padding: 8, font: { size: 10 } } },
                                datalabels: has ? {
                                    color: '#fff', font: { weight: 'bold', size: 11 },
                                    formatter: v => v > 0 ? v : '', anchor: 'center', align: 'center'
                                } : { display: false }
                            }
                        },
                        plugins: [this.makeCenterTextPlugin(has ? total + ' items' : 'No Data')]
                    });
                } catch(e) { console.error('stockChart', e); }
            },
            renderCategoryChart() {
                const el = document.getElementById('categoryChart');
                if (!el) return;
                if (this.categoryChart) { this.categoryChart.destroy(); this.categoryChart = null; }
                const raw = @json($categorySales ?? []);
                const has = raw.length > 0;
                const palette = ['{{ \App\Models\Setting::get('accent_color', '#7367f0') }}','#28c76f','#ff9f43','#ea5455','#00cfe8','#a8aaaf','#ffd166'];
                const labels = has ? raw.map(c => c.category||'Unknown') : ['No Sales This Month'];
                const values = has ? raw.map(c => Number(c.total)) : [1];
                const total = has ? values.reduce((a,b)=>a+b,0) : 0;
                const self = this;
                try {
                    this.categoryChart = new Chart(el, {
                        type: 'doughnut',
                        data: { labels, datasets: [{ data: values, backgroundColor: has ? palette.slice(0, raw.length) : ['#e0e0e0'], borderWidth: 2, borderColor: '#fff' }] },
                        options: {
                            responsive: true, maintainAspectRatio: false, cutout: '68%',
                            plugins: {
                                legend: { position: 'bottom', labels: { boxWidth: 10, padding: 8, font: { size: 10 } } },
                                tooltip: { callbacks: { label: ctx => has ? ' ' + self.formatCurrency(ctx.raw) : 'No data' } },
                                datalabels: has ? {
                                    color: '#fff', font: { weight: 'bold', size: 10 },
                                    formatter: v => v > total * 0.07 ? self.abbreviate(v) : '',
                                    anchor: 'center', align: 'center'
                                } : { display: false }
                            }
                        },
                        plugins: [this.makeCenterTextPlugin(has ? self.formatCurrency(total) : 'No Data')]
                    });
                } catch(e) { console.error('categoryChart', e); }
            },
            renderSalesChart(labels, data) {
                const ctx = document.getElementById('salesChart');
                if (!ctx) return;
                if (this.chart) { this.chart.destroy(); this.chart = null; }
                const nums = (data || []).map(Number);
                const allZero = nums.every(v => v === 0);
                const maxVal = allZero ? 0 : Math.max(...nums);
                const suggestedMax = maxVal > 0 ? maxVal * 1.2 : 50000;
                const self = this;
                try {
                    this.chart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: labels || [],
                            datasets: [{
                                label: window.currencySettings ? `Sales (${window.currencySettings.symbol})` : 'Sales (UGX)',
                                data: nums,
                                backgroundColor: '{{ \App\Models\Setting::get('accent_color', '#7367f0') }}',
                                borderRadius: 4,
                                borderSkipped: false
                            }]
                        },
                        options: {
                            responsive: true, maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false },
                                tooltip: { callbacks: { label: c => ' ' + self.formatCurrency(c.parsed.y) } },
                                datalabels: {
                                    display: c => !allZero && nums[c.dataIndex] > 0,
                                    color: '{{ \App\Models\Setting::get('accent_color', '#7367f0') }}', anchor: 'end', align: 'top', offset: 4,
                                    font: { weight: 'bold', size: 9 },
                                    formatter: v => self.abbreviate(v)
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    suggestedMax: suggestedMax,
                                    grid: { color: 'rgba(0,0,0,0.05)' },
                                    ticks: {
                                        maxTicksLimit: 6,
                                        callback: v => { const s = window.currencySettings||{symbol:'{{ \App\Models\Setting::get('currency_symbol', 'UGX') }}'}; return s.symbol+' '+self.abbreviate(v); }
                                    }
                                },
                                x: { grid: { display: false }, ticks: { maxTicksLimit: 10, maxRotation: 0 } }
                            }
                        },
                        plugins: [{
                            id: 'noDataMsg',
                            afterDraw(chart) {
                                if (!allZero) return;
                                const { ctx: c, width, height, chartArea } = chart;
                                if (!chartArea) return;
                                c.save(); c.textAlign = 'center'; c.textBaseline = 'middle';
                                c.font = '15px Arial'; c.fillStyle = '#bbb';
                                c.fillText('No sales recorded yet', width/2, (chartArea.top+chartArea.bottom)/2);
                                c.restore();
                            }
                        }]
                    });
                } catch(e) { console.error('salesChart', e); }
            },
            async updateChart() {
                this.chartLoading = true;
                try {
                    const resp = await fetch('{{ route('dashboard.chart-data') }}?days=' + this.salesPeriod);
                    const data = await resp.json();
                    if (data.labels && data.values) this.renderSalesChart(data.labels, data.values);
                } catch(e) { console.error('Chart update failed', e); }
                finally { this.chartLoading = false; }
            }
        }
    }
</script>
@endpush
