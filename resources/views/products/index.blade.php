@extends('layouts.app')
@section('title', 'Products')
@section('page-title', 'Products')

@section('breadcrumb-plugins')
    <a href="{{ route('products.create') }}" class="btn btn-primary btn-sm">
        <i class="fas fa-plus me-1"></i> Add New
    </a>
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between mb-3">
            <div class="d-flex gap-2 flex-wrap">
                <div class="input-group input-group-sm" style="max-width:300px;">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" class="form-control" id="tableSearch" placeholder="Search products...">
                </div>
                <select class="form-select form-select-sm" id="categoryFilter" style="max-width:150px;">
                    <option value="">All Categories</option>
                    @foreach($categories ?? [] as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Cost</th>
                        <th class="text-center">Stock</th>
                        <th class="text-center">Stock Status</th>
                        <th class="text-center">Active</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products ?? [] as $product)
                    <tr>
                        <td class="fw-medium">{{ $product->name }}</td>
                        <td>{{ $product->category->name ?? 'N/A' }}</td>
                        <td>{{ number_format($product->selling_price, 0) }}</td>
                        <td>{{ number_format($product->cost_price, 0) }}</td>
                        <td class="text-center">
                            <span class="badge bg-{{ $product->stock_status === 'low' ? 'danger' : ($product->stock_status === 'medium' ? 'warning' : 'success') }}">
                                {{ $product->current_stock }}
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-{{ $product->stock_status === 'low' ? 'danger' : ($product->stock_status === 'medium' ? 'warning' : 'success') }}">
                                {{ ucfirst($product->stock_status) }}
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-{{ $product->is_active ? 'success' : 'secondary' }}">
                                {{ $product->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="text-end">
                            <a href="{{ route('products.edit', $product->id) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                            <button class="btn btn-sm btn-outline-danger" onclick="confirmDelete('{{ route('products.destroy', $product->id) }}')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">No products found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($products ?? [], 'links'))
            <div class="d-flex justify-content-end">{{ $products->links() }}</div>
        @endif
    </div>
</div>

<form id="deleteForm" method="POST" class="d-none">
    @csrf @method('DELETE')
</form>
@endsection

@push('scripts')
<script>
    document.getElementById('tableSearch')?.addEventListener('keyup', function() {
        const val = this.value.toLowerCase();
        document.querySelectorAll('tbody tr').forEach(tr => {
            tr.style.display = tr.textContent.toLowerCase().includes(val) ? '' : 'none';
        });
    });
    document.getElementById('categoryFilter')?.addEventListener('change', function() {
        const val = this.value;
        document.querySelectorAll('tbody tr').forEach(tr => {
            if (!val) { tr.style.display = ''; return; }
            const cells = tr.querySelectorAll('td');
            tr.style.display = cells.length > 1 && cells[1].textContent.includes(val) ? '' : 'none';
        });
    });
</script>
@endpush
