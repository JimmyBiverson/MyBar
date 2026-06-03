@extends('layouts.app')
@section('title', 'Waiter Dashboard')
@section('page-title', 'Waiter Dashboard')

@section('content')
<div x-data="waiterApp()" x-init="init()">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0"><i class="fas fa-chair me-2"></i>My Assigned Tables</h5>
        <a href="{{ route('waiter.orders.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i> New Order
        </a>
    </div>

    <div class="row g-3">
        <template x-for="table in tables" :key="table.id">
            <div class="col-lg-3 col-md-4 col-sm-6">
                <div class="card text-center h-100" :class="'border-' + table.borderColor">
                    <div class="card-body">
                        <div class="table-icon mb-2">
                            <i class="fas fa-chair fa-3x" :class="'text-' + table.color"></i>
                        </div>
                        <h5 class="mb-1" x-text="table.name"></h5>
                        <span class="badge mb-2" :class="'bg-' + table.color" x-text="table.status"></span>
                        <p class="small text-muted mb-2" x-text="'Capacity: ' + table.capacity"></p>

                        <template x-if="table.progress">
                            <div class="mb-2 px-2">
                                <div class="d-flex justify-content-between small text-muted">
                                    <span>Placed</span>
                                    <span>Paid</span>
                                </div>
                                <div class="progress" style="height:6px;">
                                    <div class="progress-bar" :class="table.progressBarClass"
                                        role="progressbar"
                                        :style="'width: ' + ((table.progress - 1) / 4 * 100) + '%'">
                                    </div>
                                </div>
                                <small class="text-muted" x-text="table.progress_label"></small>
                            </div>
                        </template>

                        <div class="d-grid gap-1">
                            <template x-if="table.status === 'available'">
                                <a class="btn btn-sm btn-success" :href="'{{ route('waiter.orders.create') }}?table_id=' + table.id">
                                    <i class="fas fa-plus me-1"></i> New Order
                                </a>
                            </template>
                            <template x-if="table.status === 'occupied'">
                                <a class="btn btn-sm btn-warning" :href="'{{ route('waiter.orders') }}?table_id=' + table.id">
                                    <i class="fas fa-eye me-1"></i> View Orders
                                </a>
                            </template>
                            <template x-if="table.status === 'reserved'">
                                <button class="btn btn-sm btn-info text-white" disabled>
                                    <i class="fas fa-clock me-1"></i> Reserved
                                </button>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function waiterApp() {
        return {
            tables: [],
            refreshTimer: null,
            async init() {
                await this.fetchTables();
                this.refreshTimer = setInterval(() => this.fetchTables(), 15000);
            },
            async fetchTables() {
                try {
                    const resp = await fetch('{{ route('waiter.tables.data') }}');
                    const data = await resp.json();
                    this.tables = (data.tables || []).map(t => ({
                        ...t,
                        color: t.status === 'available' ? 'success' : (t.status === 'occupied' ? 'danger' : (t.status === 'reserved' ? 'info' : 'secondary')),
                        borderColor: t.status === 'available' ? 'border-success' : (t.status === 'occupied' ? 'border-danger' : (t.status === 'reserved' ? 'border-info' : 'border-secondary'))
                    }));
                } catch(e) { this.tables = []; }

                try {
                    const orderResp = await fetch('{{ route('waiter.orders.data') }}');
                    const orderData = await orderResp.json();
                    const orders = orderData.orders || [];
                    this.tables = this.tables.map(t => {
                        const order = orders.find(o => o.table_id == t.id);
                        const progressClasses = {1: 'bg-secondary', 2: 'bg-info', 3: 'bg-warning', 4: 'bg-success', 5: 'bg-dark'};
                        return {
                            ...t,
                            progress: order ? order.progress : null,
                            progress_label: order ? order.progress_label : null,
                            progressBarClass: order ? (progressClasses[order.progress] || 'bg-secondary') : null,
                        };
                    });
                } catch(e) {}
            }
        }
    }
</script>
@endpush
