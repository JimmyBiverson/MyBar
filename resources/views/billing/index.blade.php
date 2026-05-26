@extends('layouts.app')
@section('title', 'Bills')
@section('page-title', 'Bills')

@section('content')
<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between mb-3 flex-wrap gap-2">
            <div class="d-flex gap-2 flex-wrap">
                <div class="input-group input-group-sm" style="max-width:200px;">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" class="form-control" id="tableSearch" placeholder="Search bills...">
                </div>
                <input type="date" class="form-control form-control-sm" id="dateFrom" style="max-width:140px;">
                <input type="date" class="form-control form-control-sm" id="dateTo" style="max-width:140px;">
                <select class="form-select form-select-sm" id="statusFilter" style="max-width:130px;">
                    <option value="">All Status</option>
                    <option value="completed">Completed</option>
                    <option value="pending">Pending</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr><th>Invoice #</th><th>Customer</th><th>Items</th><th>Total</th><th>Paid</th><th>Payment</th><th>Status</th><th>Date</th><th class="text-end">Actions</th></tr>
                </thead>
                <tbody>
                    @forelse($bills ?? [] as $bill)
                    <tr>
                        <td class="fw-medium">#{{ $bill->invoice_no ?? $bill->id }}</td>
                        <td>{{ $bill->customer->name ?? 'Walk-in' }}</td>
                        <td>{{ $bill->items_count ?? $bill->items->count() }}</td>
                        <td>{{ number_format($bill->total, 0) }}</td>
                        <td>{{ number_format($bill->paid, 0) }}</td>
                        <td><span class="badge bg-info-subtle text-info">{{ ucfirst($bill->payment_method ?? 'cash') }}</span></td>
                        <td>
                            <span class="badge bg-{{ $bill->status === 'completed' ? 'success' : ($bill->status === 'pending' ? 'warning' : 'danger') }}">
                                {{ ucfirst($bill->status) }}
                            </span>
                        </td>
                        <td><small>{{ $bill->created_at->format('d M Y H:i') }}</small></td>
                        <td class="text-end">
                            <a href="{{ route('billing.show', $bill->id) }}" class="btn btn-sm btn-outline-info"><i class="fas fa-eye"></i></a>
                            <a href="{{ route('billing.print', $bill->id) }}" class="btn btn-sm btn-outline-secondary" target="_blank"><i class="fas fa-print"></i></a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="9" class="text-center text-muted py-4">No bills found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(method_exists($bills ?? [], 'links'))<div class="d-flex justify-content-end">{{ $bills->links() }}</div>@endif
    </div>
</div>
@endsection
@push('scripts')
<script>
    document.getElementById('tableSearch')?.addEventListener('keyup', function() {
        const v = this.value.toLowerCase();
        document.querySelectorAll('tbody tr').forEach(tr => tr.style.display = tr.textContent.toLowerCase().includes(v) ? '' : 'none');
    });
</script>
@endpush
