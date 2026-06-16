@extends('layouts.app')
@section('title', 'Order #' . $order->order_number)
@section('page-title', 'Order #' . $order->order_number)

@section('breadcrumb-plugins')
    <a href="{{ route('orders.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i> Back</a>
    @if($order->status !== 'cancelled' && $order->status !== 'completed' && $order->status !== 'served')
    <button class="btn btn-primary btn-sm" onclick="document.getElementById('statusForm').submit()">
        <i class="fas fa-check me-1"></i> Mark {{ $order->status === 'pending' ? 'Confirmed' : ($order->status === 'confirmed' ? 'Preparing' : ($order->status === 'preparing' ? 'Ready' : ($order->status === 'ready' ? 'Served' : ''))) }}
    </button>
    @elseif($order->status === 'served')
    <a href="{{ route('pos.index', ['order_id' => $order->id]) }}" class="btn btn-success btn-sm">
        <i class="fas fa-credit-card me-1"></i> Process Payment via POS
    </a>
    @endif
@endsection

@section('content')
<div class="row g-3">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header"><i class="fas fa-clipboard-list me-2"></i>Order Items</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr><th>#</th><th>Product</th><th class="text-end">Price</th><th class="text-center">Qty</th><th class="text-end">Subtotal</th><th class="text-center">Status</th></tr>
                        </thead>
                        <tbody>
                            @forelse($order->items as $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $item->product->name ?? 'N/A' }}</td>
                                <td class="text-end">{{ formatCurrency($item->price) }}</td>
                                <td class="text-center">{{ $item->quantity }}</td>
                                <td class="text-end">{{ formatCurrency($item->subtotal) }}</td>
                                <td class="text-center">
                                    <span class="badge bg-{{ $item->status === 'completed' ? 'success' : ($item->status === 'preparing' ? 'info' : 'warning') }}">
                                        {{ ucfirst($item->status) }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="text-center text-muted py-3">No items</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header"><i class="fas fa-info-circle me-2"></i>Details</div>
            <div class="card-body">
                <div class="mb-2"><strong>Table:</strong> {{ $order->table->name ?? 'Takeaway' }}</div>
                <div class="mb-2"><strong>Waiter:</strong> {{ $order->waiter->name ?? 'N/A' }}</div>
                <div class="mb-2"><strong>Customer:</strong> {{ $order->customer->name ?? 'N/A' }}</div>
                <div class="mb-2"><strong>Status:</strong>
                    <span class="badge bg-{{ $order->status === 'pending' ? 'warning' : ($order->status === 'confirmed' ? 'info' : ($order->status === 'preparing' ? 'info' : ($order->status === 'ready' ? 'success' : ($order->status === 'served' ? 'primary' : ($order->status === 'completed' ? 'dark' : 'danger'))))) }}">
                        {{ ucfirst($order->status) }}
                    </span>
                </div>
                <div class="mb-2"><strong>Order Type:</strong> {{ ucfirst($order->order_type ?? 'Dine In') }}</div>
                <div class="mb-2"><strong>Date:</strong> {{ $order->created_at->format('d M Y H:i') }}</div>
                @if($order->notes)
                <div class="mb-2"><strong>Notes:</strong> {{ $order->notes }}</div>
                @endif
                <hr>
                <div class="mb-0"><strong>Total:</strong> <span class="fs-5 text-primary">{{ formatCurrency($order->items->sum('subtotal')) }}</span></div>
            </div>
        </div>
    </div>
</div>

<form id="statusForm" method="POST" action="{{ route('orders.status', $order->id) }}" class="d-none">
    @csrf @method('PUT')
    <input type="hidden" name="status" value="{{ $order->status === 'pending' ? 'confirmed' : ($order->status === 'confirmed' ? 'preparing' : ($order->status === 'preparing' ? 'ready' : ($order->status === 'ready' ? 'served' : 'served'))) }}">
</form>
@endsection
