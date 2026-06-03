<div class="cart-items p-3">
    <template x-if="activeOrderId && !orderAccepted">
        <div class="mb-3">
            <button class="btn btn-success btn-sm w-100" @click="acceptOrder()">
                <i class="fas fa-check-circle me-1"></i> Accept Order (Notify Waiter)
            </button>
        </div>
    </template>

    <template x-if="activeOrderId">
        <div class="mb-2 small text-muted">
            <i class="fas fa-info-circle me-1"></i>
            <span>Items from this order:</span>
            <template x-for="(status, itemId) in orderItemStatuses" :key="itemId">
                <span class="badge ms-1" :class="status.selected ? 'bg-success' : 'bg-danger'" x-text="status.selected ? 'Selected' : 'Unavailable'"></span>
            </template>
        </div>
    </template>

    <template x-for="(item, index) in cart" :key="item.id">
        <div class="cart-item d-flex align-items-center gap-2 mb-2 p-2 rounded" style="background:#f8f9fa;">
            <div class="flex-grow-1">
                <div class="fw-semibold small" x-text="item.name"></div>
                <div class="text-primary small fw-bold" x-text="formatCurrency(item.selling_price)"></div>
            </div>
            <div class="d-flex align-items-center gap-1">
                <button class="btn btn-sm btn-outline-secondary px-2" @click="updateQty(item, -1)">-</button>
                <span class="fw-bold mx-1" x-text="item.qty" style="min-width:20px;text-align:center;"></span>
                <button class="btn btn-sm btn-outline-secondary px-2" @click="updateQty(item, 1)">+</button>
            </div>
            <div class="text-end" style="min-width:70px;">
                <div class="fw-bold small" x-text="formatCurrency(item.selling_price * item.qty)"></div>
            </div>
            <template x-if="activeOrderId && item.order_item_id">
                <button class="btn btn-sm btn-outline-danger" @click="showUnavailableModal(item)" title="Mark as unavailable">
                    <i class="fas fa-ban"></i>
                </button>
            </template>
            <template x-if="!activeOrderId">
                <button class="btn btn-sm text-danger" @click="removeItem(index)">
                    <i class="fas fa-times"></i>
                </button>
            </template>
        </div>
    </template>
    <template x-if="cart.length === 0">
        <div class="text-center text-muted py-5">
            <i class="fas fa-cart-plus fa-3x mb-3"></i>
            <p>Cart is empty</p>
            <small>Click products to add them</small>
        </div>
    </template>
</div>

<div class="cart-summary border-top p-3">
    <div class="mb-2">
        <label class="form-label small mb-1">Customer</label>
        <select class="form-select form-select-sm" x-model="selectedCustomer">
            <option :value="null">Walk-in Customer</option>
            <template x-for="c in customers" :key="c.id">
                <option :value="c.id" x-text="c.name"></option>
            </template>
        </select>
    </div>

    <div class="d-flex justify-content-between small mb-1">
        <span>Subtotal</span>
        <span x-text="formatCurrency(subtotal)"></span>
    </div>

    <div class="mb-2">
        <div class="input-group input-group-sm">
            <span class="input-group-text small">Discount</span>
            <input type="number" class="form-control form-control-sm" x-model="discount" min="0" placeholder="0">
            <select class="form-select form-select-sm" style="max-width:90px;" x-model="discountType">
                <option value="percentage">%</option>
                <option value="fixed">Fixed</option>
            </select>
        </div>
        <div class="d-flex justify-content-between small text-danger mt-1">
            <span></span>
            <span x-text="'-' + formatCurrency(discountAmount)"></span>
        </div>
    </div>

    <hr class="my-2">
    <div class="d-flex justify-content-between fw-bold fs-5 mb-3">
        <span>Total</span>
        <span class="text-primary" x-text="formatCurrency(total)"></span>
    </div>

    <div class="d-grid gap-2">
        <button class="btn btn-outline-secondary btn-sm" @click="holdCart()" :disabled="cart.length === 0">
            <i class="fas fa-clock me-1"></i> Hold
        </button>
        <button class="btn btn-outline-secondary btn-sm" @click="splitBill()" :disabled="cart.length === 0">
            <i class="fas fa-scissors me-1"></i> Split Bill
        </button>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#paymentModal" :disabled="cart.length === 0">
            <i class="fas fa-credit-card me-1"></i> Pay Now
        </button>
    </div>
</div>

<style>
    .cart-item { transition: background 0.2s; }
    .cart-item:hover { background: #f0f0f0 !important; }
    .dark .cart-item { background: #1e2126 !important; }
    .dark .cart-item:hover { background: #25282f !important; }
</style>
