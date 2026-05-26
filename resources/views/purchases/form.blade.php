<div class="row g-3">
    <div class="col-md-4">
        <label class="form-label">Supplier <span class="text-danger">*</span></label>
        <select class="form-select @error('supplier_id') is-invalid @enderror" name="supplier_id" required>
            <option value="">Select Supplier</option>
            @foreach($suppliers ?? \App\Models\Supplier::all() as $supplier)
                <option value="{{ $supplier->id }}" {{ old('supplier_id', $purchase->supplier_id ?? '') == $supplier->id ? 'selected' : '' }}>{{ $supplier->name }}</option>
            @endforeach
        </select>
        @error('supplier_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-4">
        <label class="form-label">Reference No</label>
        <input type="text" class="form-control" name="reference_no" value="{{ old('reference_no', $purchase->reference_no ?? 'PO-' . date('Ymd-His')) }}">
    </div>
    <div class="col-md-4">
        <label class="form-label">Date <span class="text-danger">*</span></label>
        <input type="date" class="form-control @error('date') is-invalid @enderror" name="date" value="{{ old('date', isset($purchase) ? $purchase->date->format('Y-m-d') : date('Y-m-d')) }}" required>
        @error('date') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<hr>
<h6 class="fw-semibold mb-3"><i class="fas fa-boxes me-2"></i>Purchase Items</h6>

<div class="purchase-items" x-data="purchaseItems()">
    <table class="table table-bordered">
        <thead class="table-light">
            <tr>
                <th style="width:40%;">Product</th>
                <th style="width:15%;">Quantity</th>
                <th style="width:15%;">Unit Cost</th>
                <th style="width:15%;">Subtotal</th>
                <th style="width:15%;"></th>
            </tr>
        </thead>
        <tbody>
            <template x-for="(item, index) in items" :key="index">
                <tr>
                    <td>
                        <select class="form-select form-select-sm" x-model="item.product_id" :name="'items['+index+'][product_id]'" required>
                            <option value="">Select Product</option>
                            @foreach(\App\Models\Product::all() as $product)
                                <option value="{{ $product->id }}">{{ $product->name }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <input type="number" class="form-control form-control-sm" x-model="item.qty" :name="'items['+index+'][qty]'" min="1" required>
                    </td>
                    <td>
                        <input type="number" step="0.01" class="form-control form-control-sm" x-model="item.cost" :name="'items['+index+'][cost]'" min="0" required>
                    </td>
                    <td class="fw-bold" x-text="formatCurrency(item.qty * item.cost)"></td>
                    <td>
                        <button type="button" class="btn btn-sm btn-outline-danger" @click="removeItem(index)" x-show="items.length > 1">
                            <i class="fas fa-times"></i>
                        </button>
                    </td>
                </tr>
            </template>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" class="text-end fw-bold">Total:</td>
                <td class="fw-bold text-primary" x-text="formatCurrency(total)"></td>
                <td></td>
            </tr>
        </tfoot>
    </table>
    <button type="button" class="btn btn-sm btn-outline-secondary" @click="addItem()">
        <i class="fas fa-plus me-1"></i> Add Item
    </button>
    <input type="hidden" name="total" x-model="total">
</div>

<hr>
<div class="row g-3">
    <div class="col-md-4">
        <label class="form-label">Amount Paid</label>
        <input type="number" step="0.01" class="form-control" name="paid" value="{{ old('paid', $purchase->paid ?? 0) }}">
    </div>
    <div class="col-md-4">
        <label class="form-label">Payment Method</label>
        <select class="form-select" name="payment_method">
            <option value="cash" {{ old('payment_method', $purchase->payment_method ?? '') === 'cash' ? 'selected' : '' }}>Cash</option>
            <option value="mobile_money" {{ old('payment_method', $purchase->payment_method ?? '') === 'mobile_money' ? 'selected' : '' }}>Mobile Money</option>
            <option value="bank" {{ old('payment_method', $purchase->payment_method ?? '') === 'bank' ? 'selected' : '' }}>Bank Transfer</option>
            <option value="credit" {{ old('payment_method', $purchase->payment_method ?? '') === 'credit' ? 'selected' : '' }}>Credit</option>
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label">Status</label>
        <select class="form-select" name="status">
            <option value="pending" {{ old('status', $purchase->status ?? '') === 'pending' ? 'selected' : '' }}>Pending</option>
            <option value="received" {{ old('status', $purchase->status ?? '') === 'received' ? 'selected' : '' }}>Received</option>
            <option value="cancelled" {{ old('status', $purchase->status ?? '') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
        </select>
    </div>
    <div class="col-12">
        <label class="form-label">Notes</label>
        <textarea class="form-control" name="notes" rows="2">{{ old('notes', $purchase->notes ?? '') }}</textarea>
    </div>
</div>

@push('scripts')
<script>
    function purchaseItems() {
        const existing = @json(isset($purchase) ? ($purchase->items ?? []) : []);
        return {
            items: existing.length > 0 ? existing.map(i => ({ product_id: i.product_id, qty: i.qty, cost: i.cost })) : [{ product_id: '', qty: 1, cost: 0 }],
            addItem() { this.items.push({ product_id: '', qty: 1, cost: 0 }); },
            removeItem(index) { if (this.items.length > 1) this.items.splice(index, 1); },
            get total() { return this.items.reduce((sum, i) => sum + (parseFloat(i.qty) || 0) * (parseFloat(i.cost) || 0), 0); },
            formatCurrency(val) {
                const s = window.currencySettings || { symbol: 'UGX', position: 'before', thousand_separator: ',', decimal_separator: '.', decimal_digits: 0 };
                const formatted = Number(val).toFixed(s.decimal_digits || 0).replace(/\B(?=(\d{3})+(?!\d))/g, s.thousand_separator || ',');
                return s.position === 'before' ? s.symbol + ' ' + formatted : formatted + ' ' + s.symbol;
            }
        }
    }
</script>
@endpush
