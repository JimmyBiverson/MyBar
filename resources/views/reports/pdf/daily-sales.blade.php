<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Daily Sales Report</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .header h2 { margin: 0; font-size: 18px; }
        .header p { margin: 4px 0 0; color: #666; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th { background: #f5f5f5; text-align: left; padding: 6px 8px; border-bottom: 2px solid #ddd; }
        td { padding: 5px 8px; border-bottom: 1px solid #eee; }
        .text-right { text-align: right; }
        .summary { margin-top: 10px; }
        .summary td { border: none; padding: 3px 8px; }
        .summary .total { font-size: 14px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h2>{{ $company ?? config('app.name', 'MyBar') }}</h2>
        <p>Daily Sales Report - {{ $report_date ?? ($date ? $date->format('d M Y') : '') }}</p>
    </div>

    <table class="summary" style="width:auto;margin:0 auto 15px;">
        <tr><td><strong>Total Sales:</strong></td><td class="text-right">{{ formatCurrency($total_sales ?? 0) }}</td></tr>
        <tr><td><strong>Total Expenses:</strong></td><td class="text-right">{{ formatCurrency($total_expenses ?? 0) }}</td></tr>
        <tr><td><strong>Net Profit / Loss:</strong></td><td class="text-right" style="color: {{ ($net_profit ?? 0) >= 0 ? '#2e7d32' : '#c62828' }}">{{ formatCurrency($net_profit ?? 0) }}</td></tr>
        <tr><td><strong>Orders Count:</strong></td><td class="text-right">{{ $total_transactions ?? 0 }}</td></tr>
        <tr><td><strong>Avg per Transaction:</strong></td><td class="text-right">{{ formatCurrency($average_per_transaction ?? 0) }}</td></tr>
    </table>

    @if(count($payment_methods ?? []) > 0)
    <h4>Payment Methods</h4>
    <table>
        <thead>
            <tr><th>Method</th><th class="text-right">Count</th><th class="text-right">Total</th></tr>
        </thead>
        <tbody>
            @foreach($payment_methods ?? [] as $pm)
            <tr>
                <td>{{ ucfirst($pm->payment_method) }}</td>
                <td class="text-right">{{ $pm->count }}</td>
                <td class="text-right">{{ formatCurrency((float) $pm->total) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    @if(count($bills ?? []) > 0)
    <h4>Transactions</h4>
    <table>
        <thead>
            <tr><th>#</th><th>Invoice</th><th>Customer</th><th>Items</th><th class="text-right">Total</th><th class="text-right">Paid</th></tr>
        </thead>
        <tbody>
            @foreach($bills as $i => $bill)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $bill->invoice_no ?? $bill->id }}</td>
                <td>{{ $bill->customer->name ?? 'Walk-in' }}</td>
                <td>{{ $bill->items_count }}</td>
                <td class="text-right">{{ formatCurrency((float) $bill->total_amount) }}</td>
                <td class="text-right">{{ formatCurrency((float) $bill->paid_amount) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <p style="text-align:center;color:#999;font-size:10px;margin-top:20px;">Generated on {{ $generated_at ?? now()->format('d M Y H:i') }}</p>
</body>
</html>
