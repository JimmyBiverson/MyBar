<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Inventory Report</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .header h2 { margin: 0; font-size: 18px; }
        .header p { margin: 4px 0 0; color: #666; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th { background: #f5f5f5; text-align: left; padding: 5px 6px; border-bottom: 2px solid #ddd; }
        td { padding: 4px 6px; border-bottom: 1px solid #eee; }
        .text-right { text-align: right; }
        .summary { margin-top: 10px; }
        .summary td { border: none; padding: 3px 8px; }
        .summary .total { font-size: 14px; font-weight: bold; }
        .low { color: #c62828; }
        .medium { color: #f57f17; }
        .good { color: #2e7d32; }
        .total-row { font-weight: bold; border-top: 2px solid #333; }
    </style>
</head>
<body>
    <div class="header">
        <h2>{{ $company ?? config('app.name', 'MyBar') }}</h2>
        <p>Inventory Report</p>
        <p>{{ $generated_at ?? now()->format('d M Y H:i') }}</p>
    </div>

    <table class="summary" style="width:auto;margin:0 auto 15px;">
        <tr><td><strong>Total Products:</strong></td><td class="text-right">{{ count($products ?? []) }}</td></tr>
        <tr><td><strong>Low Stock Items:</strong></td><td class="text-right">{{ collect($products)->filter(fn($p) => ($p->stock_status ?? '') === 'low')->count() }}</td></tr>
    </table>

    <table>
        <thead>
            <tr><th>Product</th><th>Category</th><th class="text-right">Stock</th><th>Status</th><th class="text-right">Cost Price</th><th class="text-right">Selling Price</th><th class="text-right">Stock Value</th></tr>
        </thead>
        <tbody>
            @foreach($products ?? [] as $product)
            <tr>
                <td>{{ $product->name }}</td>
                <td>{{ $product->category->name ?? 'N/A' }}</td>
                <td class="text-right">{{ $product->current_stock }}</td>
                <td class="{{ $product->stock_status ?? '' }}">{{ ucfirst($product->stock_status ?? 'unknown') }}</td>
                <td class="text-right">UGX {{ number_format((float) $product->cost_price, 0) }}</td>
                <td class="text-right">UGX {{ number_format((float) $product->selling_price, 0) }}</td>
                <td class="text-right">UGX {{ number_format((float) $product->current_stock * (float) $product->cost_price, 0) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="6" class="text-right">TOTAL STOCK VALUE</td>
                <td class="text-right">UGX {{ number_format(collect($products)->sum(fn($p) => (float) $p->current_stock * (float) $p->cost_price), 0) }}</td>
            </tr>
        </tfoot>
    </table>

    <p style="text-align:center;color:#999;font-size:10px;margin-top:20px;">{{ config('app.name', 'MyBar') }} - Inventory Report</p>
</body>
</html>
