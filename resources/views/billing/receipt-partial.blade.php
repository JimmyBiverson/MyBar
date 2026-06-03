<style>
    .receipt-box { font-family: 'Courier New', Courier, monospace; font-size: 12px; color: #000; text-align: left; }
    .receipt-box .r-header { text-align: center; margin-bottom: 8px; }
    .receipt-box .r-header h3 { margin: 0; font-size: 15px; }
    .receipt-box .r-header p { margin: 2px 0; font-size: 11px; color: #555; }
    .receipt-box .r-divider { border-top: 1px dashed #ccc; margin: 5px 0; }
    .receipt-box table { width: 100%; border-collapse: collapse; }
    .receipt-box th { text-align: left; font-size: 11px; padding: 3px 0; border-bottom: 1px dashed #ccc; }
    .receipt-box td { padding: 3px 0; vertical-align: top; font-size: 11px; }
    .receipt-box .r-qty { text-align: center; }
    .receipt-box .r-price { text-align: right; }
    .receipt-box .r-total-row td { font-weight: bold; padding-top: 6px; font-size: 12px; }
    .receipt-box .r-grand-total { font-size: 14px; font-weight: bold; }
    .receipt-box .r-footer { text-align: center; margin-top: 8px; font-size: 10px; color: #666; }
    .receipt-box .r-label { color: #555; }
</style>
<div class="receipt-box">
    <div class="r-header">
        <h3>{{ $settings['site_name'] ?? config('app.name', 'MyBar') }}</h3>
        <p>{{ $settings['site_address'] ?? '' }}</p>
        <p>Tel: {{ $settings['site_phone'] ?? 'N/A' }}</p>
        <div class="r-divider"></div>
        <p><strong>RECEIPT #{{ $bill->bill_number ?? $bill->id }}</strong></p>
        <p>Date: {{ $bill->created_at->format('d M Y H:i') }}</p>
        <p>Cashier: {{ $bill->cashier->name ?? $bill->waiter->name ?? 'N/A' }}</p>
        <p><span class="badge bg-{{ $bill->processed_by_role === 'waiter' ? 'info' : 'secondary' }}">{{ ucfirst($bill->processed_by_role ?? 'cashier') }}</span></p>
        <p>Customer: {{ $bill->customer->name ?? 'Walk-in' }}</p>
        <div class="r-divider"></div>
    </div>
    <table>
        <thead>
            <tr><th style="width:50%;">Item</th><th class="r-qty" style="width:15%;">Qty</th><th class="r-price" style="width:35%;">Amount</th></tr>
        </thead>
        <tbody>
            @foreach($bill->items as $item)
            <tr>
                <td>{{ $item->product->name ?? 'N/A' }}</td>
                <td class="r-qty">{{ $item->quantity }}</td>
                <td class="r-price">{{ number_format($item->subtotal, 0) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="r-divider"></div>
    <table>
        <tr><td class="r-label">Subtotal</td><td class="r-price">{{ number_format($bill->subtotal ?? $bill->total_amount, 0) }}</td></tr>
        @if($bill->discount > 0)
        <tr><td class="r-label">Discount</td><td class="r-price">-{{ number_format($bill->discount, 0) }}</td></tr>
        @endif
        @if($bill->tax_amount > 0)
        <tr><td class="r-label">Tax</td><td class="r-price">{{ number_format($bill->tax_amount, 0) }}</td></tr>
        @endif
        @if($bill->service_charge > 0)
        <tr><td class="r-label">Service Charge</td><td class="r-price">{{ number_format($bill->service_charge, 0) }}</td></tr>
        @endif
        <tr class="r-total-row"><td>Total</td><td class="r-price r-grand-total">{{ number_format($bill->total_amount, 0) }}</td></tr>
        <tr><td class="r-label">Paid ({{ ucfirst(str_replace('_', ' ', $bill->payment_method ?? 'Cash')) }})</td><td class="r-price">{{ number_format($bill->paid_amount ?? $bill->total_amount, 0) }}</td></tr>
        @if($bill->change_amount > 0)
        <tr><td class="r-label">Change</td><td class="r-price">{{ number_format($bill->change_amount, 0) }}</td></tr>
        @endif
    </table>
    <div class="r-divider"></div>
    <div class="r-footer">
        <p>Thank you for your patronage!</p>
        <p style="margin-top:4px;">Powered by MyBar POS</p>
    </div>
</div>
