<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Profit & Loss Report</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .header h2 { margin: 0; font-size: 18px; }
        .header p { margin: 4px 0 0; color: #666; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th { background: #f5f5f5; text-align: left; padding: 6px 8px; border-bottom: 2px solid #ddd; }
        td { padding: 5px 8px; border-bottom: 1px solid #eee; }
        .text-right { text-align: right; }
        .text-success { color: #2e7d32; }
        .text-danger { color: #c62828; }
        .fw-bold { font-weight: bold; }
        .summary-table td { border: none; padding: 4px 8px; }
        .total-row { font-weight: bold; font-size: 13px; border-top: 2px solid #333; }
        .section-title { font-size: 14px; font-weight: bold; margin: 15px 0 5px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>{{ $company ?? config('app.name', 'MyBar') }}</h2>
        <p>Profit & Loss Report</p>
        <p>{{ $report_period ?? ($startDate ?? '') . ' - ' . ($endDate ?? '') }}</p>
    </div>

    <table class="summary-table">
        <tr><td><strong>Total Revenue</strong></td><td class="text-right text-success">UGX {{ number_format($total_revenue ?? 0, 0) }}</td></tr>
        <tr><td><strong>Cost of Goods Sold</strong></td><td class="text-right text-danger">UGX {{ number_format(($total_revenue ?? 0) - ($gross_profit ?? $net_profit ?? 0) - ($total_expenses ?? 0), 0) }}</td></tr>
        <tr class="total-row"><td>Gross Profit</td><td class="text-right">UGX {{ number_format(($total_revenue ?? 0) - (($total_revenue ?? 0) - ($gross_profit ?? $net_profit ?? 0) - ($total_expenses ?? 0)), 0) }}</td></tr>
        <tr><td><strong>Total Expenses</strong></td><td class="text-right text-danger">UGX {{ number_format($total_expenses ?? 0, 0) }}</td></tr>
        <tr class="total-row"><td>Net Profit / Loss</td><td class="text-right" style="color: {{ ($net_profit ?? 0) >= 0 ? '#2e7d32' : '#c62828' }}">UGX {{ number_format($net_profit ?? 0, 0) }}</td></tr>
    </table>

    <div class="section-title">Revenue Breakdown</div>
    <table>
        <thead>
            <tr><th>Source</th><th class="text-right">Amount</th><th class="text-right">%</th></tr>
        </thead>
        <tbody>
            <tr><td>Product Sales</td><td class="text-right">UGX {{ number_format($product_sales ?? 0, 0) }}</td><td class="text-right">{{ $product_sales_percent ?? 0 }}%</td></tr>
            <tr><td>Service Charges</td><td class="text-right">UGX {{ number_format($service_charges ?? 0, 0) }}</td><td class="text-right">{{ $service_charges_percent ?? 0 }}%</td></tr>
            <tr style="font-weight:bold"><td>Total Revenue</td><td class="text-right">UGX {{ number_format($total_revenue ?? 0, 0) }}</td><td class="text-right">100%</td></tr>
        </tbody>
    </table>

    <div class="section-title">Expense Breakdown</div>
    <table>
        <thead>
            <tr><th>Category</th><th class="text-right">Amount</th></tr>
        </thead>
        <tbody>
            @forelse($expense_categories ?? [] as $cat)
            <tr>
                <td>{{ $cat['name'] ?? 'N/A' }}</td>
                <td class="text-right">UGX {{ number_format($cat['total'] ?? 0, 0) }}</td>
            </tr>
            @empty
            <tr><td colspan="2" style="text-align:center;color:#999">No expenses recorded</td></tr>
            @endforelse
            <tr style="font-weight:bold"><td>Total Expenses</td><td class="text-right">UGX {{ number_format($total_expenses ?? 0, 0) }}</td></tr>
        </tbody>
    </table>

    <p style="text-align:center;color:#999;font-size:10px;margin-top:20px;">Generated on {{ $generated_at ?? now()->format('d M Y H:i') }}</p>
</body>
</html>
