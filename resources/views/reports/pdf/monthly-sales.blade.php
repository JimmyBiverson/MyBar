<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Monthly Sales Report</title>
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
        .total-row { font-weight: bold; border-top: 2px solid #333; }
    </style>
</head>
<body>
    <div class="header">
        <h2>{{ $company ?? config('app.name', 'MyBar') }}</h2>
        <p>Monthly Sales Report - {{ $month ? $month->format('F Y') : '' }}</p>
    </div>

    <table class="summary" style="width:auto;margin:0 auto 15px;">
        <tr><td><strong>Total Sales:</strong></td><td class="text-right">UGX {{ number_format($sales ?? 0, 0) }}</td></tr>
        <tr><td><strong>Total Expenses:</strong></td><td class="text-right">UGX {{ number_format($expenses ?? 0, 0) }}</td></tr>
        <tr><td><strong>Net:</strong></td><td class="text-right">UGX {{ number_format(($sales ?? 0) - ($expenses ?? 0), 0) }}</td></tr>
    </table>

    @if(count($dailyData ?? []) > 0)
    <h4>Daily Breakdown</h4>
    <table>
        <thead>
            <tr><th>Date</th><th class="text-right">Sales</th></tr>
        </thead>
        <tbody>
            @foreach($dailyData as $d)
            <tr>
                <td>{{ $d->date }}</td>
                <td class="text-right">UGX {{ number_format((float) ($d->total_sum ?? $d->total ?? 0), 0) }}</td>
            </tr>
            @endforeach
            <tr class="total-row">
                <td>TOTAL</td>
                <td class="text-right">UGX {{ number_format($dailyData->sum(fn($r) => $r->total_sum ?? $r->total ?? 0), 0) }}</td>
            </tr>
        </tbody>
    </table>
    @endif

    <p style="text-align:center;color:#999;font-size:10px;margin-top:20px;">Generated on {{ $generated_at ?? now()->format('d M Y H:i') }}</p>
</body>
</html>
