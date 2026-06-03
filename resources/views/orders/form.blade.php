@extends('layouts.app')
@section('title', 'New Order')
@section('page-title', 'New Order')

@section('breadcrumb-plugins')
    <a href="{{ route('orders.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i> Back</a>
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('orders.store') }}">
            @csrf
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Order Type <span class="text-danger">*</span></label>
                    <select class="form-select @error('order_type') is-invalid @enderror" name="order_type" required>
                        <option value="dine_in" {{ old('order_type') === 'dine_in' ? 'selected' : '' }}>Dine In</option>
                        <option value="takeaway" {{ old('order_type') === 'takeaway' ? 'selected' : '' }}>Takeaway</option>
                        <option value="delivery" {{ old('order_type') === 'delivery' ? 'selected' : '' }}>Delivery</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Customer</label>
                    <select class="form-select @error('customer_id') is-invalid @enderror" name="customer_id">
                        <option value="">Walk-in</option>
                        @foreach($customers ?? [] as $customer)
                            <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>{{ $customer->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
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
                                        <option value="{{ $product->id }}" data-stock="{{ $product->current_stock }}" data-status="{{ $product->stock_status }}">{{ $product->name }} (Stock: {{ $product->current_stock }} - {{ ucfirst($product->stock_status) }})</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <input type="number" class="form-control form-control-sm" name="items[0][quantity]" value="1" min="1" required>
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
                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Create Order</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let itemIndex = 1;
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
</script>
@endpush
