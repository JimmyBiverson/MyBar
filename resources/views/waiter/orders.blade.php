@extends('layouts.app')
@section('title', 'Waiter Orders')
@section('page-title', 'Waiter Orders')

@section('breadcrumb-plugins')
    <a href="{{ route('waiter.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i> Back</a>
@endsection

@section('content')
<div x-data="waiterOrders()" x-init="init()">
    <div class="row g-3">
        <div class="col-lg-5">
            <div class="card">
                <div class="card-header"><i class="fas fa-plus-circle me-2"></i>New Order</div>
                <div class="card-body">
                    <form @submit.prevent="submitOrder">
                        <div class="mb-3">
                            <label class="form-label">Table</label>
                            <select class="form-select" x-model="newOrder.table_id" required>
                                <option value="">Select Table</option>
                                <option value="takeaway">Takeaway</option>
                                <template x-for="t in tables" :key="t.id">
                                    <option :value="t.id" x-text="t.name"></option>
                                </template>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Customer Name</label>
                            <input type="text" class="form-control" x-model="newOrder.customer_name" placeholder="Optional">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Products</label>
                            <template x-for="(item, i) in newOrder.items" :key="i">
                                <div class="d-flex gap-2 mb-2">
                                    <select class="form-select form-select-sm" x-model="item.product_id" style="flex:2;" required>
                                        <option value="">Select Product</option>
                                        <template x-for="p in products" :key="p.id">
                                            <option :value="p.id" x-text="p.name + ' (' + formatCurrency(p.selling_price) + ')'"></option>
                                        </template>
                                    </select>
                                    <input type="number" class="form-control form-control-sm" style="width:60px;" x-model="item.qty" min="1" placeholder="Qty">
                                    <button type="button" class="btn btn-sm btn-outline-danger" @click="removeItem(i)" x-show="newOrder.items.length > 1">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </template>
                            <button type="button" class="btn btn-sm btn-outline-secondary" @click="addItem()">
                                <i class="fas fa-plus me-1"></i> Add Item
                            </button>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea class="form-control form-control-sm" x-model="newOrder.notes" rows="2" placeholder="Any special instructions..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100" :disabled="!canSubmit">
                            <i class="fas fa-paper-plane me-1"></i> Place Order
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-list me-2"></i>Active Orders</span>
                    <div class="d-flex gap-2 align-items-center">
                        <span class="badge bg-secondary" x-text="orders.length + ' active'"></span>
                        <select class="form-select form-select-sm w-auto" x-model="filterTable">
                            <option value="">All Tables</option>
                            <template x-for="t in tables" :key="t.id">
                                <option :value="t.id" x-text="t.name"></option>
                            </template>
                            <option value="takeaway">Takeaway</option>
                        </select>
                    </div>
                </div>
                <div class="card-body p-0">
                    <template x-for="order in filteredOrders" :key="order.id">
                        <div class="border-bottom p-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div>
                                    <strong x-text="'Order #' + order.id"></strong>
                                    <span class="badge ms-2" :class="progressBadgeClass(order.progress)" x-text="order.progress_label"></span>
                                    <small class="text-muted ms-2" x-text="'Table: ' + (order.table_name || 'Takeaway')"></small>
                                </div>
                                <small class="text-muted" x-text="order.created_at"></small>
                            </div>

                            <div class="mb-2">
                                <div class="d-flex justify-content-between small text-muted mb-1">
                                    <span>Placed</span>
                                    <span>Received</span>
                                    <span>Ready</span>
                                    <span>Delivered</span>
                                    <span>Paid</span>
                                </div>
                                <div class="progress" style="height:8px;">
                                    <div class="progress-bar" :class="progressBarClass(order.progress)" role="progressbar"
                                        :style="'width: ' + ((order.progress - 1) / 4 * 100) + '%'"
                                        :aria-valuenow="order.progress" aria-valuemin="1" aria-valuemax="5">
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between mt-1">
                                    <template x-for="step in 5" :key="step">
                                        <div class="text-center" style="width:20%;">
                                            <i class="fas fa-circle fa-xs" :class="step <= order.progress ? 'text-primary' : 'text-secondary'"></i>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <div class="small mt-1" x-text="order.items_text"></div>

                            <div class="mt-2 d-flex gap-1 flex-wrap">
                                <template x-if="order.status === 'ready'">
                                    <button class="btn btn-sm btn-success" @click="markServed(order.id)">
                                        <i class="fas fa-hand-peace me-1"></i> Mark Delivered
                                    </button>
                                </template>
                                <template x-if="order.status === 'pending' || order.status === 'confirmed' || order.status === 'preparing'">
                                    <button class="btn btn-sm btn-outline-danger" @click="cancelOrder(order.id)">
                                        <i class="fas fa-ban me-1"></i> Cancel
                                    </button>
                                </template>
                                <template x-if="order.status !== 'cancelled' && order.status !== 'served' && order.status !== 'completed'">
                                    <button class="btn btn-sm btn-outline-primary" @click="requestBill(order.id)">
                                        <i class="fas fa-receipt me-1"></i> Request Bill
                                    </button>
                                </template>
                                <template x-if="order.bill_requested && order.payment_status !== 'paid'">
                                    <button class="btn btn-sm btn-primary" @click="openPayment(order)">
                                        <i class="fas fa-credit-card me-1"></i> Pay Now
                                    </button>
                                </template>
                                <template x-if="order.bill_requested">
                                    <span class="badge" :class="order.payment_status === 'paid' ? 'bg-success' : 'bg-warning text-dark'">
                                        <i class="fas" :class="order.payment_status === 'paid' ? 'fa-check-circle' : 'fa-clock'"></i>
                                        <span x-text="order.payment_status === 'paid' ? ' Paid' : ' Bill Pending'"></span>
                                    </span>
                                </template>
                            </div>

                            <template x-if="order.items && order.items.some(i => i.status === 'cancelled')">
                                <div class="mt-2 small text-danger">
                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                    <template x-for="item in order.items.filter(i => i.status === 'cancelled')" :key="item.id">
                                        <span x-text="item.product_name + ' unavailable' + (item.rejection_reason ? ': ' + item.rejection_reason : '')"></span>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </template>
                    <template x-if="filteredOrders.length === 0">
                        <div class="text-center text-muted py-5">
                            <i class="fas fa-inbox fa-3x mb-3"></i>
                            <p>No orders found</p>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="paymentModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-credit-card me-2"></i>Process Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <small class="text-muted">Total Amount</small>
                        <h2 class="fw-bold text-primary" x-text="formatCurrency(paymentTotal)"></h2>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Payment Method</label>
                        <div class="payment-methods">
                            <input type="radio" class="btn-check" name="pmtMethod" id="waiterMethodCash" value="cash" x-model="paymentMethod" checked>
                            <label class="btn btn-outline-primary" for="waiterMethodCash"><i class="fas fa-money-bill me-1"></i> Cash</label>
                            <input type="radio" class="btn-check" name="pmtMethod" id="waiterMethodMobile" value="mobile_money" x-model="paymentMethod">
                            <label class="btn btn-outline-primary" for="waiterMethodMobile"><i class="fas fa-mobile-screen me-1"></i> Mobile Money</label>
                            <input type="radio" class="btn-check" name="pmtMethod" id="waiterMethodCard" value="card" x-model="paymentMethod">
                            <label class="btn btn-outline-primary" for="waiterMethodCard"><i class="fas fa-credit-card me-1"></i> Card</label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">Amount Received</label>
                        <div class="input-group">
                            <span class="input-group-text">UGX</span>
                            <input type="number" class="form-control form-control-lg" x-model="amountReceived" min="0" step="any" placeholder="0.00">
                        </div>
                    </div>
                    <div class="d-flex justify-content-between py-2 px-3 rounded" style="background:#f8f9fa;">
                        <span class="fw-semibold">Change Due</span>
                        <span class="fw-bold fs-5" :class="changeDue >= 0 ? 'text-success' : 'text-danger'" x-text="formatCurrency(changeDue)"></span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-primary" @click="submitPayment()" :disabled="amountReceived < paymentTotal">
                        <i class="fas fa-check me-1"></i> Complete Payment
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .payment-methods {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }
    .payment-methods .btn {
        flex: 1;
        min-width: 100px;
        white-space: nowrap;
    }
    .payment-methods .btn-outline-primary { border-color: #dee2e6; }
    .btn-check:checked + .btn-outline-primary { background: #7367f0; border-color: #7367f0; color: #fff; }
    .dark .modal-body .rounded { background: #1e2126 !important; }
</style>
@endpush

@push('scripts')
<script>
    function waiterOrders() {
        return {
            products: [],
            tables: [],
            orders: [],
            filterTable: '',
            refreshTimer: null,
            paymentOrderId: null,
            paymentTotal: 0,
            paymentMethod: 'cash',
            amountReceived: 0,
            newOrder: {
                table_id: '',
                customer_name: '',
                items: [{ product_id: '', qty: 1 }],
                notes: ''
            },
            async init() {
                await this.fetchAll();
                this.refreshTimer = setInterval(() => this.fetchOrders(), 10000);
            },
            async fetchAll() {
                try {
                    const [prodResp, tableResp, orderResp] = await Promise.all([
                        fetch('{{ route('waiter.products') }}'),
                        fetch('{{ route('waiter.tables.data') }}'),
                        fetch('{{ route('waiter.orders.data') }}')
                    ]);
                    const prodData = await prodResp.json();
                    const tableData = await tableResp.json();
                    const orderData = await orderResp.json();
                    this.products = prodData.products || [];
                    this.tables = tableData.tables || [];
                    this.orders = orderData.orders || [];
                } catch(e) {}
            },
            async fetchOrders() {
                try {
                    const resp = await fetch('{{ route('waiter.orders.data') }}');
                    const data = await resp.json();
                    this.orders = data.orders || [];
                } catch(e) {}
            },
            get filteredOrders() {
                if (!this.filterTable) return this.orders;
                return this.orders.filter(o => (o.table_id && o.table_id == this.filterTable) || (!o.table_id && this.filterTable === 'takeaway'));
            },
            get canSubmit() {
                return this.newOrder.table_id && this.newOrder.items.some(i => i.product_id && i.qty > 0);
            },
            progressBadgeClass(progress) {
                const classes = {1: 'bg-secondary', 2: 'bg-info', 3: 'bg-warning text-dark', 4: 'bg-success', 5: 'bg-primary'};
                return classes[progress] || 'bg-secondary';
            },
            progressBarClass(progress) {
                const classes = {1: 'bg-secondary', 2: 'bg-info', 3: 'bg-warning', 4: 'bg-success', 5: 'bg-primary'};
                return classes[progress] || 'bg-secondary';
            },
            addItem() { this.newOrder.items.push({ product_id: '', qty: 1 }); },
            removeItem(i) { if (this.newOrder.items.length > 1) this.newOrder.items.splice(i, 1); },
            async submitOrder() {
                try {
                    const resp = await fetch('{{ route('waiter.orders.store') }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                        body: JSON.stringify(this.newOrder)
                    });
                    const data = await resp.json();
                    if (data.success) {
                        Swal.fire({ icon: 'success', title: 'Order Placed', text: 'Order #' + data.order_id });
                        this.newOrder = { table_id: '', customer_name: '', items: [{ product_id: '', qty: 1 }], notes: '' };
                        await this.fetchOrders();
                    } else {
                        Swal.fire({ icon: 'error', title: 'Error', text: data.message || 'Failed to place order' });
                    }
                } catch(e) { Swal.fire({ icon: 'error', title: 'Error', text: 'Something went wrong' }); }
            },
            async markServed(id) {
                const result = await Swal.fire({ title: 'Mark as Delivered?', text: 'Confirm items have been served to the customer', icon: 'question', showCancelButton: true, confirmButtonText: 'Yes, delivered' });
                if (result.isConfirmed) {
                    await fetch('{{ route('waiter.orders.serve') }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                        body: JSON.stringify({ order_id: id })
                    });
                    await this.fetchOrders();
                }
            },
            async cancelOrder(id) {
                const result = await Swal.fire({ title: 'Cancel Order?', icon: 'warning', showCancelButton: true, confirmButtonText: 'Yes, cancel' });
                if (result.isConfirmed) {
                    await fetch('{{ route('waiter.orders.cancel') }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                        body: JSON.stringify({ order_id: id })
                    });
                    await this.fetchOrders();
                }
            },
            async requestBill(id) {
                await fetch('{{ route('waiter.orders.request-bill') }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: JSON.stringify({ order_id: id })
                });
                Swal.fire({ icon: 'success', title: 'Bill Requested', text: 'The cashier has been notified' });
                await this.fetchOrders();
            },
            get changeDue() {
                return parseFloat(this.amountReceived || 0) - this.paymentTotal;
            },
            openPayment(order) {
                this.paymentOrderId = order.id;
                this.paymentTotal = order.total;
                this.paymentMethod = 'cash';
                this.amountReceived = order.total;
                const modal = new bootstrap.Modal(document.getElementById('paymentModal'));
                modal.show();
            },
            async submitPayment() {
                if (!this.paymentOrderId || this.amountReceived < this.paymentTotal) return;
                try {
                    const resp = await fetch('{{ route('waiter.orders.pay') }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                        body: JSON.stringify({ order_id: this.paymentOrderId, payment_method: this.paymentMethod, amount_received: this.amountReceived })
                    });
                    const data = await resp.json();
                    if (data.success) {
                        bootstrap.Modal.getInstance(document.getElementById('paymentModal')).hide();
                        this.paymentOrderId = null;
                        this.paymentTotal = 0;
                        await this.fetchOrders();
                        this.showReceipt(data.bill_id, data.receipt_no);
                    } else {
                        Swal.fire({ icon: 'error', title: 'Payment Failed', text: data.message || 'Something went wrong' });
                    }
                } catch(e) {
                    Swal.fire({ icon: 'error', title: 'Error', text: 'Payment request failed' });
                }
            },
            async showReceipt(billId, receiptNo) {
                try {
                    const r = await fetch('{{ url('billing') }}/' + billId + '/receipt-content');
                    const d = await r.json();
                    Swal.fire({
                        icon: 'success',
                        title: 'Payment Successful',
                        html: '<div class="mb-2 text-muted small">Receipt #' + receiptNo + '</div>' + d.html,
                        showCancelButton: true,
                        confirmButtonText: '<i class="fas fa-print me-1"></i> Print Receipt',
                        cancelButtonText: 'Close',
                        width: 420,
                        padding: '1rem',
                    }).then(result => {
                        if (result.isConfirmed) {
                            this.printReceipt(billId);
                        }
                    });
                } catch(e) {
                    Swal.fire({ icon: 'success', title: 'Payment Successful', text: 'Receipt #' + receiptNo });
                }
            },
            printReceipt(billId) {
                const pw = window.open('', '_blank', 'width=400,height=600,menubar=no,location=no,status=no');
                fetch('{{ url('billing') }}/' + billId + '/receipt-content')
                    .then(r => r.json())
                    .then(d => {
                        pw.document.write('<!DOCTYPE html><html><head><title>Receipt</title></head><body>' + d.html + '<script>window.onload=function(){window.print();window.close();}<\/script></body></html>');
                        pw.document.close();
                    });
            },
            formatCurrency(val) {
                const s = window.currencySettings || { symbol: 'UGX', position: 'before', thousand_separator: ',', decimal_separator: '.', decimal_digits: 0 };
                const formatted = Number(val).toFixed(s.decimal_digits || 0).replace(/\B(?=(\d{3})+(?!\d))/g, s.thousand_separator || ',');
                return s.position === 'before' ? s.symbol + ' ' + formatted : formatted + ' ' + s.symbol;
            }
        }
    }
</script>
@endpush
