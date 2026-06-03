<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Inventory Report</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .header h2 { margin: 0; font-size: 18px; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th { background: #f5f5f5; text-align: left; padding: 6px 8px; border-bottom: 2px solid #ddd; }
        td { padding: 5px 8px; border-bottom: 1px solid #eee; }
        .text-right { text-align: right; }
    </style>
</head>
<body>
    <div class="header">
        <h2>{{ config('app.name', 'MyBar') }}</h2>
        <p>Inventory Report</p>
    </div>
    <table>
        <thead>
            <tr><th>Product</th><th>Category</th><th class="text-right">Stock</th><th>Status</th><th class="text-right">Cost Price</th><th class="text-right">Stock Value</th></tr>
        </thead>
        <tbody>
            @foreach($products ?? [] as $product)
            <tr>
                <td>{{ $product->name }}</td>
                <td>{{ $product->category->name ?? 'N/A' }}</td>
                <td class="text-right">{{ $product->current_stock }}</td>
                <td>{{ ucfirst($product->stock_status) }}</td>
                <td class="text-right">{{ number_format($product->cost_price, 0) }}</td>
                <td class="text-right">{{ number_format($product->current_stock * $product->cost_price, 0) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
