<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\BillItem;
use App\Models\Expense;
use App\Models\Product;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DashboardController extends Controller
{
    private function paidBillsQuery($branchId)
    {
        return Bill::where('payment_status', 'paid')
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId));
    }

    public function index()
    {
        $branchId = auth()->user()->branch_id;
        $today = now()->toDateString();
        $startOfMonth = now()->startOfMonth()->toDateString();

        $todaySales = (clone $this->paidBillsQuery($branchId))
            ->whereDate('created_at', $today)
            ->sum('total_amount');

        $monthlySales = (clone $this->paidBillsQuery($branchId))
            ->whereDate('created_at', '>=', $startOfMonth)
            ->sum('total_amount');

        $totalSales = (clone $this->paidBillsQuery($branchId))->sum('total_amount');

        $yesterdaySales = (clone $this->paidBillsQuery($branchId))
            ->whereDate('created_at', now()->subDay()->toDateString())
            ->sum('total_amount');
        $todaySalesPercent = $yesterdaySales > 0
            ? round(($todaySales - $yesterdaySales) / $yesterdaySales * 100, 1)
            : ($todaySales > 0 ? 100 : 0);

        $lastMonthStart = now()->subMonth()->startOfMonth()->toDateString();
        $lastMonthEnd = now()->subMonth()->endOfMonth()->toDateString();
        $lastMonthSales = (clone $this->paidBillsQuery($branchId))
            ->whereDate('created_at', '>=', $lastMonthStart)
            ->whereDate('created_at', '<=', $lastMonthEnd)
            ->sum('total_amount');
        $monthlySalesPercent = $lastMonthSales > 0
            ? round(($monthlySales - $lastMonthSales) / $lastMonthSales * 100, 1)
            : ($monthlySales > 0 ? 100 : 0);

        $expenseQuery = Expense::when($branchId, fn ($q) => $q->where('branch_id', $branchId));
        $todayExpenses = (clone $expenseQuery)->whereDate('expense_date', $today)->sum('amount');
        $monthlyExpenses = (clone $expenseQuery)->whereDate('expense_date', '>=', $startOfMonth)->sum('amount');
        $expensePercent = $monthlySales > 0
            ? round($monthlyExpenses / $monthlySales * 100, 1)
            : 0;

        $allProducts = Product::with('category')
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->orderBy('current_stock')
            ->get();

        $lowStockProducts = $allProducts->filter(fn ($p) => $p->stock_status === 'low')->take(10);
        $mediumStockProducts = $allProducts->filter(fn ($p) => $p->stock_status === 'medium')->take(5);
        $lowStockCount = $allProducts->filter(fn ($p) => $p->stock_status === 'low')->count();
        $stockStatusCounts = [
            'low' => $allProducts->filter(fn ($p) => $p->stock_status === 'low')->count(),
            'medium' => $allProducts->filter(fn ($p) => $p->stock_status === 'medium')->count(),
            'good' => $allProducts->filter(fn ($p) => $p->stock_status === 'good')->count(),
        ];

        $recentTransactions = Bill::with(['customer', 'items'])
            ->where('payment_status', 'paid')
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->latest()
            ->take(10)
            ->get()
            ->map(function ($bill) {
                $bill->items_count = $bill->items->count();
                return $bill;
            });

        $recentOrders = Order::with(['table', 'waiter', 'items.product', 'bill'])
            ->whereIn('status', ['pending', 'confirmed', 'preparing', 'ready', 'served', 'completed'])
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->latest()
            ->take(10)
            ->get();

        $topProducts = BillItem::select(
            'product_id',
            DB::raw('SUM(quantity) as total_qty'),
            DB::raw('SUM(subtotal) as total_revenue')
        )
            ->with('product')
            ->whereHas('bill', fn ($q) => $q->where('payment_status', 'paid')
                ->whereDate('created_at', '>=', now()->startOfMonth())
                ->when(auth()->user()->branch_id, fn ($bq) => $bq->where('branch_id', auth()->user()->branch_id)))
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->take(5)
            ->get();

        $chartDays = 30;
        $billChartRaw = (clone $this->paidBillsQuery($branchId))
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total_amount) as total_sum'))
            ->whereDate('created_at', '>=', now()->subDays($chartDays))
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('total_sum', 'date');

        $chartData = collect();
        for ($i = $chartDays; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $chartData->push([
                'date' => $date,
                'total' => (float) ($billChartRaw[$date] ?? 0),
            ]);
        }
        $chartLabels = $chartData->pluck('date');
        $chartValues = $chartData->pluck('total');

        $paymentMethods = Bill::where('payment_status', 'paid')
            ->whereDate('created_at', $today)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->groupBy('payment_method')
            ->selectRaw('payment_method, COUNT(*) as count, SUM(total_amount) as total')
            ->toBase()
            ->get();

        $processorStats = Bill::where('payment_status', 'paid')
            ->whereDate('created_at', $today)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->selectRaw("COUNT(*) as total_count")
            ->selectRaw("SUM(CASE WHEN processed_by_role = 'waiter' THEN 1 ELSE 0 END) as waiter_count")
            ->selectRaw("SUM(CASE WHEN processed_by_role = 'waiter' THEN total_amount ELSE 0 END) as waiter_total")
            ->selectRaw("SUM(CASE WHEN processed_by_role = 'cashier' THEN 1 ELSE 0 END) as cashier_count")
            ->selectRaw("SUM(CASE WHEN processed_by_role = 'cashier' THEN total_amount ELSE 0 END) as cashier_total")
            ->first();

        $topCustomers = Bill::select(
            'customer_id',
            DB::raw('COUNT(*) as visit_count'),
            DB::raw('SUM(total_amount) as total_spent')
        )
            ->where('payment_status', 'paid')
            ->whereDate('created_at', '>=', now()->startOfMonth())
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->with('customer')
            ->groupBy('customer_id')
            ->orderByDesc('total_spent')
            ->take(5)
            ->get();

        $categorySales = BillItem::select(
            'products.category_id',
            DB::raw('COALESCE(categories.name, \'Uncategorized\') as category_name'),
            DB::raw('SUM(bill_items.subtotal) as total')
        )
            ->join('products', 'bill_items.product_id', '=', 'products.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->whereHas('bill', fn ($q) => $q->where('payment_status', 'paid')
                ->whereDate('created_at', '>=', $startOfMonth)
                ->when($branchId, fn ($bq) => $bq->where('branch_id', $branchId)))
            ->groupBy('products.category_id', 'categories.name')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($item) => [
                'category' => $item->category_name,
                'total' => (float) $item->total,
            ]);

        return view('dashboard.index', compact(
            'todaySales', 'monthlySales', 'totalSales',
            'todaySalesPercent', 'monthlySalesPercent',
            'todayExpenses', 'monthlyExpenses', 'expensePercent',
            'lowStockCount', 'lowStockProducts', 'mediumStockProducts', 'stockStatusCounts',
            'recentTransactions', 'recentOrders', 'topProducts',
            'chartLabels', 'chartValues',
            'paymentMethods', 'processorStats', 'topCustomers', 'categorySales',
        ));
    }

    public function chartData(Request $request)
    {
        $days = min(90, max(1, (int) ($request->days ?? 30)));
        $branchId = auth()->user()->branch_id;

        $billRaw = Bill::where('payment_status', 'paid')
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total_amount) as total_sum'))
            ->whereDate('created_at', '>=', now()->subDays($days))
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('total_sum', 'date');

        $data = collect();
        for ($i = $days; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $data->push([
                'date' => $date,
                'total' => (float) ($billRaw[$date] ?? 0),
            ]);
        }

        return response()->json([
            'labels' => $data->pluck('date'),
            'values' => $data->pluck('total'),
        ]);
    }

    public function profile()
    {
        $user = auth()->user();
        return view('profile.show', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'current_password' => 'required_with:password|string|current_password',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $user->update($request->only(['name', 'email']));

        if ($request->filled('password')) {
            $user->update(['password' => Hash::make($request->password)]);
        }

        return back()->with('success', 'Profile updated successfully.');
    }
}
