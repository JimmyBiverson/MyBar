@extends('layouts.app')
@section('title', 'Bill #' . ($bill->invoice_no ?? $bill->id))
@section('page-title', 'Bill #' . ($bill->invoice_no ?? $bill->id))

@section('breadcrumb-plugins')
    <a href="{{ route('billing.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i> Back</a>
    <a href="{{ route('billing.print', $bill->id) }}" class="btn btn-primary btn-sm" target="_blank"><i class="fas fa-print me-1"></i> Print</a>
    @if($bill->status === 'completed')
    <a href="{{ route('billing.pdf', $bill->id) }}" class="btn btn-outline-primary btn-sm"><i class="fas fa-file-pdf me-1"></i> PDF</a>
    @endif
@endsection

@section('content')
<div class="row g-3">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header"><i class="fas fa-receipt me-2"></i>Invoice Items</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr><th>#</th><th>Product</th><th class="text-end">Price</th><th class="text-center">Qty</th><th class="text-end">Subtotal</th></tr>
                        </thead>
                        <tbody>
                            @forelse($bill->items as $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $item->product->name ?? $item->product_name ?? 'N/A' }}</td>
                                <td class="text-end">{{ number_format($item->price, 0) }}</td>
                                <td class="text-center">{{ $item->qty }}</td>
                                <td class="text-end">{{ number_format($item->subtotal ?? ($item->price * $item->qty), 0) }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center text-muted py-3">No items</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-header"><i class="fas fa-info-circle me-2"></i>Payment Info</div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr><td>Subtotal</td><td class="text-end">{{ number_format($bill->subtotal ?? $bill->total, 0) }}</td></tr>
                    @if($bill->discount > 0)
                    <tr><td>Discount</td><td class="text-end text-danger">-{{ number_format($bill->discount, 0) }}</td></tr>
                    @endif

                    @if($bill->tax_amount > 0)
                    <tr><td>{{ \App\Models\Setting::get('tax_label', 'VAT') }}</td><td class="text-end">{{ number_format($bill->tax_amount, 0) }}</td></tr>
                    @endif

                    @if($bill->service_charge > 0)
                    <tr><td>Service Charge</td><td class="text-end">{{ number_format($bill->service_charge, 0) }}</td></tr>
                    @endif
                    <tr class="fw-bold"><td>Total</td><td class="text-end text-primary fs-5">{{ number_format($bill->total, 0) }}</td></tr>
                    <tr><td>Paid</td><td class="text-end text-success">{{ number_format($bill->paid, 0) }}</td></tr>
                    @if($bill->balance > 0)
                    <tr><td>Balance</td><td class="text-end text-danger">{{ number_format($bill->balance, 0) }}</td></tr>
                    @endif
                </table>
                <hr>
                <div class="mb-2"><strong>Payment Method:</strong>
                    <span class="badge bg-info-subtle text-info">
                        <i class="fas {{ $bill->payment_method === 'cash' ? 'fa-money-bill' : ($bill->payment_method === 'mobile_money' ? 'fa-mobile-screen' : 'fa-credit-card') }} me-1"></i>
                        {{ ucfirst(str_replace('_', ' ', $bill->payment_method ?? 'Cash')) }}
                    </span>
                </div>
                <div class="mb-2"><strong>Status:</strong>
                    <span class="badge bg-{{ $bill->status === 'completed' ? 'success' : ($bill->status === 'pending' ? 'warning' : 'danger') }}">
                        {{ ucfirst($bill->status) }}
                    </span>
                </div>
                <div class="mb-2"><strong>Customer:</strong> {{ $bill->customer->name ?? 'Walk-in' }}</div>
                <div class="mb-0"><strong>Date:</strong> {{ $bill->created_at->format('d M Y H:i:s') }}</div>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><i class="fas fa-user-cog me-2"></i>Processor</div>
            <div class="card-body">
                <div class="d-flex align-items-center gap-3">
                    <div class="d-flex align-items-center justify-content-center rounded-circle {{ $bill->processor_badge_class }} text-white" style="width:48px;height:48px;font-size:1.3rem;">
                        <i class="fas fa-{{ $bill->processed_by_role === 'waiter' ? 'user-tie' : 'cash-register' }}"></i>
                    </div>
                    <div>
                        <div class="fw-semibold fs-6">{{ $bill->processor_name }}</div>
                        <span class="badge {{ $bill->processor_badge_class }}">{{ $bill->processor_label }}</span>
                    </div>
                </div>
                @if($bill->processed_by_role === 'waiter' && $bill->waiter)
                <div class="mt-3 pt-2 border-top">
                    <div class="small text-muted mb-2"><i class="fas fa-identification-badge me-1"></i> Waiter ID: <strong>{{ $bill->waiter->employee_id ?? 'N/A' }}</strong></div>
                    <div class="small text-muted"><i class="fas fa-user me-1"></i> Name: <strong>{{ $bill->waiter->name }}</strong></div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
