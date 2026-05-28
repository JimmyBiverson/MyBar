@extends('layouts.app')
@section('title', 'Kitchen Display')
@section('page-title', 'Kitchen Display')

@section('content')
<div class="kitchen-display" x-data="kitchenApp()" x-init="init()">
    <div class="d-flex justify-content-end mb-2">
        <button class="btn btn-sm btn-outline-secondary" @click="playNotificationSound()" title="Test sound">
            <i class="fas fa-volume-up me-1"></i> Ding
        </button>
    </div>
    <div class="row g-3">
        <div class="col-md-4">
            <div class="card border-danger border-2">
                <div class="card-header bg-danger text-white fw-bold">
                    <i class="fas fa-clock me-2"></i>Pending
                    <span class="badge bg-light text-danger ms-2" x-text="pendingCount"></span>
                </div>
                <div class="card-body kitchen-column" style="max-height:calc(100vh - 250px);overflow-y:auto;">
                    <template x-for="order in pendingOrders" :key="order.id">
                        <div class="order-card card mb-2 border-danger">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h6 class="mb-0" x-text="'Order #' + order.id"></h6>
                                        <small class="text-muted" x-text="'Table: ' + (order.table_name || 'Takeaway')"></small>
                                    </div>
                                    <span class="badge bg-danger" x-text="order.elapsed"></span>
                                </div>
                                <div class="order-items small mb-2">
                                    <template x-for="item in order.items" :key="item.id">
                                        <div class="d-flex justify-content-between">
                                            <span x-text="item.qty + 'x ' + item.product_name"></span>
                                            <template x-if="item.notes">
                                                <small class="text-muted fst-italic" x-text="'(' + item.notes + ')'"></small>
                                            </template>
                                        </div>
                                    </template>
                                </div>
                                <button class="btn btn-sm btn-warning w-100" @click="updateStatus(order.id, 'preparing')">
                                    <i class="fas fa-fire me-1"></i> Start Preparing
                                </button>
                            </div>
                        </div>
                    </template>
                    <template x-if="pendingOrders.length === 0">
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                            <p>No pending orders</p>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-warning border-2">
                <div class="card-header bg-warning text-dark fw-bold">
                    <i class="fas fa-fire me-2"></i>Preparing
                    <span class="badge bg-dark text-warning ms-2" x-text="preparingCount"></span>
                </div>
                <div class="card-body kitchen-column" style="max-height:calc(100vh - 250px);overflow-y:auto;">
                    <template x-for="order in preparingOrders" :key="order.id">
                        <div class="order-card card mb-2 border-warning">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h6 class="mb-0" x-text="'Order #' + order.id"></h6>
                                        <small class="text-muted" x-text="'Table: ' + (order.table_name || 'Takeaway')"></small>
                                    </div>
                                    <span class="badge bg-warning text-dark" x-text="order.elapsed"></span>
                                </div>
                                <div class="order-items small mb-2">
                                    <template x-for="item in order.items" :key="item.id">
                                        <div class="d-flex justify-content-between">
                                            <span x-text="item.qty + 'x ' + item.product_name"></span>
                                            <template x-if="item.notes">
                                                <small class="text-muted fst-italic" x-text="'(' + item.notes + ')'"></small>
                                            </template>
                                        </div>
                                    </template>
                                </div>
                                <button class="btn btn-sm btn-success w-100" @click="confirmReady(order.id)">
                                    <i class="fas fa-check me-1"></i> Mark Ready
                                </button>
                            </div>
                        </div>
                    </template>
                    <template x-if="preparingOrders.length === 0">
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-utensils fa-2x text-muted mb-2"></i>
                            <p>No orders being prepared</p>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-success border-2">
                <div class="card-header bg-success text-white fw-bold">
                    <i class="fas fa-check-circle me-2"></i>Ready
                    <span class="badge bg-light text-success ms-2" x-text="readyCount"></span>
                </div>
                <div class="card-body kitchen-column" style="max-height:calc(100vh - 250px);overflow-y:auto;">
                    <template x-for="order in readyOrders" :key="order.id">
                        <div class="order-card card mb-2 border-success">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h6 class="mb-0" x-text="'Order #' + order.id"></h6>
                                        <small class="text-muted" x-text="'Table: ' + (order.table_name || 'Takeaway')"></small>
                                    </div>
                                    <span class="badge bg-success" x-text="order.elapsed"></span>
                                </div>
                                <div class="order-items small mb-2">
                                    <template x-for="item in order.items" :key="item.id">
                                        <div class="d-flex justify-content-between">
                                            <span x-text="item.qty + 'x ' + item.product_name"></span>
                                        </div>
                                    </template>
                                </div>
                                <button class="btn btn-sm btn-outline-success w-100" @click="updateStatus(order.id, 'served')">
                                    <i class="fas fa-hand-peace me-1"></i> Mark Served
                                </button>
                            </div>
                        </div>
                    </template>
                    <template x-if="readyOrders.length === 0">
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-check-double fa-2x text-muted mb-2"></i>
                            <p>No ready orders</p>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function kitchenApp() {
        return {
            orders: [],
            refreshTimer: null,
            soundTimer: null,
            previousOrderCount: 0,
            init() {
                this.fetchOrders();
                this.refreshTimer = setInterval(() => this.fetchOrders(), 30000);
                this.soundTimer = setInterval(() => this.checkNewOrders(), 15000);
                this.previousOrderCount = this.orders.length;
                this.playNotificationSound();
            },
            playNotificationSound() {
                try {
                    const ctx = new (window.AudioContext || window.webkitAudioContext)();
                    const oscillator = ctx.createOscillator();
                    const gain = ctx.createGain();
                    oscillator.connect(gain);
                    gain.connect(ctx.destination);
                    oscillator.type = 'sine';
                    oscillator.frequency.setValueAtTime(523, ctx.currentTime);
                    oscillator.frequency.setValueAtTime(659, ctx.currentTime + 0.1);
                    oscillator.frequency.setValueAtTime(784, ctx.currentTime + 0.2);
                    gain.gain.setValueAtTime(0.3, ctx.currentTime);
                    gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.5);
                    oscillator.start(ctx.currentTime);
                    oscillator.stop(ctx.currentTime + 0.5);
                } catch(e) {}
            },
            checkNewOrders() {
                const currentCount = this.orders.length;
                if (currentCount > this.previousOrderCount) {
                    this.playNotificationSound();
                }
                this.previousOrderCount = currentCount;
            },
            get pendingOrders() { return this.orders.filter(o => o.status === 'pending'); },
            get preparingOrders() { return this.orders.filter(o => o.status === 'preparing'); },
            get readyOrders() { return this.orders.filter(o => o.status === 'ready'); },
            get pendingCount() { return this.pendingOrders.length; },
            get preparingCount() { return this.preparingOrders.length; },
            get readyCount() { return this.readyOrders.length; },
            async fetchOrders() {
                try {
                    const resp = await fetch('{{ route('kitchen.orders') }}');
                    const data = await resp.json();
                    if (data.orders) this.orders = data.orders.map(o => ({
                        ...o,
                        elapsed: this.calculateElapsed(o.created_at)
                    }));
                } catch(e) {}
            },
            calculateElapsed(dateStr) {
                const diff = Math.floor((new Date() - new Date(dateStr)) / 1000);
                const mins = Math.floor(diff / 60);
                const secs = diff % 60;
                return mins + 'm ' + secs + 's';
            },
            confirmReady(orderId) {
                Swal.fire({
                    title: 'Mark as Ready?',
                    text: 'Confirm this order is ready to serve?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    confirmButtonText: 'Yes, mark ready!'
                }).then((r) => {
                    if (r.isConfirmed) this.updateStatus(orderId, 'ready');
                });
            },
            async updateStatus(orderId, status) {
                try {
                    const url = '{{ route('kitchen.update-status', ['order' => 'ORDER_ID']) }}'.replace('ORDER_ID', orderId);
                    const resp = await fetch(url, {
                        method: 'PUT',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                        body: JSON.stringify({ status })
                    });
                    const data = await resp.json();
                    if (data.success) this.fetchOrders();
                } catch(e) {}
            }
        }
    }
</script>
@endpush

@push('styles')
<style>
    .kitchen-column .order-card { transition: all 0.2s; }
    .kitchen-column .order-card:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
    .new-order-flash {
        animation: flashNew 1s ease-in-out 3;
    }
    @keyframes flashNew {
        0%, 100% { background-color: transparent; }
        50% { background-color: rgba(255, 193, 7, 0.3); }
    }
    @media (max-width: 768px) {
        .kitchen-display .row > div { margin-bottom: 1rem; }
    }
</style>
@endpush
