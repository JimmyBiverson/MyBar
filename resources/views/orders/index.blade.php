@extends('layouts.app')
@section('title', 'Orders')
@section('page-title', 'Orders')

@section('breadcrumb-plugins')
    <a href="{{ route('orders.create') }}" class="btn btn-primary btn-sm">
        <i class="fas fa-plus me-1"></i> New Order
    </a>
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between mb-3 flex-wrap gap-2">
            <div class="d-flex gap-2 flex-wrap">
                <div class="input-group input-group-sm" style="max-width:250px;">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" class="form-control" id="tableSearch" placeholder="Search orders...">
                </div>
                <select class="form-select form-select-sm" id="statusFilter" style="max-width:140px;">
                    <option value="">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="preparing">Preparing</option>
                    <option value="ready">Ready</option>
                    <option value="served">Served</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr><th>Order #</th><th>Table</th><th>Items</th><th>Total</th><th>Status</th><th>Date</th><th class="text-end">Actions</th></tr>
                </thead>
                <tbody>
                    @forelse($orders ?? [] as $order)
                    <tr>
                        <td class="fw-medium">#{{ $order->id }}</td>
                        <td>{{ $order->table->name ?? 'Takeaway' }}</td>
                        <td>{{ $order->items_count ?? $order->items()->count() }}</td>
                        <td>{{ number_format($order->total ?? 0) }}</td>
                        <td>
                            <span class="badge bg-{{ $order->status === 'pending' ? 'warning' : ($order->status === 'preparing' ? 'info' : ($order->status === 'ready' ? 'success' : ($order->status === 'served' ? 'primary' : 'danger'))) }}">
                                {{ ucfirst($order->status) }}
                            </span>
                        </td>
                        <td><small>{{ $order->created_at->format('d M Y H:i') }}</small></td>
                        <td class="text-end">
                            <a href="{{ route('orders.show', $order->id) }}" class="btn btn-sm btn-outline-info"><i class="fas fa-eye"></i></a>
                            <button class="btn btn-sm btn-outline-danger" onclick="confirmDelete('{{ route('orders.destroy', $order->id) }}')"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">No orders found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(method_exists($orders ?? [], 'links'))<div class="d-flex justify-content-end">{{ $orders->links() }}</div>@endif
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
</script>
@endpush
