<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice #{{ $bill->bill_number ?? $bill->id }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .header h2 { margin: 0; font-size: 20px; }
        .header p { margin: 2px 0; color: #666; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th { background: #f5f5f5; text-align: left; padding: 8px; border-bottom: 2px solid #ddd; }
        td { padding: 6px 8px; border-bottom: 1px solid #eee; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .summary { margin-top: 20px; }
        .summary td { border: none; padding: 4px 8px; }
        .summary .total { font-size: 16px; font-weight: bold; }
        .footer { text-align: center; margin-top: 30px; color: #999; font-size: 10px; border-top: 1px solid #ddd; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>{{ $settings['site_name'] ?? config('app.name', 'MyBar') }}</h2>
        <p>{{ $settings['site_address'] ?? '' }}</p>
        <p>Tel: {{ $settings['site_phone'] ?? 'N/A' }}</p>
    </div>
    <h3>INVOICE #{{ $bill->bill_number ?? $bill->id }}</h3>
    <p><strong>Date:</strong> {{ $bill->created_at->format('d M Y H:i') }}</p>
    <p><strong>Customer:</strong> {{ $bill->customer->name ?? 'Walk-in' }}</p>
    <p><strong>Processed By:</strong> {{ $bill->waiter_identification ?? ($bill->cashier->name ?? 'N/A') }} ({{ ucfirst($bill->processed_by_role ?? 'cashier') }})</p>
    <table>
        <thead>
            <tr><th>Item</th><th class="text-center">Qty</th><th class="text-right">Price</th><th class="text-right">Total</th></tr>
        </thead>
        <tbody>
            @foreach($bill->items as $item)
            <tr>
                <td>{{ $item->product->name ?? 'N/A' }}</td>
                <td class="text-center">{{ $item->quantity }}</td>
                <td class="text-right">{{ number_format($item->price, 0) }}</td>
                <td class="text-right">{{ number_format($item->subtotal, 0) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <table class="summary">
        <tr><td><strong>Subtotal</strong></td><td class="text-right">{{ number_format($bill->subtotal ?? $bill->total_amount, 0) }}</td></tr>
        @if($bill->discount > 0)
        <tr><td>Discount</td><td class="text-right">-{{ number_format($bill->discount, 0) }}</td></tr>
        @endif

        @if($bill->tax_amount > 0)
        <tr><td>{{ \App\Models\Setting::get('tax_label', 'VAT') }}</td><td class="text-right">{{ number_format($bill->tax_amount, 0) }}</td></tr>
        @endif

        @if($bill->service_charge > 0)
        <tr><td>Service Charge</td><td class="text-right">{{ number_format($bill->service_charge, 0) }}</td></tr>
        @endif
        <tr class="total"><td><strong>Total</strong></td><td class="text-right"><strong>{{ number_format($bill->total_amount, 0) }}</strong></td></tr>
    </table>
    <div class="footer">
        <p>Thank you for your business!</p>
        <p>Powered by MyBar POS</p>
    </div>
</body>
</html>
