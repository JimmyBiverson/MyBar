<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\BillItem;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Table;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WaiterController extends Controller
{
    public function index()
    {
        $waiterId = auth()->id();
        $branchId = auth()->user()->branch_id;

        $assignedTables = Table::whereHas('orders', fn ($q) => $q->where('waiter_id', $waiterId)
            ->whereNotIn('status', ['completed', 'cancelled']))
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->get();

        $activeOrders = Order::with(['table', 'items.product'])
            ->where('waiter_id', $waiterId)
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->latest()
            ->get();

        $tables = Table::when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->where('status', 'available')
            ->get();

        return view('waiter.index', compact('assignedTables', 'activeOrders', 'tables'));
    }

    public function orders()
    {
        $orders = Order::with(['table', 'items.product', 'customer', 'bill'])
            ->where('waiter_id', auth()->id())
            ->latest()
            ->paginate(15);

        return view('waiter.orders', compact('orders'));
    }

    public function storeOrder(Request $request)
    {
        $request->validate([
            'table_id' => 'required',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.notes' => 'nullable|string',
            'customer_name' => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            $items = $request->input('items', []);
            $tableId = is_numeric($request->table_id) ? $request->table_id : null;
            $orderType = $tableId ? 'dine_in' : 'takeaway';

            $customerId = null;
            if ($request->filled('customer_name')) {
                $customer = Customer::firstOrCreate(
                    ['name' => $request->customer_name, 'branch_id' => auth()->user()->branch_id],
                    ['is_active' => true]
                );
                $customerId = $customer->id;
            }

            $order = Order::create([
                'order_number' => Order::generateOrderNumber(),
                'table_id' => $tableId,
                'waiter_id' => auth()->id(),
                'customer_id' => $customerId,
                'status' => 'pending',
                'order_type' => $orderType,
                'notes' => $request->notes,
                'branch_id' => auth()->user()->branch_id,
            ]);

            foreach ($items as $item) {
                $product = Product::findOrFail($item['product_id']);
                $qty = $item['qty'] ?? 1;
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'price' => $product->selling_price,
                    'subtotal' => $product->selling_price * $qty,
                    'notes' => $item['notes'] ?? null,
                    'status' => 'pending',
                ]);
            }

            if ($tableId) {
                Table::where('id', $tableId)->update(['status' => 'occupied']);
            }

            DB::commit();

            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'order_id' => $order->id]);
            }

            return redirect()->route('waiter.index')
                ->with('success', 'Order placed successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }

            return back()->withInput()->with('error', 'Failed to create order: ' . $e->getMessage());
        }
    }

    public function createOrder(Request $request)
    {
        $branchId = auth()->user()->branch_id;
        $tables = Table::when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->where('status', 'available')
            ->get();
        $products = Product::where('is_active', true)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->get();

        return view('waiter.create-order', compact('tables', 'products'));
    }

    public function tablesData()
    {
        $waiterId = auth()->id();
        $branchId = auth()->user()->branch_id;

        $tables = Table::whereHas('orders', fn ($q) => $q->where('waiter_id', $waiterId)
            ->whereNotIn('status', ['completed', 'cancelled']))
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->get()
            ->map(fn ($t) => [
                'id' => $t->id,
                'name' => $t->name,
                'capacity' => $t->capacity,
                'status' => 'occupied',
            ]);

        $availableTables = Table::when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->where('status', 'available')
            ->get()
            ->map(fn ($t) => [
                'id' => $t->id,
                'name' => $t->name,
                'capacity' => $t->capacity,
                'status' => 'available',
            ]);

        return response()->json(['tables' => $tables->concat($availableTables)]);
    }

    public function productsData()
    {
        $branchId = auth()->user()->branch_id;
        $products = Product::where('is_active', true)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->get(['id', 'name', 'selling_price', 'current_stock']);

        return response()->json(['products' => $products]);
    }

    public function ordersData()
    {
        $orders = Order::with(['table', 'items.product', 'bill'])
            ->where('waiter_id', auth()->id())
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->latest()
            ->get()
            ->map(fn ($o) => [
                'id' => $o->id,
                'table_name' => $o->table->name ?? 'Takeaway',
                'table_id' => $o->table_id,
                'status' => $o->status,
                'progress' => $o->progress,
                'progress_label' => $o->progress_label,
                'items_text' => $o->items->take(3)->pluck('product.name')->implode(', ') . ($o->items->count() > 3 ? '...' : ''),
                'items_count' => $o->items->count(),
                'items' => $o->items->map(fn ($i) => [
                    'id' => $i->id,
                    'product_name' => $i->product->name ?? 'N/A',
                    'qty' => $i->quantity,
                    'status' => $i->status,
                    'rejection_reason' => $i->rejection_reason,
                ]),
                'payment_status' => $o->payment_status,
                'bill_requested' => $o->bill_requested,
                'total' => $o->items->sum('subtotal'),
                'created_at' => $o->created_at->format('H:i'),
                'received_at' => $o->received_at ? $o->received_at->format('H:i') : null,
                'served_at' => $o->served_at ? $o->served_at->format('H:i') : null,
            ]);

        return response()->json(['orders' => $orders]);
    }

    public function orderDetail(Order $order)
    {
        $order->load(['items.product', 'table', 'bill']);

        if ($order->waiter_id !== auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'success' => true,
            'order' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'table_name' => $order->table->name ?? 'Takeaway',
                'status' => $order->status,
                'progress' => $order->progress,
                'progress_label' => $order->progress_label,
                'items' => $order->items->map(fn ($i) => [
                    'id' => $i->id,
                    'product_name' => $i->product->name ?? 'N/A',
                    'qty' => $i->quantity,
                    'price' => $i->price,
                    'status' => $i->status,
                    'rejection_reason' => $i->rejection_reason,
                    'notes' => $i->notes,
                ]),
                'payment_status' => $order->payment_status,
                'bill_requested' => $order->bill_requested,
                'total' => $order->items->sum('subtotal'),
                'created_at' => $order->created_at->format('H:i'),
                'received_at' => $order->received_at ? $order->received_at->format('H:i') : null,
                'served_at' => $order->served_at ? $order->served_at->format('H:i') : null,
                'completed_at' => $order->completed_at ? $order->completed_at->format('H:i') : null,
            ],
        ]);
    }

    public function markServed(Request $request)
    {
        $request->validate(['order_id' => 'required|exists:orders,id']);

        $order = Order::where('waiter_id', auth()->id())->findOrFail($request->order_id);
        $order->update(['status' => 'served', 'served_at' => now()]);

        return response()->json(['success' => true]);
    }

    public function cancelOrder(Request $request)
    {
        $request->validate(['order_id' => 'required|exists:orders,id']);

        $order = Order::where('waiter_id', auth()->id())->findOrFail($request->order_id);
        $order->update(['status' => 'cancelled']);

        if ($order->table_id) {
            Table::where('id', $order->table_id)->update(['status' => 'available']);
        }

        return response()->json(['success' => true]);
    }

    public function requestBill(Request $request)
    {
        $request->validate(['order_id' => 'required|exists:orders,id']);

        $order = Order::with('items')->where('waiter_id', auth()->id())->findOrFail($request->order_id);

        $bill = Bill::create([
            'bill_number' => Bill::generateBillNumber(),
            'order_id' => $order->id,
            'customer_id' => $order->customer_id,
            'subtotal' => $order->items->sum('subtotal'),
            'total_amount' => $order->items->sum('subtotal'),
            'payment_status' => 'unpaid',
            'waiter_id' => auth()->id(),
            'branch_id' => auth()->user()?->branch_id,
        ]);

        foreach ($order->items as $item) {
            $bill->items()->create([
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'price' => $item->price,
                'subtotal' => $item->subtotal,
            ]);
        }

        return response()->json(['success' => true, 'bill_id' => $bill->id]);
    }

    public function processPayment(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'payment_method' => 'required|string',
            'amount_received' => 'required|numeric|min:0',
            'mobile_provider' => 'nullable|string',
            'reference_number' => 'nullable|string',
        ]);

        $order = Order::with('items.product')
            ->where('waiter_id', auth()->id())
            ->findOrFail($request->order_id);

        if ($order->status === 'completed' || $order->status === 'cancelled') {
            return response()->json(['success' => false, 'message' => 'Order is already ' . $order->status], 400);
        }

        try {
            DB::beginTransaction();

            $total = $order->items->sum('subtotal');
            $paidAmount = $request->amount_received;
            $changeAmount = max(0, $paidAmount - $total);

            $bill = Bill::create([
                'bill_number' => Bill::generateBillNumber(),
                'order_id' => $order->id,
                'customer_id' => $order->customer_id,
                'subtotal' => $total,
                'total_amount' => $total,
                'paid_amount' => $paidAmount,
                'change_amount' => $changeAmount,
                'payment_method' => $request->payment_method,
                'mobile_provider' => $request->mobile_provider,
                'reference_number' => $request->reference_number,
                'payment_status' => 'paid',
                'waiter_id' => auth()->id(),
                'processed_by_role' => 'waiter',
                'branch_id' => auth()->user()->branch_id,
            ]);

            foreach ($order->items as $item) {
                $product = $item->product;
                if (!$product) {
                    throw new \Exception('Product not found for order item #' . $item->id);
                }

                if ($product->current_stock < $item->quantity) {
                    throw new \Exception('Insufficient stock for ' . $product->name . ': only ' . $product->current_stock . ' available');
                }

                BillItem::create([
                    'bill_id' => $bill->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'subtotal' => $item->subtotal,
                ]);

                $product->decrement('current_stock', $item->quantity);
                $product->decrement('stock_value', (float) $product->cost_price * (float) $item->quantity);

                StockMovement::create([
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'type' => 'out',
                    'reference_type' => 'bill',
                    'reference_id' => $bill->id,
                    'notes' => 'Sale #' . $bill->bill_number,
                    'created_by' => auth()->id(),
                    'branch_id' => auth()->user()->branch_id,
                ]);
            }

            $order->update(['status' => 'completed', 'completed_at' => now()]);

            if ($order->table_id) {
                Table::where('id', $order->table_id)->update(['status' => 'available']);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'bill_id' => $bill->id,
                'receipt_no' => $bill->bill_number,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
