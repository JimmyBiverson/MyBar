<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Product Performance Report</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .header h2 { margin: 0; font-size: 18px; }
        .header p { margin: 4px 0 0; color: #666; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th { background: #f5f5f5; text-align: left; padding: 5px 6px; border-bottom: 2px solid #ddd; font-size: 10px; }
        td { padding: 4px 6px; border-bottom: 1px solid #eee; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-success { color: #2e7d32; }
        .text-danger { color: #c62828; }
        .fw-bold { font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h2>{{ $company ?? config('app.name', 'MyBar') }}</h2>
        <p>Product Performance Report</p>
        <p>{{ $report_period ?? '' }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Product</th>
                <th>Category</th>
                <th class="text-right">Qty Sold</th>
                <th class="text-right">Revenue</th>
                <th class="text-right">Cost</th>
                <th class="text-right">Profit</th>
                <th class="text-right">Margin</th>
            </tr>
        </thead>
        <tbody>
            @forelse($products ?? [] as $key => $item)
            @php $prod = $item->product ?? null; @endphp
            <tr>
                <td>{{ $key + 1 }}</td>
                <td>{{ $prod->name ?? 'N/A' }}</td>
                <td>{{ $prod->category->name ?? 'N/A' }}</td>
                <td class="text-right">{{ (int) ($item->total_qty ?? 0) }}</td>
                <td class="text-right">{{ formatCurrency((float) ($item->total_revenue ?? 0)) }}</td>
                <td class="text-right">{{ formatCurrency((float) ($item->total_cost ?? 0)) }}</td>
                <td class="text-right" style="color: {{ ($item->total_profit ?? 0) >= 0 ? '#2e7d32' : '#c62828' }}">{{ formatCurrency((float) ($item->total_profit ?? 0)) }}</td>
                <td class="text-right">{{ $item->margin ?? 0 }}%</td>
            </tr>
            @empty
            <tr><td colspan="8" style="text-align:center;color:#999;padding:20px;">No product data for this period</td></tr>
            @endforelse
        </tbody>
    </table>

    <p style="text-align:center;color:#999;font-size:10px;margin-top:20px;">Generated on {{ $generated_at ?? now()->format('d M Y H:i') }}</p>
</body>
</html>
