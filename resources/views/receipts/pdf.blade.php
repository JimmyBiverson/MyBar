<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Receipt #{{ $bill->invoice_no ?? $bill->id }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; color: #333; padding: 20px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #7367f0; padding-bottom: 15px; }
        .header img { max-height: 70px; }
        .header h2 { margin: 5px 0; color: #7367f0; }
        .header p { margin: 2px 0; color: #666; font-size: 11px; }
        .invoice-info { margin-bottom: 20px; }
        .invoice-info table { width: 100%; }
        .invoice-info td { padding: 2px 0; }
        .invoice-info .right { text-align: right; }
        table.items { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table.items th { background: #7367f0; color: #fff; padding: 8px 10px; text-align: left; font-size: 11px; }
        table.items td { padding: 6px 10px; border-bottom: 1px solid #eee; }
        table.items tr:nth-child(even) td { background: #f8f9fa; }
        .right { text-align: right; }
        .center { text-align: center; }
        .totals { margin-left: auto; width: 300px; }
        .totals table { width: 100%; }
        .totals td { padding: 4px 10px; }
        .totals .grand-total td { font-size: 16px; font-weight: bold; color: #7367f0; border-top: 2px solid #7367f0; padding-top: 8px; }
        .footer { text-align: center; margin-top: 30px; padding-top: 15px; border-top: 1px solid #ddd; font-size: 10px; color: #999; }
        .amount-word { font-size: 11px; color: #666; margin: 10px 0; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 10px; font-weight: bold; }
        .badge-success { background: #d4edda; color: #155724; }
    </style>
</head>
<body>
    <div class="header">
        @if($business_logo ?? false)
            <img src="{{ $business_logo }}" alt="Logo">
        @else
            <i class="fas fa-glass-cheers" style="font-size:40px;color:#7367f0;"></i>
        @endif
        <h2>{{ config('app.name', 'MyBar') }}</h2>
        <p>{{ $business_address ?? 'Kampala, Uganda' }}</p>
        <p>Tel: {{ $business_phone ?? 'N/A' }} | Email: {{ $business_email ?? 'N/A' }}</p>
        <p>Tax ID: {{ $business_tin ?? 'N/A' }}</p>
    </div>

    <div class="invoice-info">
        <table>
            <tr>
                <td><strong>Invoice #:</strong> {{ $bill->invoice_no ?? $bill->id }}</td>
                <td class="right"><strong>Date:</strong> {{ $bill->created_at->format('d M Y H:i') }}</td>
            </tr>
            <tr>
                <td><strong>Customer:</strong> {{ $bill->customer->name ?? 'Walk-in Customer' }}</td>
                <td class="right"><strong>Cashier:</strong> {{ $bill->user->name ?? $bill->cashier->name ?? 'N/A' }}</td>
            </tr>
        </table>
    </div>

    <table class="items">
        <thead>
            <tr>
                <th style="width:5%;">#</th>
                <th style="width:45%;">Product</th>
                <th style="width:15%;" class="right">Price</th>
                <th style="width:10%;" class="center">Qty</th>
                <th style="width:25%;" class="right">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($bill->items as $item)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $item->product->name ?? $item->product_name ?? 'N/A' }}</td>
                <td class="right">{{ number_format($item->price, 0) }}</td>
                <td class="center">{{ $item->qty }}</td>
                <td class="right">{{ number_format($item->subtotal ?? ($item->price * $item->qty), 0) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals">
        <table>
            <tr><td>Subtotal:</td><td class="right">{{ number_format($bill->subtotal ?? $bill->total, 0) }}</td></tr>
            @if($bill->discount > 0)
            <tr><td>Discount:</td><td class="right">-{{ number_format($bill->discount, 0) }}</td></tr>
            @endif
            @if($bill->tax > 0)
            <tr><td>Tax ({{ $bill->tax_rate ?? 0 }}%):</td><td class="right">{{ number_format($bill->tax, 0) }}</td></tr>
            @endif
            @if($bill->service_charge > 0)
            <tr><td>Service Charge:</td><td class="right">{{ number_format($bill->service_charge, 0) }}</td></tr>
            @endif
            <tr class="grand-total"><td>Total:</td><td class="right">{{ number_format($bill->total, 0) }}</td></tr>
            <tr><td>Paid ({{ ucfirst($bill->payment_method ?? 'Cash') }}):</td><td class="right">{{ number_format($bill->paid, 0) }}</td></tr>
            @if($bill->balance > 0)
            <tr><td>Balance:</td><td class="right">{{ number_format($bill->balance, 0) }}</td></tr>
            @endif
        </table>
    </div>

    <div class="amount-word">
        Amount in words: {{ number_to_words($bill->total) ?? number_format($bill->total, 0) }}
    </div>

    <div class="footer">
        <p>Thank you for your patronage!</p>
        <p>{{ config('app.name', 'MyBar') }} - Point of Sale System</p>
        <p>{{ $bill->created_at->format('Y-m-d H:i:s') }}</p>
    </div>
</body>
</html>
