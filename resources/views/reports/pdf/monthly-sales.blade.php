<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Monthly Sales Report</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .header h2 { margin: 0; font-size: 18px; }
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
        <h2>{{ config('app.name', 'MyBar') }}</h2>
        <p>Monthly Sales Report - {{ $month->format('F Y') }}</p>
    </div>
    <p><strong>Total Sales:</strong> {{ number_format($sales, 0) }}</p>
    <p><strong>Total Expenses:</strong> {{ number_format($expenses, 0) }}</p>
</body>
</html>
