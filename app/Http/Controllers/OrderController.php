<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Table;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['table', 'waiter', 'items.product', 'customer']);

        if ($search = $request->search) {
            $query->where('order_number', 'like', "%{$search}%");
        }

        if ($status = $request->status) {
            $query->where('status', $status);
        }

        if ($branchId = auth()->user()->branch_id) {
            $query->where('branch_id', $branchId);
        }

        $orders = $query->latest()->paginate(15);

        return view('orders.index', compact('orders'));
    }

    public function create()
    {
        $products = Product::where('is_active', true)->get();
        $categories = Category::where('is_active', true)->get();
        $customers = Customer::all();

        return view('orders.form', compact('products', 'categories', 'customers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'table_id' => 'nullable|exists:tables,id',
            'customer_id' => 'nullable|exists:customers,id',
            'order_type' => 'required|string|in:dine_in,takeaway,delivery',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.notes' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $order = Order::create([
                'order_number' => Order::generateOrderNumber(),
                'table_id' => $request->table_id,
                'customer_id' => $request->customer_id,
                'waiter_id' => auth()->id(),
                'status' => 'pending',
                'order_type' => $request->order_type,
                'notes' => $request->notes,
                'branch_id' => auth()->user()->branch_id,
            ]);

            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'price' => $product->selling_price,
                    'subtotal' => $product->selling_price * $item['quantity'],
                    'notes' => $item['notes'] ?? null,
                    'status' => 'pending',
                ]);
            }

            if ($request->table_id) {
                Table::where('id', $request->table_id)->update(['status' => 'occupied']);
            }

            DB::commit();

            return redirect()->route('orders.show', $order)
                ->with('success', 'Order created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to create order: ' . $e->getMessage());
        }
    }

    public function show(Order $order)
    {
        $order->load(['items.product', 'table', 'waiter', 'customer', 'bill']);
        return view('orders.show', compact('order'));
    }

    public function update(Request $request, Order $order)
    {
        $request->validate([
            'table_id' => 'nullable|exists:tables,id',
            'customer_id' => 'nullable|exists:customers,id',
            'notes' => 'nullable|string',
        ]);

        try {
            $order->update($request->only(['table_id', 'customer_id', 'notes']));
            return redirect()->route('orders.show', $order)
                ->with('success', 'Order updated successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update order.');
        }
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|string|in:pending,confirmed,preparing,ready,served,completed,cancelled',
        ]);

        try {
            $updateData = ['status' => $request->status];

            if ($request->status === 'confirmed' && !$order->received_at) {
                $updateData['received_at'] = Carbon::now();
            }
            if ($request->status === 'served' && !$order->served_at) {
                $updateData['served_at'] = Carbon::now();
            }
            if ($request->status === 'completed' && !$order->completed_at) {
                $updateData['completed_at'] = Carbon::now();
            }

            $order->update($updateData);

            if ($request->status === 'completed' || $request->status === 'cancelled') {
                if ($order->table_id) {
                    Table::where('id', $order->table_id)->update(['status' => 'available']);
                }
            }

            return redirect()->route('orders.index')
                ->with('success', 'Order status updated to ' . $request->status . '.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update order status.');
        }
    }

    public function destroy(Order $order)
    {
        try {
            $order->items()->delete();
            $order->delete();

            if ($order->table_id) {
                Table::where('id', $order->table_id)->update(['status' => 'available']);
            }

            return redirect()->route('orders.index')
                ->with('success', 'Order deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete order.');
        }
    }
}
