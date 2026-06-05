<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Receipt #{{ $bill->bill_number ?? $bill->id }}</title>
    <style>
        @page { margin: 0; padding: 0; size: 80mm auto; }
        body { font-family: 'Courier New', Courier, monospace; font-size: 12px; width: 72mm; margin: 0 auto; padding: 5px; color: #000; }
        .header { text-align: center; margin-bottom: 10px; }
        .header h3 { margin: 0; font-size: 16px; }
        .header p { margin: 2px 0; font-size: 11px; }
        .divider { border-top: 1px dashed #000; margin: 5px 0; }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; font-size: 11px; padding: 3px 0; }
        td { padding: 2px 0; vertical-align: top; }
        .item-name { font-size: 11px; }
        .qty { text-align: center; }
        .price { text-align: right; }
        .total-row td { font-weight: bold; padding-top: 5px; }
        .grand-total { font-size: 14px; font-weight: bold; }
        .footer { text-align: center; margin-top: 10px; font-size: 10px; }
        .right { text-align: right; }
        .center { text-align: center; }
        @media print { body { margin: 0; padding: 2mm; } .no-print { display: none; } }
    </style>
</head>
<body>
    <div class="header">
        <h3>{{ $settings['site_name'] ?? config('app.name', 'MyBar') }}</h3>
        <p>{{ $settings['site_address'] ?? '' }}</p>
        <p>Tel: {{ $settings['site_phone'] ?? 'N/A' }}</p>
        <div class="divider"></div>
        <p><strong>RECEIPT #{{ $bill->bill_number ?? $bill->id }}</strong></p>
        <p>Date: {{ $bill->created_at->format('d M Y H:i') }}</p>
        <p>Cashier: {{ $bill->cashier->name ?? 'N/A' }}</p>
        <p>Customer: {{ $bill->customer->name ?? 'Walk-in' }}</p>
        <div class="divider"></div>
    </div>
    <table>
        <thead>
            <tr><th style="width:50%;">Item</th><th class="qty" style="width:15%;">Qty</th><th class="price" style="width:35%;">Amount</th></tr>
        </thead>
        <tbody>
            @foreach($bill->items as $item)
            <tr>
                <td class="item-name">{{ $item->product->name ?? 'N/A' }}</td>
                <td class="qty">{{ $item->quantity }}</td>
                <td class="price">{{ number_format($item->subtotal, 0) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="divider"></div>
    <table>
        <tr><td>Subtotal</td><td class="right">{{ number_format($bill->subtotal ?? $bill->total_amount, 0) }}</td></tr>
        @if($bill->discount > 0)
        <tr><td>Discount</td><td class="right">-{{ number_format($bill->discount, 0) }}</td></tr>
        @endif

        @if($bill->tax_amount > 0)
        <tr><td>{{ \App\Models\Setting::get('tax_label', 'VAT') }}</td><td class="right">{{ number_format($bill->tax_amount, 0) }}</td></tr>
        @endif

        @if($bill->service_charge > 0)
        <tr><td>Service Charge</td><td class="right">{{ number_format($bill->service_charge, 0) }}</td></tr>
        @endif
        <tr class="total-row"><td>Total</td><td class="right grand-total">{{ number_format($bill->total_amount, 0) }}</td></tr>
        <tr><td>Paid ({{ ucfirst($bill->payment_method ?? 'Cash') }})</td><td class="right">{{ number_format($bill->total_amount, 0) }}</td></tr>
    </table>
    <div class="divider"></div>
    <div class="footer">
        <p>Thank you for your patronage!</p>
        <p style="margin-top:8px;">Powered by MyBar POS</p>
        <div class="divider"></div>
        <button class="no-print" onclick="window.print()" style="padding:8px 20px;margin-top:10px;cursor:pointer;">Print</button>
    </div>
    <script>window.onload = function() { window.print(); }</script>
</body>
</html>
