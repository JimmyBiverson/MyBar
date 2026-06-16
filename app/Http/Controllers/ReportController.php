<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\BillItem;
use App\Models\Expense;
use App\Models\Product;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->type;
        $data = [];
        $dateFrom = $request->from ? Carbon::parse($request->from) : now()->startOfMonth();
        $dateTo = $request->to ? Carbon::parse($request->to) : now();
        $branchId = auth()->user()->branch_id;

        if ($type) {
            switch ($type) {
                case 'daily-sales':
                    $data = $this->dailySalesData($dateFrom, $branchId);
                    break;
                case 'monthly-sales':
                    $data = $this->monthlySalesData($dateFrom, $branchId);
                    break;
                case 'profit-loss':
                    $data = $this->profitLossData($dateFrom, $dateTo, $branchId);
                    break;
                case 'inventory':
                    $data = $this->inventoryData($branchId);
                    break;
                case 'product-performance':
                    $data = $this->productPerformanceData($dateFrom, $dateTo, $branchId);
                    break;
            }
        }

        return view('reports.index', compact('type', 'data', 'dateFrom', 'dateTo'));
    }

    private function dailySalesData(Carbon $date, $branchId): array
    {
        $query = Bill::where('payment_status', 'paid')->whereDate('created_at', $date);
        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $totalSales = (float) $query->sum('total_amount');
        $ordersCount = (int) $query->count();

        $billsQuery = Bill::with(['customer', 'items'])
            ->where('payment_status', 'paid')
            ->whereDate('created_at', $date);
        if ($branchId) {
            $billsQuery->where('branch_id', $branchId);
        }
        $bills = $billsQuery->orderBy('created_at', 'desc')->get();

        $paymentMethods = Bill::where('payment_status', 'paid')
            ->whereDate('created_at', $date)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->select('payment_method', DB::raw('COUNT(*) as count'), DB::raw('SUM(total_amount) as total'))
            ->groupBy('payment_method')
            ->get();

        $totalExpenses = (float) Expense::whereDate('expense_date', $date)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->sum('amount');

        return [
            'total_sales' => $totalSales,
            'total_expenses' => $totalExpenses,
            'net_profit' => $totalSales - $totalExpenses,
            'total_transactions' => $ordersCount,
            'average_per_transaction' => $ordersCount > 0 ? round($totalSales / $ordersCount) : 0,
            'total_discounts' => 0,
            'bills' => $bills,
            'payment_methods' => $paymentMethods,
            'report_date' => $date->format('d M Y'),
        ];
    }

    private function monthlySalesData(Carbon $month, $branchId): array
    {
        $startOfMonth = $month->copy()->startOfMonth();
        $endOfMonth = $month->copy()->endOfMonth();

        $sales = (float) Bill::where('payment_status', 'paid')
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->sum('total_amount');

        $transactions = Bill::where('payment_status', 'paid')
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->count();

        $dailyData = Bill::where('payment_status', 'paid')
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total_amount) as total_sum'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $totalExpenses = (float) Expense::whereBetween('expense_date', [$startOfMonth, $endOfMonth])
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->sum('amount');

        $daysInMonth = now()->month === $month->month && now()->year === $month->year
            ? now()->day
            : $month->daysInMonth;

        return [
            'total_sales' => $sales,
            'total_expenses' => $totalExpenses,
            'net_profit' => $sales - $totalExpenses,
            'total_transactions' => $transactions,
            'average_daily' => $daysInMonth > 0 ? round($sales / $daysInMonth) : 0,
            'monthly_data' => $dailyData,
            'report_month' => $month->format('F Y'),
        ];
    }

    private function profitLossData(Carbon $startDate, Carbon $endDate, $branchId): array
    {
        $billQuery = Bill::where('payment_status', 'paid')
            ->whereBetween('created_at', [$startDate, $endDate]);
        if ($branchId) {
            $billQuery->where('branch_id', $branchId);
        }
        $totalSales = (float) $billQuery->sum('total_amount');

        $expenseQuery = Expense::whereBetween('expense_date', [$startDate, $endDate]);
        if ($branchId) {
            $expenseQuery->where('branch_id', $branchId);
        }
        $totalExpenses = (float) $expenseQuery->sum('amount');

        $cogsQuery = BillItem::whereHas('bill', fn ($q) => $q->where('payment_status', 'paid')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->when($branchId, fn ($bq) => $bq->where('branch_id', $branchId)))
            ->sum(DB::raw('bill_items.quantity * (SELECT cost_price FROM products WHERE products.id = bill_items.product_id)'));

        $grossProfit = $totalSales - (float) $cogsQuery;
        $netProfit = $grossProfit - $totalExpenses;

        $expenseCategories = Expense::whereBetween('expense_date', [$startDate, $endDate])
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->select('category', DB::raw('SUM(amount) as total'))
            ->groupBy('category')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($e) => ['name' => $e->category, 'total' => (float) $e->total])
            ->toArray();

        return [
            'total_revenue' => $totalSales,
            'total_expenses' => $totalExpenses,
            'net_profit' => $netProfit,
            'product_sales' => $totalSales,
            'service_charges' => 0,
            'product_sales_percent' => $totalSales > 0 ? 100 : 0,
            'service_charges_percent' => 0,
            'expense_categories' => $expenseCategories,
            'report_period' => $startDate->format('d M Y') . ' - ' . $endDate->format('d M Y'),
        ];
    }

    private function inventoryData($branchId): array
    {
        $products = Product::with(['category', 'unit'])
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->orderBy('name')
            ->get();

        $totalValue = $products->sum(fn ($p) => $p->current_stock * $p->cost_price);
        $lowStockCount = $products->filter(fn ($p) => $p->stock_status === 'low')->count();
        $avgCost = $products->avg('cost_price');

        return [
            'products' => $products,
            'total_products' => $products->count(),
            'total_value' => $totalValue,
            'low_stock' => $lowStockCount,
            'average_cost' => round($avgCost ?: 0),
        ];
    }

    private function productPerformanceData(Carbon $startDate, Carbon $endDate, $branchId): array
    {
        $items = BillItem::select(
            'bill_items.product_id',
            DB::raw('SUM(bill_items.quantity) as total_qty'),
            DB::raw('SUM(bill_items.subtotal) as total_revenue'),
            DB::raw('SUM(bill_items.quantity * (SELECT cost_price FROM products WHERE products.id = bill_items.product_id)) as total_cost')
        )
            ->whereHas('bill', fn ($q) => $q->where('payment_status', 'paid')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->when($branchId, fn ($bq) => $bq->where('branch_id', $branchId)))
            ->with('product.category', 'product.unit')
            ->groupBy('bill_items.product_id')
            ->orderByDesc('total_qty')
            ->get();

        $items->each(function ($item) {
            $cost = (float) ($item->total_cost ?? 0);
            $revenue = (float) ($item->total_revenue ?? 0);
            $item->total_cost = $cost;
            $item->total_profit = $revenue - $cost;
            $item->margin = $revenue > 0 ? round(($revenue - $cost) / $revenue * 100, 1) : 0;
        });

        return [
            'products' => $items,
            'report_period' => $startDate->format('d M Y') . ' - ' . $endDate->format('d M Y'),
        ];
    }

    public function exportPdf(Request $request)
    {
        $type = $request->type ?? 'daily-sales';
        $branchId = auth()->user()->branch_id;
        $data = [];
        $view = '';

        $dateFrom = $request->from ? Carbon::parse($request->from) : now()->startOfMonth();
        $dateTo = $request->to ? Carbon::parse($request->to) : now();

        $typeMap = [
            'daily-sales' => 'daily',
            'monthly-sales' => 'monthly',
            'profit-loss' => 'profit-loss',
            'inventory' => 'inventory',
            'product-performance' => 'product-performance',
        ];

        $pdfType = $typeMap[$type] ?? null;
        if (!$pdfType) {
            return back()->with('error', 'Invalid report type.');
        }

        switch ($pdfType) {
            case 'daily':
                $data = $this->dailySalesData($dateFrom, $branchId);
                $data['date'] = $dateFrom;
                $view = 'reports.pdf.daily-sales';
                break;

            case 'monthly':
                $data['month'] = $dateFrom;
                $data['sales'] = (float) Bill::where('payment_status', 'paid')
                    ->whereBetween('created_at', [$dateFrom->copy()->startOfMonth(), $dateFrom->copy()->endOfMonth()])
                    ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                    ->sum('total_amount');
                $data['expenses'] = (float) Expense::whereBetween('expense_date', [$dateFrom->copy()->startOfMonth(), $dateFrom->copy()->endOfMonth()])
                    ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                    ->sum('amount');
                $data['dailyData'] = Bill::where('payment_status', 'paid')
                    ->whereBetween('created_at', [$dateFrom->copy()->startOfMonth(), $dateFrom->copy()->endOfMonth()])
                    ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                    ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total_amount) as total_sum'))
                    ->groupBy('date')->orderBy('date')->get();
                $view = 'reports.pdf.monthly-sales';
                break;

            case 'profit-loss':
                $data = $this->profitLossData($dateFrom, $dateTo, $branchId);
                $view = 'reports.pdf.profit-loss';
                break;

            case 'inventory':
                $data['products'] = Product::with('category')
                    ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                    ->orderBy('name')->get();
                $view = 'reports.pdf.inventory';
                break;

            case 'product-performance':
                $data = $this->productPerformanceData($dateFrom, $dateTo, $branchId);
                $view = 'reports.pdf.product-performance';
                break;
        }

        $data['company'] = config('app.name', 'MyBar');
        $data['generated_at'] = now()->format('d M Y H:i');

        $pdf = Pdf::loadView($view, $data);
        $filename = str_replace('_', '-', $type) . '-report-' . now()->format('Ymd') . '.pdf';
        return $pdf->download($filename);
    }

    public function exportExcel(Request $request)
    {
        $type = $request->type ?? 'daily-sales';
        $branchId = auth()->user()->branch_id;
        $dateFrom = $request->from ? Carbon::parse($request->from) : now()->startOfMonth();
        $dateTo = $request->to ? Carbon::parse($request->to) : now();

        $typeMap = [
            'daily-sales' => 'daily_sales',
            'monthly-sales' => 'monthly_sales',
            'profit-loss' => 'profit_loss',
            'inventory' => 'inventory',
            'product-performance' => 'products',
        ];

        $csvType = $typeMap[$type] ?? null;
        if (!$csvType) {
            return back()->with('error', 'Invalid report type.');
        }

        $filename = str_replace('_', '-', $type) . '-report-' . now()->format('Ymd') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($csvType, $dateFrom, $dateTo, $branchId) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            switch ($csvType) {
                case 'daily_sales':
                    fputcsv($handle, ['Daily Sales Report', $dateFrom->format('d M Y')]);
                    fputcsv($handle, []);
                    fputcsv($handle, ['Date', 'Invoice #', 'Customer', 'Items', 'Total', 'Paid']);
                    $bills = Bill::with('customer')
                        ->where('payment_status', 'paid')->whereDate('created_at', $dateFrom)
                        ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                        ->orderBy('created_at', 'desc')->get();
                    foreach ($bills as $b) {
                        fputcsv($handle, [
                            $b->created_at->format('d M Y'),
                            $b->invoice_no ?? $b->id,
                            $b->customer->name ?? 'Walk-in',
                            $b->items_count,
                            formatCurrency((float) $b->total_amount),
                            formatCurrency((float) $b->paid_amount),
                        ]);
                    }
                    fputcsv($handle, []);
                    $totalSales = (float) $bills->sum('total_amount');
                    $dailyExpenses = (float) Expense::whereDate('expense_date', $dateFrom)
                        ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                        ->sum('amount');
                    fputcsv($handle, ['TOTAL SALES', '', '', '', formatCurrency($totalSales), '']);
                    fputcsv($handle, ['TOTAL TRANSACTIONS', $bills->count()]);
                    fputcsv($handle, ['TOTAL EXPENSES', '', '', '', formatCurrency($dailyExpenses), '']);
                    fputcsv($handle, ['NET PROFIT / LOSS', '', '', '', formatCurrency($totalSales - $dailyExpenses), '']);
                    break;

                case 'monthly_sales':
                    $month = $dateFrom;
                    fputcsv($handle, ['Monthly Sales Report', $month->format('F Y')]);
                    fputcsv($handle, []);
                    fputcsv($handle, ['Date', 'Sales']);
                    $dailyData = Bill::where('payment_status', 'paid')
                        ->whereBetween('created_at', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])
                        ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                        ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total_amount) as total_sum'))
                        ->groupBy('date')->orderBy('date')->get();
                    foreach ($dailyData as $d) {
                        fputcsv($handle, [$d->date, formatCurrency((float) ($d->total_sum ?? $d->total ?? 0))]);
                    }
                    $monthlyTotalSales = $dailyData->sum(fn($r) => $r->total_sum ?? $r->total ?? 0);
                    $monthlyExpenses = (float) Expense::whereBetween('expense_date', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])
                        ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                        ->sum('amount');
                    fputcsv($handle, ['TOTAL SALES', formatCurrency($monthlyTotalSales)]);
                    fputcsv($handle, ['TOTAL EXPENSES', formatCurrency($monthlyExpenses)]);
                    fputcsv($handle, ['NET PROFIT / LOSS', formatCurrency($monthlyTotalSales - $monthlyExpenses)]);
                    break;

                case 'profit_loss':
                    fputcsv($handle, ['Profit & Loss Report', $dateFrom->format('d M Y') . ' - ' . $dateTo->format('d M Y')]);
                    fputcsv($handle, []);
                    $totalSales = (float) Bill::where('payment_status', 'paid')
                        ->whereBetween('created_at', [$dateFrom, $dateTo])
                        ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                        ->sum('total_amount');
                    $totalExpenses = (float) Expense::whereBetween('expense_date', [$dateFrom, $dateTo])
                        ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                        ->sum('amount');
                    $costOfGoods = (float) BillItem::whereHas('bill', fn ($q) => $q->where('payment_status', 'paid')
                        ->whereBetween('created_at', [$dateFrom, $dateTo])
                        ->when($branchId, fn ($bq) => $bq->where('branch_id', $branchId)))
                        ->sum(DB::raw('bill_items.quantity * (SELECT cost_price FROM products WHERE products.id = bill_items.product_id)'));
                    fputcsv($handle, ['Total Sales', formatCurrency($totalSales)]);
                    fputcsv($handle, ['Cost of Goods Sold', formatCurrency($costOfGoods)]);
                    fputcsv($handle, ['Gross Profit', formatCurrency($totalSales - $costOfGoods)]);
                    fputcsv($handle, ['Total Expenses', formatCurrency($totalExpenses)]);
                    fputcsv($handle, ['Net Profit', formatCurrency($totalSales - $costOfGoods - $totalExpenses)]);
                    break;

                case 'inventory':
                    fputcsv($handle, ['Inventory Report', now()->format('d M Y')]);
                    fputcsv($handle, []);
                    fputcsv($handle, ['Product', 'Category', 'Stock', 'Reorder Level', 'Cost Price', 'Selling Price', 'Stock Value', 'Status']);
                    $products = Product::with('category')
                        ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                        ->orderBy('name')->get();
                    foreach ($products as $p) {
                        fputcsv($handle, [
                            $p->name,
                            $p->category->name ?? 'N/A',
                            $p->current_stock,
                            $p->reorder_level,
                            formatCurrency((float) $p->cost_price),
                            formatCurrency((float) $p->selling_price),
                            formatCurrency((float) $p->current_stock * (float) $p->cost_price),
                            ucfirst($p->stock_status ?? 'unknown'),
                        ]);
                    }
                    fputcsv($handle, []);
                    fputcsv($handle, ['TOTAL PRODUCTS', $products->count()]);
                    fputcsv($handle, ['TOTAL STOCK VALUE', formatCurrency($products->sum(fn ($p) => (float) $p->current_stock * (float) $p->cost_price))]);
                    break;

                case 'products':
                    fputcsv($handle, ['Product Performance Report', $dateFrom->format('d M Y') . ' - ' . $dateTo->format('d M Y')]);
                    fputcsv($handle, []);
                    fputcsv($handle, ['Product', 'Qty Sold', 'Revenue', 'Cost', 'Profit', 'Margin (%)']);
                    $items = BillItem::select(
                        'bill_items.product_id',
                        DB::raw('SUM(bill_items.quantity) as total_qty'),
                        DB::raw('SUM(bill_items.subtotal) as total_revenue'),
                        DB::raw('SUM(bill_items.quantity * (SELECT cost_price FROM products WHERE products.id = bill_items.product_id)) as total_cost')
                    )
                        ->whereHas('bill', fn ($q) => $q->where('payment_status', 'paid')
                            ->whereBetween('created_at', [$dateFrom, $dateTo])
                            ->when($branchId, fn ($bq) => $bq->where('branch_id', $branchId)))
                        ->groupBy('bill_items.product_id')
                        ->orderByDesc('total_qty')
                        ->get();
                    foreach ($items as $item) {
                        $revenue = (float) $item->total_revenue;
                        $cost = (float) $item->total_cost;
                        $profit = $revenue - $cost;
                        $margin = $revenue > 0 ? round($profit / $revenue * 100, 1) : 0;
                        $product = \App\Models\Product::find($item->product_id);
                        fputcsv($handle, [
                            $product->name ?? 'Unknown',
                            (int) $item->total_qty,
                            formatCurrency($revenue),
                            formatCurrency($cost),
                            formatCurrency($profit),
                            $margin,
                        ]);
                    }
                    break;
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
