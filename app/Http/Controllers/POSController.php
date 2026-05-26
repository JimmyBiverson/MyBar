<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\BillItem;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Table;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class POSController extends Controller
{
    public function index()
    {
        $branchId = auth()->user()->branch_id;

        $products = Product::with(['category', 'unit'])
            ->where('is_active', true)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->get();

        $categories = Category::where('is_active', true)->get();
        $tables = Table::when($branchId, fn ($q) => $q->where('branch_id', $branchId))->get();
        $customers = Customer::when($branchId, fn ($q) => $q->where('branch_id', $branchId))->get();

        $settings = Setting::whereIn('key', ['tax_rate', 'service_charge_rate', 'currency_symbol', 'currency_position'])
            ->pluck('value', 'key');

        return view('pos.index', compact('products', 'categories', 'tables', 'customers', 'settings'));
    }

    public function pendingOrders()
    {
        $branchId = auth()->user()->branch_id;

        $orders = Order::with(['items.product', 'table', 'waiter'])
            ->whereIn('status', ['pending', 'confirmed'])
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->latest()
            ->get()
            ->map(fn ($o) => [
                'id' => $o->id,
                'order_number' => $o->order_number,
                'table_name' => $o->table->name ?? 'Takeaway',
                'waiter_name' => $o->waiter->name ?? 'N/A',
                'items_count' => $o->items->count(),
                'total' => $o->items->sum('subtotal'),
                'status' => $o->status,
                'items' => $o->items->map(fn ($i) => [
                    'id' => $i->product_id,
                    'order_item_id' => $i->id,
                    'name' => $i->product->name ?? 'N/A',
                    'selling_price' => (float) $i->price,
                    'qty' => $i->quantity,
                    'stock' => $i->product->current_stock ?? 0,
                    'status' => $i->status,
                    'notes' => $i->notes,
                ]),
                'created_at' => $o->created_at->format('H:i'),
            ]);

        return response()->json(['orders' => $orders]);
    }

    public function acceptOrder(Order $order)
    {
        try {
            $order->update([
                'status' => 'confirmed',
                'received_at' => now(),
            ]);

            return response()->json(['success' => true, 'message' => 'Order accepted']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function markItemUnavailable(Request $request)
    {
        $request->validate([
            'order_item_id' => 'required|exists:order_items,id',
            'rejection_reason' => 'required|string|max:255',
        ]);

        try {
            $item = OrderItem::findOrFail($request->order_item_id);
            $item->update([
                'status' => 'cancelled',
                'rejection_reason' => $request->rejection_reason,
            ]);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function hold(Request $request)
    {
        try {
            $bill = Bill::create([
                'bill_number' => Bill::generateBillNumber(),
                'items_data' => json_encode($request->items),
                'customer_id' => $request->customer_id,
                'discount' => $request->discount ?? 0,
                'discount_type' => $request->discount_type ?? 'percentage',
                'status' => 'hold',
                'cashier_id' => auth()->id(),
                'branch_id' => auth()->user()->branch_id,
            ]);

            return response()->json(['success' => true, 'bill_id' => $bill->id]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function payment(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'payment_method' => 'required|string',
            'total' => 'required|numeric',
        ]);

        try {
            DB::beginTransaction();

            $paidAmount = $request->amount_received ?? $request->total;
            $changeAmount = max(0, $paidAmount - $request->total);

            $bill = Bill::create([
                'bill_number' => Bill::generateBillNumber(),
                'order_id' => $request->order_id,
                'customer_id' => $request->customer_id,
                'discount_value' => $request->discount ?? 0,
                'discount_type' => $request->discount_type ?? 'percentage',
                'subtotal' => collect($request->items)->sum(fn ($i) => $i['price'] * $i['qty']),
                'total_amount' => $request->total,
                'paid_amount' => $paidAmount,
                'change_amount' => $changeAmount,
                'payment_method' => $request->payment_method,
                'payment_status' => 'paid',
                'cashier_id' => auth()->id(),
                'branch_id' => auth()->user()->branch_id,
            ]);

            foreach ($request->items as $item) {
                BillItem::create([
                    'bill_id' => $bill->id,
                    'product_id' => $item['id'],
                    'quantity' => $item['qty'],
                    'price' => $item['price'],
                    'subtotal' => $item['price'] * $item['qty'],
                ]);
            }

            if ($request->order_id) {
                $order = Order::with('items')->find($request->order_id);

                if ($request->has('billed_item_ids') && count($request->billed_item_ids) > 0) {
                    OrderItem::whereIn('id', $request->billed_item_ids)->update(['status' => 'served']);

                    $pendingItems = $order->items()->whereNotIn('status', ['served', 'cancelled'])->count();
                    if ($pendingItems === 0) {
                        $order->update(['status' => 'completed', 'completed_at' => now()]);
                    }
                } else {
                    $order->items()->update(['status' => 'served']);
                    $order->update(['status' => 'completed', 'completed_at' => now()]);
                }
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
