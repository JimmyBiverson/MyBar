<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\BillItem;
use App\Models\Expense;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DashboardController extends Controller
{
    private function salesQuery($branchId, $dateField = 'created_at')
    {
        $paidBills = Bill::where('payment_status', 'paid')
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId));

        $orderTotalSub = Order::whereIn('orders.status', ['confirmed', 'preparing', 'ready', 'served', 'completed'])
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->select(DB::raw('COALESCE(SUM(order_items.subtotal), 0) as total'));

        return [$paidBills, $orderTotalSub];
    }

    public function index()
    {
        $branchId = auth()->user()->branch_id;
        $today = now()->toDateString();
        $startOfMonth = now()->startOfMonth()->toDateString();

        $todayFromBills = (clone $this->salesQuery($branchId)[0])
            ->whereDate('created_at', $today)
            ->sum('total_amount');

        $todayFromOrders = Order::whereIn('orders.status', ['confirmed', 'preparing', 'ready', 'served', 'completed'])
            ->whereDate('orders.created_at', $today)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->sum('order_items.subtotal');
        $todaySales = $todayFromBills + $todayFromOrders;

        $monthlyFromBills = (clone $this->salesQuery($branchId)[0])
            ->whereDate('created_at', '>=', $startOfMonth)
            ->sum('total_amount');

        $monthlyFromOrders = Order::whereIn('orders.status', ['confirmed', 'preparing', 'ready', 'served', 'completed'])
            ->whereDate('orders.created_at', '>=', $startOfMonth)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->sum('order_items.subtotal');
        $monthlySales = $monthlyFromBills + $monthlyFromOrders;

        $totalSales = (clone $this->salesQuery($branchId)[0])->sum('total_amount')
            + Order::whereIn('orders.status', ['confirmed', 'preparing', 'ready', 'served', 'completed'])
                ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                ->join('order_items', 'orders.id', '=', 'order_items.order_id')
                ->sum('order_items.subtotal');

        $yesterdayFromBills = (clone $this->salesQuery($branchId)[0])
            ->whereDate('created_at', now()->subDay()->toDateString())
            ->sum('total_amount');
        $yesterdayFromOrders = Order::whereIn('orders.status', ['confirmed', 'preparing', 'ready', 'served', 'completed'])
            ->whereDate('orders.created_at', now()->subDay()->toDateString())
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->sum('order_items.subtotal');
        $yesterdaySales = $yesterdayFromBills + $yesterdayFromOrders;
        $todaySalesPercent = $yesterdaySales > 0
            ? round(($todaySales - $yesterdaySales) / $yesterdaySales * 100, 1)
            : ($todaySales > 0 ? 100 : 0);

        $lastMonthStart = now()->subMonth()->startOfMonth()->toDateString();
        $lastMonthEnd = now()->subMonth()->endOfMonth()->toDateString();
        $lastMonthFromBills = (clone $this->salesQuery($branchId)[0])
            ->whereDate('created_at', '>=', $lastMonthStart)
            ->whereDate('created_at', '<=', $lastMonthEnd)
            ->sum('total_amount');
        $lastMonthFromOrders = Order::whereIn('orders.status', ['confirmed', 'preparing', 'ready', 'served', 'completed'])
            ->whereDate('orders.created_at', '>=', $lastMonthStart)
            ->whereDate('orders.created_at', '<=', $lastMonthEnd)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->sum('order_items.subtotal');
        $lastMonthSales = $lastMonthFromBills + $lastMonthFromOrders;
        $monthlySalesPercent = $lastMonthSales > 0
            ? round(($monthlySales - $lastMonthSales) / $lastMonthSales * 100, 1)
            : ($monthlySales > 0 ? 100 : 0);

        $expenseQuery = Expense::when($branchId, fn ($q) => $q->where('branch_id', $branchId));
        $todayExpenses = (clone $expenseQuery)->whereDate('expense_date', $today)->sum('amount');
        $monthlyExpenses = (clone $expenseQuery)->whereDate('expense_date', '>=', $startOfMonth)->sum('amount');
        $expensePercent = $monthlySales > 0
            ? round($monthlyExpenses / $monthlySales * 100, 1)
            : 0;

        $lowStockCount = Product::whereColumn('current_stock', '<=', 'reorder_level')
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->count();
        $lowStockProducts = Product::with('category')
            ->whereColumn('current_stock', '<=', 'reorder_level')
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->orderBy('current_stock')
            ->limit(10)
            ->get();

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
            ->whereIn('status', ['confirmed', 'preparing', 'ready', 'served', 'completed'])
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
                ->when(auth()->user()->branch_id, fn ($bq) => $bq->where('branch_id', auth()->user()->branch_id)))
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->take(5)
            ->get();

        $chartDays = 30;
        $chartRaw = [];
        $billChartRaw = (clone $this->salesQuery($branchId)[0])
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total_amount) as total'))
            ->whereDate('created_at', '>=', now()->subDays($chartDays))
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('total', 'date');

        $orderChartRaw = Order::whereIn('orders.status', ['confirmed', 'preparing', 'ready', 'served', 'completed'])
            ->whereDate('orders.created_at', '>=', now()->subDays($chartDays))
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->select(DB::raw('DATE(orders.created_at) as date'), DB::raw('SUM(order_items.subtotal) as total'))
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('total', 'date');

        $chartData = collect();
        for ($i = $chartDays; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $billVal = (float) ($billChartRaw[$date] ?? 0);
            $orderVal = (float) ($orderChartRaw[$date] ?? 0);
            $chartData->push([
                'date' => $date,
                'total' => $billVal + $orderVal,
            ]);
        }
        $chartLabels = $chartData->pluck('date');
        $chartValues = $chartData->pluck('total');

        return view('dashboard.index', compact(
            'todaySales', 'monthlySales', 'totalSales',
            'todaySalesPercent', 'monthlySalesPercent',
            'todayExpenses', 'monthlyExpenses', 'expensePercent',
            'lowStockCount', 'lowStockProducts',
            'recentTransactions', 'recentOrders', 'topProducts',
            'chartLabels', 'chartValues',
        ));
    }

    public function chartData(Request $request)
    {
        $days = min(90, max(1, (int) ($request->days ?? 30)));
        $branchId = auth()->user()->branch_id;

        $billRaw = Bill::where('payment_status', 'paid')
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total_amount) as total'))
            ->whereDate('created_at', '>=', now()->subDays($days))
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('total', 'date');

        $orderRaw = Order::whereIn('orders.status', ['confirmed', 'preparing', 'ready', 'served', 'completed'])
            ->whereDate('orders.created_at', '>=', now()->subDays($days))
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->select(DB::raw('DATE(orders.created_at) as date'), DB::raw('SUM(order_items.subtotal) as total'))
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('total', 'date');

        $data = collect();
        for ($i = $days; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $data->push([
                'date' => $date,
                'total' => ((float) ($billRaw[$date] ?? 0)) + ((float) ($orderRaw[$date] ?? 0)),
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
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $user->update($request->only(['name', 'email']));

        if ($request->filled('password')) {
            $user->update(['password' => Hash::make($request->password)]);
        }

        return back()->with('success', 'Profile updated successfully.');
    }
}
