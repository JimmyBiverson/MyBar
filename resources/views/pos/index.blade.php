@extends('layouts.app')
@section('title', 'POS Terminal')
@section('page-title', 'POS Terminal')

@section('breadcrumb-plugins')
    <button class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#holdModal">
        <i class="fas fa-clock me-1"></i> Held Bills
    </button>
@endsection

@section('content')
<div class="pos-container" x-data="posApp()" x-init="init()">
    <div class="row g-3">
        <div class="col-lg-7 col-xl-8">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex gap-2 mb-3 flex-wrap">
                        <div class="flex-grow-1">
                            <div class="input-group">
                                <span class="input-group-text bg-transparent"><i class="fas fa-search"></i></span>
                                <input type="text" class="form-control" placeholder="Search products..." x-model="search" @input="filterProducts">
                            </div>
                        </div>
                        <button class="btn btn-sm btn-outline-secondary" @click="showBarcodeInput = !showBarcodeInput" title="Toggle Barcode Scanner">
                            <i class="fas fa-barcode me-1"></i> Scan
                        </button>
                        <template x-if="showBarcodeInput">
                            <div class="flex-grow-1">
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent"><i class="fas fa-barcode"></i></span>
                                    <input type="text" class="form-control" placeholder="Scan barcode..." x-model="barcodeInput" @keydown.enter="handleBarcode" id="barcodeInput">
                                </div>
                            </div>
                        </template>
                        <button class="btn btn-sm btn-outline-primary" :class="{ active: selectedCategory === 'all' }" @click="selectedCategory = 'all'; filterProducts()">All</button>
                        <template x-for="cat in categories" :key="cat.id">
                            <button class="btn btn-sm" :class="selectedCategory === cat.id ? 'btn-primary' : 'btn-outline-primary'" @click="selectedCategory = cat.id; filterProducts()" x-text="cat.name"></button>
                        </template>
                    </div>

                    <div class="row g-2" id="productGrid">
                        <template x-for="product in filteredProducts" :key="product.id">
                            <div class="col-6 col-md-4 col-lg-3">
                                <div class="product-card card h-100" @click="addToCart(product)" role="button">
                                    <div class="card-body text-center p-3">
                                        <div class="product-img mb-2">
                                            <i class="fas fa-beer fa-2x text-primary"></i>
                                        </div>
                                        <h6 class="mb-1 small fw-semibold" x-text="product.name"></h6>
                                        <div class="text-primary fw-bold small" x-text="formatCurrency(product.selling_price)"></div>
                                        <small class="text-muted d-block" x-text="'Stock: ' + product.stock"></small>
                                    </div>
                                </div>
                            </div>
                        </template>
                        <template x-if="filteredProducts.length === 0">
                            <div class="col-12 text-center py-5 text-muted">
                                <i class="fas fa-box-open fa-3x mb-3"></i><br>
                                No products found
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5 col-xl-4">
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-clipboard-list me-2"></i>Pending Orders</span>
                    <span class="badge bg-warning" x-text="pendingOrders.length"></span>
                </div>
                <div class="card-body p-0" style="max-height:200px;overflow-y:auto;">
                    <template x-if="pendingOrders.length === 0">
                        <div class="text-center text-muted py-3">
                            <small>No pending orders</small>
                        </div>
                    </template>
                    <template x-for="order in pendingOrders" :key="order.id">
                        <div class="border-bottom p-2 px-3 d-flex justify-content-between align-items-center" style="cursor:pointer;" @click="loadPendingOrder(order)">
                            <div class="small">
                                <strong x-text="'#' + order.order_number"></strong>
                                <span class="text-muted ms-1" x-text="order.table_name"></span>
                                <br>
                                <small class="text-muted" x-text="'Waiter: ' + order.waiter_name + ' | ' + order.items_count + ' items'"></small>
                                <span class="badge ms-1" :class="order.status === 'pending' ? 'bg-warning text-dark' : 'bg-info'" x-text="order.status"></span>
                            </div>
                            <div class="text-end small">
                                <div class="fw-bold" x-text="formatCurrency(order.total)"></div>
                                <small class="text-muted" x-text="order.created_at"></small>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <div class="card cart-panel">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <i class="fas fa-shopping-cart me-2"></i>Cart
                        <template x-if="activeOrderId">
                            <small class="text-muted ms-2">Order #<span x-text="activeOrderId"></span></small>
                        </template>
                        <template x-if="activeOrderId && !orderAccepted">
                            <span class="badge bg-warning text-dark ms-1">Pending</span>
                        </template>
                        <template x-if="activeOrderId && orderAccepted">
                            <span class="badge bg-success ms-1">Accepted</span>
                        </template>
                    </div>
                    <span class="badge bg-primary" x-text="cart.length + ' items'"></span>
                </div>
                <div class="card-body p-0">
                    @include('pos.partials.cart')
                </div>
            </div>
        </div>
    </div>

    @include('pos.partials.payment-modal')

    <div class="modal fade" id="unavailableModal" tabindex="-1">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Mark Item Unavailable</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="small text-muted mb-2" x-text="'Item: ' + (unavailableItem ? unavailableItem.name : '')"></p>
                    <label class="form-label small">Reason</label>
                    <textarea class="form-control" x-model="unavailableReason" rows="2" placeholder="Why is this item unavailable?"></textarea>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-danger btn-sm" @click="confirmUnavailable()" :disabled="!unavailableReason.trim()">
                        <i class="fas fa-ban me-1"></i> Mark Unavailable
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="holdModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Held Bills</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                    <table class="table">
                        <thead><tr><th>#</th><th>Items</th><th>Total</th><th>Date</th><th></th></tr></thead>
                        <tbody>
                            <template x-for="(bill, i) in heldBills" :key="bill.id">
                                <tr>
                                    <td x-text="'#' + bill.id"></td>
                                    <td x-text="bill.items_count"></td>
                                    <td x-text="formatCurrency(bill.total)"></td>
                                    <td x-text="bill.held_at"></td>
                                    <td><button class="btn btn-sm btn-primary" @click="resumeHold(bill.id)"><i class="fas fa-rotate-left"></i></button></td>
                                </tr>
                            </template>
                            <template x-if="heldBills.length === 0">
                                <tr><td colspan="5" class="text-center text-muted py-3">No held bills</td></tr>
                            </template>
                        </tbody>
                    </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .product-card { cursor: pointer; transition: all 0.2s; border: 2px solid transparent; }
    .product-card:active { transform: scale(0.97); }
    @media (hover: hover) {
        .product-card:hover { border-color: #7367f0; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(115,103,240,0.15); }
    }
    .cart-panel .card-body { max-height: calc(100vh - 260px); overflow-y: auto; }
    .pos-container .btn-sm { font-size: 0.78rem; }
    @media (max-width: 991px) { .cart-panel { position: fixed; bottom: 0; left: 0; right: 0; z-index: 1050; border-radius: 16px 16px 0 0; max-height: 60vh; } }
    .order-item-checkbox { width: 18px; height: 18px; cursor: pointer; }
    .item-unavailable { opacity: 0.5; text-decoration: line-through; }
</style>
@endpush

@push('scripts')
<script>
    function posApp() {
        return {
            search: '',
            selectedCategory: 'all',
            showBarcodeInput: false,
            barcodeInput: '',
            products: @json($products ?? []),
            categories: @json($categories ?? []),
            cart: [],
            customers: @json($customers ?? []),
            selectedCustomer: null,
            discount: 0,
            discountType: 'percentage',
            heldBills: [],
            pendingOrders: [],
            activeOrderId: null,
            orderAccepted: false,
            orderItemStatuses: {},
            unavailableItem: null,
            unavailableReason: '',
            taxRate: {{ $taxRate ?? 0 }},
            serviceChargeRate: {{ $serviceChargeRate ?? 0 }},
            filteredProducts: [],
            init() {
                this.filteredProducts = [...this.products];
                this.fetchPendingOrders();
                window.cacheAppData(this.products, this.categories);
            },
            async fetchPendingOrders() {
                try {
                    const resp = await fetch('{{ route('pos.orders') }}');
                    const data = await resp.json();
                    this.pendingOrders = data.orders || [];
                } catch(e) { this.pendingOrders = []; }
                setTimeout(() => this.fetchPendingOrders(), 15000);
            },
            handleBarcode() {
                const code = this.barcodeInput.trim();
                if (!code) return;
                const product = this.products.find(p => p.barcode && p.barcode === code);
                if (product) {
                    this.addToCart(product);
                    this.barcodeInput = '';
                } else {
                    Swal.fire({ icon: 'warning', title: 'Not Found', text: 'No product found with barcode: ' + code });
                    this.barcodeInput = '';
                }
            },
            filterProducts() {
                let result = this.products;
                if (this.selectedCategory !== 'all') {
                    result = result.filter(p => p.category_id == this.selectedCategory);
                }
                if (this.search) {
                    const s = this.search.toLowerCase();
                    result = result.filter(p => p.name.toLowerCase().includes(s) || (p.barcode && p.barcode.includes(s)));
                }
                this.filteredProducts = result;
            },
            addToCart(product) {
                const existing = this.cart.find(c => c.id === product.id);
                if (existing) {
                    existing.qty = Math.min(existing.qty + 1, product.stock);
                } else {
                    this.cart.push({ ...product, qty: 1 });
                }
            },
            updateQty(item, delta) {
                item.qty = Math.max(1, Math.min(item.qty + delta, item.stock));
            },
            removeItem(index) {
                this.cart.splice(index, 1);
            },
            get subtotal() {
                return this.cart.reduce((sum, item) => sum + (item.selling_price * item.qty), 0);
            },
            get discountAmount() {
                if (!this.discount) return 0;
                return this.discountType === 'percentage'
                    ? this.subtotal * (this.discount / 100)
                    : this.discount;
            },
            get tax() {
                return (this.subtotal - this.discountAmount) * (this.taxRate / 100);
            },
            get serviceCharge() {
                return (this.subtotal - this.discountAmount) * (this.serviceChargeRate / 100);
            },
            get total() {
                return this.subtotal - this.discountAmount + this.tax + this.serviceCharge;
            },
            get itemCount() {
                return this.cart.reduce((sum, item) => sum + item.qty, 0);
            },
            get grandTotal() { return this.total; },
            formatCurrency(val) {
                const s = window.currencySettings || { symbol: 'UGX', position: 'before', thousand_separator: ',', decimal_separator: '.', decimal_digits: 0 };
                const formatted = Number(val).toFixed(s.decimal_digits || 0).replace(/\B(?=(\d{3})+(?!\d))/g, s.thousand_separator || ',');
                return s.position === 'before' ? s.symbol + ' ' + formatted : formatted + ' ' + s.symbol;
            },
            loadPendingOrder(order) {
                this.activeOrderId = order.id;
                this.orderAccepted = order.status === 'confirmed';
                this.orderItemStatuses = {};
                order.items.forEach(i => {
                    this.orderItemStatuses[i.order_item_id] = { selected: i.status !== 'cancelled', status: i.status };
                });

                this.cart = order.items
                    .filter(i => i.status !== 'cancelled')
                    .map(i => ({
                        id: i.id,
                        order_item_id: i.order_item_id,
                        name: i.name,
                        selling_price: i.selling_price,
                        qty: i.qty,
                        stock: i.stock,
                    }));
                this.discount = 0;
                this.discountType = 'percentage';
                this.selectedCustomer = null;
                Swal.fire({ icon: 'info', title: 'Order Loaded', text: 'Order #' + order.order_number + ' from ' + order.waiter_name, timer: 2000, showConfirmButton: false });
            },
            async acceptOrder() {
                if (!this.activeOrderId) return;
                const result = await Swal.fire({ title: 'Accept Order?', text: 'Confirm receipt of this order', icon: 'question', showCancelButton: true, confirmButtonText: 'Yes, accept' });
                if (!result.isConfirmed) return;

                try {
                    const url = '{{ route('pos.accept-order', ['order' => 'ORDER_ID']) }}'.replace('ORDER_ID', this.activeOrderId);
                    const resp = await fetch(url, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                        body: JSON.stringify({})
                    });
                    const data = await resp.json();
                    if (data.success) {
                        this.orderAccepted = true;
                        Swal.fire({ icon: 'success', title: 'Order Accepted', timer: 1500, showConfirmButton: false });
                        this.fetchPendingOrders();
                    }
                } catch(e) {
                    Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to accept order' });
                }
            },
            showUnavailableModal(item) {
                this.unavailableItem = item;
                this.unavailableReason = '';
                const modal = new bootstrap.Modal(document.getElementById('unavailableModal'));
                modal.show();
            },
            async confirmUnavailable() {
                if (!this.unavailableItem || !this.unavailableReason.trim()) return;

                try {
                    const resp = await fetch('{{ route('pos.item-unavailable') }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                        body: JSON.stringify({ order_item_id: this.unavailableItem.order_item_id, rejection_reason: this.unavailableReason.trim() })
                    });
                    const data = await resp.json();
                    if (data.success) {
                        this.orderItemStatuses[this.unavailableItem.order_item_id] = { selected: false, status: 'cancelled' };
                        const idx = this.cart.findIndex(c => c.order_item_id === this.unavailableItem.order_item_id);
                        if (idx !== -1) this.cart.splice(idx, 1);
                        bootstrap.Modal.getInstance(document.getElementById('unavailableModal')).hide();
                        Swal.fire({ icon: 'info', title: 'Marked Unavailable', text: this.unavailableItem.name + ' has been marked unavailable', timer: 1500, showConfirmButton: false });
                    }
                } catch(e) {
                    Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to mark item' });
                }
            },
            resetCart() {
                this.cart = [];
                this.discount = 0;
                this.discountType = 'percentage';
                this.selectedCustomer = null;
                this.activeOrderId = null;
                this.orderAccepted = false;
                this.orderItemStatuses = {};
            },
            async holdCart() {
                const resp = await fetch('{{ route('pos.hold') }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: JSON.stringify({ items: this.cart, customer_id: this.selectedCustomer, discount: this.discount, discount_type: this.discountType })
                });
                const data = await resp.json();
                if (data.success) { this.resetCart(); Swal.fire('Held', 'Bill held successfully', 'success'); }
            },
            async resumeHold(id) {
                const resp = await fetch('{{ route('pos.resume', ['bill' => 'BILL_ID']) }}'.replace('BILL_ID', id));
                const data = await resp.json();
                if (data.success) {
                    this.cart = data.items;
                    this.selectedCustomer = data.customer_id;
                    this.discount = data.discount;
                    this.discountType = data.discount_type;
                    bootstrap.Modal.getInstance(document.getElementById('holdModal')).hide();
                }
            },
            splitBill() {
                Swal.fire({ title: 'Split Bill', text: 'Split bill functionality - coming soon', icon: 'info' });
            },
            async processPayment(method, amountReceived) {
                const billedItemIds = Object.entries(this.orderItemStatuses)
                    .filter(([_, v]) => v.selected)
                    .map(([id, _]) => parseInt(id));

                const resp = await fetch('{{ route('pos.payment') }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: JSON.stringify({
                        items: this.cart.map(i => ({ id: i.id, qty: i.qty, price: i.selling_price })),
                        customer_id: this.selectedCustomer,
                        discount: this.discount,
                        discount_type: this.discountType,
                        payment_method: method,
                        amount_received: amountReceived,
                        total: this.total,
                        order_id: this.activeOrderId,
                        billed_item_ids: billedItemIds.length > 0 ? billedItemIds : null,
                    })
                });
                const data = await resp.json();
                if (data.success) {
                    this.resetCart();
                    Swal.fire({ icon: 'success', title: 'Payment Successful', text: 'Receipt #' + data.receipt_no }).then(() => {
                        window.open('{{ url('billing') }}/' + data.bill_id + '/print', '_blank');
                    });
                    this.fetchPendingOrders();
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: data.message || 'Payment failed' });
                }
            }
        }
    }
</script>
@endpush
