@extends('layouts.app')
@section('title', 'Purchases')
@section('page-title', 'Purchases')

@section('breadcrumb-plugins')
    <a href="{{ route('purchases.create') }}" class="btn btn-primary btn-sm">
        <i class="fas fa-plus me-1"></i> New Purchase
    </a>
@endsection

@php
    $stockStatus = session('stock_status');
@endphp

@section('content')
<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between mb-3 flex-wrap gap-2">
            <div class="d-flex gap-2 flex-wrap">
                <div class="input-group input-group-sm" style="max-width:250px;">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" class="form-control" id="tableSearch" placeholder="Search purchases...">
                </div>
                <select class="form-select form-select-sm" id="statusFilter" style="max-width:140px;">
                    <option value="">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="received">Received</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr><th>Reference</th><th>Supplier</th><th>Items</th><th>Total</th><th>Paid</th><th>Status</th><th>Date</th><th class="text-end">Actions</th></tr>
                </thead>
                <tbody>
                    @forelse($purchases ?? [] as $purchase)
                    <tr>
                        <td class="fw-medium">{{ $purchase->reference_no ?? '#' . $purchase->id }}</td>
                        <td>{{ $purchase->supplier->name ?? 'N/A' }}</td>
                        <td>{{ $purchase->items_count ?? $purchase->items()->count() }}</td>
                        <td>{{ number_format($purchase->total ?? 0) }}</td>
                        <td>{{ number_format($purchase->paid ?? 0) }}</td>
                        <td>
                            <span class="badge bg-{{ $purchase->status === 'received' ? 'success' : ($purchase->status === 'pending' ? 'warning' : 'danger') }}">
                                {{ ucfirst($purchase->status) }}
                            </span>
                        </td>
                        <td><small>{{ $purchase->date->format('d M Y') }}</small></td>
                        <td class="text-end">
                            <a href="{{ route('purchases.edit', $purchase->id) }}" class="btn btn-sm btn-outline-info"><i class="fas fa-eye"></i></a>
                            @if($purchase->status === 'pending')
                            <a href="{{ route('purchases.edit', $purchase->id) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                            @endif
                            <button class="btn btn-sm btn-outline-danger" onclick="confirmDelete('{{ route('purchases.destroy', $purchase->id) }}')"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">No purchases found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(method_exists($purchases ?? [], 'links'))<div class="d-flex justify-content-end">{{ $purchases->links() }}</div>@endif
    </div>
</div>
<form id="deleteForm" method="POST" class="d-none">@csrf @method('DELETE')</form>
@endsection
@push('scripts')
<script>
    document.getElementById('tableSearch')?.addEventListener('keyup', function() {
        const v = this.value.toLowerCase();
        document.querySelectorAll('tbody tr').forEach(tr => tr.style.display = tr.textContent.toLowerCase().includes(v) ? '' : 'none');
    });
    document.getElementById('statusFilter')?.addEventListener('change', function() {
        const v = this.value.toLowerCase();
        document.querySelectorAll('tbody tr').forEach(tr => {
            const status = tr.querySelector('.badge');
            tr.style.display = !v || (status && status.textContent.toLowerCase().includes(v)) ? '' : 'none';
        });
    });

    @if($stockStatus)
    document.addEventListener('DOMContentLoaded', function() {
        let html = '<table class="table table-sm table-borderless mb-0"><thead><tr><th>Product</th><th class="text-end">Stock</th><th class="text-center">Status</th></tr></thead><tbody>';
        @foreach($stockStatus as $item)
            html += '<tr><td>{{ $item['name'] }}</td><td class="text-end">{{ $item['stock'] }}</td><td class="text-center"><span class="badge bg-{{ $item['status'] === 'low' ? 'danger' : ($item['status'] === 'medium' ? 'warning' : 'success') }}">{{ ucfirst($item['status']) }}</span></td></tr>';
        @endforeach
        html += '</tbody></table>';

        Swal.fire({
            title: 'Stock Status After Receiving',
            html: html,
            icon: 'info',
            confirmButtonText: 'OK',
            confirmButtonColor: '#7367f0',
        });
    });
    @endif
</script>
@endpush
