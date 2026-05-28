<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Carbon\Carbon;

class KitchenController extends Controller
{
    public function index()
    {
        $orders = Order::with(['items.product', 'table', 'waiter'])
            ->whereIn('status', ['confirmed', 'preparing'])
            ->when($branchId = auth()->user()->branch_id, fn ($q) => $q->where('branch_id', $branchId))
            ->latest()
            ->get();

        return view('kitchen.index', compact('orders'));
    }

    public function getOrders()
    {
        $orders = Order::with(['items.product', 'table', 'waiter'])
            ->whereIn('status', ['confirmed', 'preparing', 'ready'])
            ->when($branchId = auth()->user()->branch_id, fn ($q) => $q->where('branch_id', $branchId))
            ->latest()
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'status' => $order->status,
                    'table_name' => $order->table->name ?? null,
                    'created_at' => $order->created_at,
                    'items' => $order->items->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'product_name' => $item->product->name ?? 'Unknown',
                            'qty' => $item->quantity,
                            'notes' => $item->notes,
                        ];
                    }),
                ];
            });

        return response()->json(['orders' => $orders]);
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|string|in:pending,preparing,ready,served,cancelled',
        ]);

        try {
            $status = $request->status;

            if ($status === 'preparing') {
                $order->items()->where('status', 'pending')->update(['status' => 'preparing']);
                $order->update(['status' => 'preparing']);
            } elseif ($status === 'ready') {
                $order->items()->whereIn('status', ['pending', 'preparing'])->update(['status' => 'ready']);
                $order->update(['status' => 'ready']);
            } elseif ($status === 'served') {
                $order->items()->update(['status' => 'served']);
                $order->update(['status' => 'served', 'served_at' => Carbon::now()]);
            } else {
                $order->update(['status' => $status]);
            }

            if ($request->wantsJson()) {
                return response()->json(['success' => true]);
            }

            return redirect()->route('kitchen.index')
                ->with('success', 'Order status updated to ' . $status . '.');
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
            return back()->with('error', 'Failed to update order status.');
        }
    }
}
