<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\BillItem;
use App\Models\Expense;
use App\Models\Product;
use App\Models\Purchase;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index()
    {
        return view('reports.index');
    }

    public function dailySales(Request $request)
    {
        $date = $request->date ? Carbon::parse($request->date) : now();

        $sales = Bill::where('payment_status', 'paid')
            ->whereDate('created_at', $date)
            ->sum('total_amount');

        $ordersCount = Bill::where('payment_status', 'paid')
            ->whereDate('created_at', $date)
            ->count();

        $paymentMethods = Bill::where('payment_status', 'paid')
            ->whereDate('created_at', $date)
            ->select('payment_method', DB::raw('COUNT(*) as count'), DB::raw('SUM(total_amount) as total'))
            ->groupBy('payment_method')
            ->get();

        return view('reports.daily-sales', compact('date', 'sales', 'ordersCount', 'paymentMethods'));
    }

    public function monthlySales(Request $request)
    {
        $month = $request->month ? Carbon::parse($request->month) : now();
        $startOfMonth = $month->copy()->startOfMonth();
        $endOfMonth = $month->copy()->endOfMonth();

        $sales = Bill::where('payment_status', 'paid')
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->sum('total_amount');

        $expenses = Expense::whereBetween('expense_date', [$startOfMonth, $endOfMonth])
            ->sum('amount');

        $dailyData = Bill::where('payment_status', 'paid')
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total_amount) as total'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return view('reports.monthly-sales', compact('month', 'sales', 'expenses', 'dailyData'));
    }

    public function profitLoss(Request $request)
    {
        $startDate = $request->from_date ? Carbon::parse($request->from_date) : now()->startOfMonth();
        $endDate = $request->to_date ? Carbon::parse($request->to_date) : now();

        $totalSales = Bill::where('payment_status', 'paid')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('total_amount');

        $totalExpenses = Expense::whereBetween('expense_date', [$startDate, $endDate])
            ->sum('amount');

        $costOfGoods = BillItem::whereHas('bill', fn ($q) => $q->where('payment_status', 'paid')
            ->whereBetween('created_at', [$startDate, $endDate]))
            ->join('products', 'bill_items.product_id', '=', 'products.id')
            ->sum(DB::raw('bill_items.quantity * products.cost_price'));

        $grossProfit = $totalSales - $costOfGoods;
        $netProfit = $grossProfit - $totalExpenses;

        return view('reports.profit-loss', compact(
            'startDate', 'endDate', 'totalSales', 'totalExpenses',
            'costOfGoods', 'grossProfit', 'netProfit'
        ));
    }

    public function inventory()
    {
        $products = Product::with(['category', 'unit'])
            ->select('*', DB::raw('(current_stock * cost_price) as stock_value'))
            ->orderBy('name')
            ->get();

        $totalValue = $products->sum('stock_value');
        $lowStockCount = $products->filter(fn ($p) => $p->current_stock <= $p->reorder_level)->count();
        $avgCost = $products->avg('cost_price');

        $data = [
            'products' => $products,
            'total_products' => $products->count(),
            'total_value' => $totalValue,
            'low_stock' => $lowStockCount,
            'average_cost' => $avgCost,
        ];

        return view('reports.inventory', compact('data'));
    }

    public function productPerformance(Request $request)
    {
        $startDate = $request->from_date ? Carbon::parse($request->from_date) : now()->startOfMonth();
        $endDate = $request->to_date ? Carbon::parse($request->to_date) : now();

        $products = BillItem::select(
            'product_id',
            DB::raw('SUM(quantity) as total_qty'),
            DB::raw('SUM(subtotal) as total_revenue')
        )
            ->whereHas('bill', fn ($q) => $q->where('payment_status', 'paid')
                ->whereBetween('created_at', [$startDate, $endDate]))
            ->with('product')
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->get();

        return view('reports.product-performance', compact('products', 'startDate', 'endDate'));
    }

    public function exportPdf(Request $request)
    {
        $type = $request->type ?? 'daily';
        $data = [];
        $view = '';

        switch ($type) {
            case 'daily':
                $date = $request->date ? Carbon::parse($request->date) : now();
                $data['date'] = $date;
                $data['sales'] = Bill::where('payment_status', 'paid')->whereDate('created_at', $date)->sum('total_amount');
                $data['ordersCount'] = Bill::where('payment_status', 'paid')->whereDate('created_at', $date)->count();
                $view = 'reports.pdf.daily-sales';
                break;

            case 'monthly':
                $month = $request->month ? Carbon::parse($request->month) : now();
                $data['month'] = $month;
                $data['sales'] = Bill::where('payment_status', 'paid')
                    ->whereBetween('created_at', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])
                    ->sum('total_amount');
                $data['expenses'] = Expense::whereBetween('expense_date', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])
                    ->sum('amount');
                $view = 'reports.pdf.monthly-sales';
                break;

            case 'inventory':
                $data['products'] = Product::with('category')->orderBy('name')->get();
                $view = 'reports.pdf.inventory';
                break;

            default:
                return back()->with('error', 'Invalid report type.');
        }

        $pdf = Pdf::loadView($view, $data);
        return $pdf->download("{$type}-report.pdf");
    }

    public function exportExcel(Request $request)
    {
        $type = $request->report_type ?? 'daily_sales';
        $dateFrom = $request->date_from ? Carbon::parse($request->date_from) : now()->startOfMonth();
        $dateTo = $request->date_to ? Carbon::parse($request->date_to) : now();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $type . '-report.csv"',
        ];

        $callback = function () use ($type, $dateFrom, $dateTo) {
            $handle = fopen('php://output', 'w');

            fwrite($handle, "\xEF\xBB\xBF");

            switch ($type) {
                case 'daily_sales':
                    fputcsv($handle, ['Date', 'Total Sales', 'Orders Count', 'Payment Method', 'Method Count', 'Method Total']);
                    $sales = Bill::where('payment_status', 'paid')
                        ->whereDate('created_at', $dateFrom)
                        ->sum('total_amount');
                    $ordersCount = Bill::where('payment_status', 'paid')
                        ->whereDate('created_at', $dateFrom)
                        ->count();
                    $paymentMethods = Bill::where('payment_status', 'paid')
                        ->whereDate('created_at', $dateFrom)
                        ->select('payment_method', DB::raw('COUNT(*) as count'), DB::raw('SUM(total_amount) as total'))
                        ->groupBy('payment_method')
                        ->get();
                    foreach ($paymentMethods as $pm) {
                        fputcsv($handle, [$dateFrom->format('Y-m-d'), $sales, $ordersCount, $pm->payment_method, $pm->count, $pm->total]);
                    }
                    break;

                case 'monthly_sales':
                    fputcsv($handle, ['Date', 'Daily Sales', 'Expenses']);
                    $dailyData = Bill::where('payment_status', 'paid')
                        ->whereBetween('created_at', [$dateFrom, $dateTo])
                        ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total_amount) as total'))
                        ->groupBy('date')
                        ->orderBy('date')
                        ->get();
                    $totalExpenses = Expense::whereBetween('expense_date', [$dateFrom, $dateTo])->sum('amount');
                    foreach ($dailyData as $d) {
                        fputcsv($handle, [$d->date, $d->total, $totalExpenses]);
                    }
                    fputcsv($handle, ['TOTAL', $dailyData->sum('total'), $totalExpenses]);
                    break;

                case 'profit_loss':
                    fputcsv($handle, ['Metric', 'Value']);
                    $totalSales = Bill::where('payment_status', 'paid')
                        ->whereBetween('created_at', [$dateFrom, $dateTo])
                        ->sum('total_amount');
                    $totalExpenses = Expense::whereBetween('expense_date', [$dateFrom, $dateTo])->sum('amount');
                    $costOfGoods = BillItem::whereHas('bill', fn ($q) => $q->where('payment_status', 'paid')
                        ->whereBetween('created_at', [$dateFrom, $dateTo]))
                        ->join('products', 'bill_items.product_id', '=', 'products.id')
                        ->sum(DB::raw('bill_items.quantity * products.cost_price'));
                    $grossProfit = $totalSales - $costOfGoods;
                    $netProfit = $grossProfit - $totalExpenses;
                    fputcsv($handle, ['Period', $dateFrom->format('Y-m-d') . ' to ' . $dateTo->format('Y-m-d')]);
                    fputcsv($handle, ['Total Sales', $totalSales]);
                    fputcsv($handle, ['Cost of Goods Sold', $costOfGoods]);
                    fputcsv($handle, ['Gross Profit', $grossProfit]);
                    fputcsv($handle, ['Total Expenses', $totalExpenses]);
                    fputcsv($handle, ['Net Profit', $netProfit]);
                    break;

                case 'inventory':
                    fputcsv($handle, ['Product', 'Category', 'Current Stock', 'Reorder Level', 'Cost Price', 'Stock Value']);
                    $products = Product::with('category')
                        ->select('*', DB::raw('(current_stock * cost_price) as stock_value'))
                        ->orderBy('name')
                        ->get();
                    foreach ($products as $p) {
                        fputcsv($handle, [
                            $p->name,
                            $p->category->name ?? 'N/A',
                            $p->current_stock,
                            $p->reorder_level,
                            $p->cost_price,
                            $p->stock_value,
                        ]);
                    }
                    break;

                case 'products':
                    fputcsv($handle, ['Product', 'Quantity Sold', 'Total Revenue']);
                    $products = BillItem::select(
                        'product_id',
                        DB::raw('SUM(quantity) as total_qty'),
                        DB::raw('SUM(subtotal) as total_revenue')
                    )
                        ->whereHas('bill', fn ($q) => $q->where('payment_status', 'paid')
                            ->whereBetween('created_at', [$dateFrom, $dateTo]))
                        ->with('product')
                        ->groupBy('product_id')
                        ->orderByDesc('total_qty')
                        ->get();
                    foreach ($products as $p) {
                        fputcsv($handle, [
                            $p->product->name ?? 'Unknown',
                            $p->total_qty,
                            $p->total_revenue,
                        ]);
                    }
                    break;
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
