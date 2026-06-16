@extends('layouts.app')
@section('title', 'New Order')
@section('page-title', 'New Order')

@section('breadcrumb-plugins')
    <a href="{{ route('waiter.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i> Back</a>
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('waiter.orders.store') }}">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Table <span class="text-danger">*</span></label>
                    <select class="form-select @error('table_id') is-invalid @enderror" name="table_id" required>
                        <option value="">Select Table</option>
                        @foreach($tables ?? [] as $table)
                            <option value="{{ $table->id }}" {{ request('table_id') == $table->id ? 'selected' : '' }}>{{ $table->name }}</option>
                        @endforeach
                    </select>
                    @error('table_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Notes</label>
                    <input type="text" class="form-control" name="notes" value="{{ old('notes') }}">
                </div>
            </div>

            <hr>
            <h6 class="fw-semibold mb-3"><i class="fas fa-boxes me-2"></i>Order Items</h6>

            <div class="table-responsive">
                <table class="table table-bordered" id="itemsTable">
                    <thead class="table-light">
                        <tr>
                            <th style="width:45%;">Product</th>
                            <th style="width:20%;">Quantity</th>
                            <th style="width:25%;">Notes</th>
                            <th style="width:10%;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="item-row">
                            <td>
                                <select class="form-select form-select-sm" name="items[0][product_id]" required>
                                    <option value="">Select Product</option>
                                    @foreach($products ?? [] as $product)
                                        <option value="{{ $product->id }}">{{ $product->name }} ({{ formatCurrency($product->selling_price) }})</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <input type="number" class="form-control form-control-sm" name="items[0][qty]" value="1" min="1" required>
                            </td>
                            <td>
                                <input type="text" class="form-control form-control-sm" name="items[0][notes]" placeholder="Optional">
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-outline-danger remove-item"><i class="fas fa-times"></i></button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <button type="button" class="btn btn-sm btn-outline-secondary" id="addItem"><i class="fas fa-plus me-1"></i> Add Item</button>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Place Order</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let itemIndex = 1;
    document.addEventListener('DOMContentLoaded', () => {
        const cachedProducts = window.getCachedProducts();
        if (cachedProducts && cachedProducts.length > 0) {
            const updateSelect = (select) => {
                const selectedVal = select.value;
                select.innerHTML = '<option value="">Select Product</option>';
                cachedProducts.forEach(p => {
                    const opt = document.createElement('option');
                    opt.value = p.id;
                    opt.textContent = `${p.name} (${parseFloat(p.selling_price).toLocaleString()})`;
                    if (p.id == selectedVal) opt.selected = true;
                    select.appendChild(opt);
                });
            };
            const initialSelect = document.querySelector('select[name="items[0][product_id]"]');
            if (initialSelect) updateSelect(initialSelect);
        }
    });

    document.getElementById('addItem')?.addEventListener('click', function() {
        const tbody = document.querySelector('#itemsTable tbody');
        const row = document.querySelector('.item-row').cloneNode(true);
        row.innerHTML = row.innerHTML.replace(/items\[\d+\]/g, `items[${itemIndex}]`);
        row.querySelectorAll('input').forEach(i => i.value = '');
        row.querySelectorAll('select').forEach(s => s.selectedIndex = 0);
        tbody.appendChild(row);
        itemIndex++;
    });
    document.addEventListener('click', function(e) {
        if (e.target.closest('.remove-item')) {
            const rows = document.querySelectorAll('.item-row');
            if (rows.length > 1) e.target.closest('.item-row').remove();
        }
    });

    document.querySelector('form')?.addEventListener('submit', function(e) {
        if (!navigator.onLine) {
            e.preventDefault();
            
            const items = [];
            document.querySelectorAll('.item-row').forEach(row => {
                const select = row.querySelector('select[name*="[product_id]"]');
                const qtyInput = row.querySelector('input[name*="[qty]"]');
                const notesInput = row.querySelector('input[name*="[notes]"]');
                if (select && select.value) {
                    items.push({
                        product_id: select.value,
                        qty: qtyInput ? parseInt(qtyInput.value) : 1,
                        notes: notesInput ? notesInput.value : ''
                    });
                }
            });

            if (items.length === 0) {
                Swal.fire('Error', 'Please select at least one product', 'error');
                return;
            }

            const orderData = {
                table_id: document.querySelector('select[name="table_id"]').value,
                notes: document.querySelector('input[name="notes"]').value,
                customer_name: '',
                items: items
            };

            window.OfflineSyncManager.saveTransaction({
                type: 'order',
                data: orderData,
                timestamp: new Date().getTime()
            });

            Swal.fire({
                icon: 'warning',
                title: 'Offline Order Placed',
                text: 'Order stored locally and will sync when internet is back.',
                confirmButtonText: 'OK'
            }).then(() => {
                window.location.href = '{{ route('waiter.index') }}';
            });
        }
    });
</script>
@endpush
