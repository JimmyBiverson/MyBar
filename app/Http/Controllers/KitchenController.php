<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;

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

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string|in:pending,preparing,ready,cancelled',
        ]);

        try {
            $item = OrderItem::findOrFail($id);
            $item->update(['status' => $request->status]);

            $order = $item->order;
            $allItemsReady = $order->items()->whereNotIn('status', ['ready', 'cancelled'])->count() === 0;

            if ($allItemsReady) {
                $order->update(['status' => 'ready']);
            }

            return redirect()->route('kitchen.index')
                ->with('success', 'Item status updated to ' . $request->status . '.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update item status.');
        }
    }
}
