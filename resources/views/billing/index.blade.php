@extends('layouts.app')
@section('title', 'Bills')
@section('page-title', 'Bills')

@section('content')
<div class="row g-3 mb-3">
    <div class="col-md-3">
        <div class="card bg-primary text-white shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <i class="fas fa-receipt fa-2x opacity-50"></i>
                <div>
                    <div class="fs-4 fw-bold">{{ $todayStats->total_count ?? 0 }}</div>
                    <div class="small opacity-75">Today's Bills</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <i class="fas fa-user-tie fa-2x opacity-50"></i>
                <div>
                    <div class="fs-4 fw-bold">{{ $todayStats->waiter_count ?? 0 }}</div>
                    <div class="small opacity-75">by Waiters</div>
                    <div class="small opacity-75">{{ formatCurrency($todayStats->waiter_total ?? 0) }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-secondary text-white shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <i class="fas fa-cash-register fa-2x opacity-50"></i>
                <div>
                    <div class="fs-4 fw-bold">{{ $todayStats->cashier_count ?? 0 }}</div>
                    <div class="small opacity-75">by Cashiers</div>
                    <div class="small opacity-75">{{ formatCurrency($todayStats->cashier_total ?? 0) }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-dark text-white shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <i class="fas fa-coins fa-2x opacity-50"></i>
                <div>
                    <div class="fs-4 fw-bold">{{ formatCurrency(($todayStats->waiter_total ?? 0) + ($todayStats->cashier_total ?? 0)) }}</div>
                    <div class="small opacity-75">Today's Total {{ \App\Models\Setting::get('currency_symbol', 'UGX') }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

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
                <select class="form-select form-select-sm" id="roleFilter" style="max-width:140px;">
                    <option value="">All Processors</option>
                    <option value="waiter" {{ request('processed_by_role') === 'waiter' ? 'selected' : '' }}>Waiters Only</option>
                    <option value="cashier" {{ request('processed_by_role') === 'cashier' ? 'selected' : '' }}>Cashiers Only</option>
                </select>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Invoice #</th>
                        <th>Customer</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th>Paid</th>
                        <th>Payment</th>
                        <th>Processed By</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bills ?? [] as $bill)
                    <tr>
                        <td class="fw-medium">#{{ $bill->invoice_no ?? $bill->id }}</td>
                        <td>{{ $bill->customer->name ?? 'Walk-in' }}</td>
                        <td>{{ $bill->items_count ?? $bill->items->count() }}</td>
                        <td>{{ formatCurrency($bill->total) }}</td>
                        <td>{{ formatCurrency($bill->paid) }}</td>
                        <td>
                            @php
                                $methodIcons = ['cash' => 'fa-money-bill', 'mobile_money' => 'fa-mobile-screen', 'card' => 'fa-credit-card'];
                            @endphp
                            <span class="badge bg-info-subtle text-info">
                                <i class="fas {{ $methodIcons[$bill->payment_method] ?? 'fa-circle' }} me-1"></i>
                                {{ ucfirst(str_replace('_', ' ', $bill->payment_method ?? 'cash')) }}
                            </span>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-1">
                                <i class="fas fa-{{ $bill->processed_by_role === 'waiter' ? 'user-tie' : 'cash-register' }} text-muted me-1"></i>
                                <div>
                                    <div class="fw-medium">{{ $bill->processor_name }}</div>
                                    <small class="text-muted" x-show="$el.parentElement">
                                        @if($bill->processed_by_role === 'waiter' && $bill->waiter && $bill->waiter->employee_id)
                                            ID: {{ $bill->waiter->employee_id }}
                                        @endif
                                    </small>
                                </div>
                                <span class="badge {{ $bill->processor_badge_class }}">{{ $bill->processor_label }}</span>
                            </div>
                        </td>
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
                    <tr><td colspan="10" class="text-center text-muted py-4">No bills found</td></tr>
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
    document.getElementById('roleFilter')?.addEventListener('change', function() {
        const url = new URL(window.location.href);
        if (this.value) {
            url.searchParams.set('processed_by_role', this.value);
        } else {
            url.searchParams.delete('processed_by_role');
        }
        window.location.href = url.toString();
    });
</script>
@endpush
