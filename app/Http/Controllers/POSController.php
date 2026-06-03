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
use App\Models\StockMovement;
use App\Models\Table;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class POSController extends Controller
{
    public function index(Request $request)
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

        $taxRate = (float) ($settings['tax_rate'] ?? 0);
        $serviceChargeRate = (float) ($settings['service_charge_rate'] ?? 0);

        $preloadOrder = null;
        if ($request->filled('order_id')) {
            $order = Order::with(['items.product', 'table', 'waiter', 'customer'])
                ->find($request->order_id);
            if ($order && !in_array($order->status, ['completed', 'cancelled'])) {
                $preloadOrder = [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'table_name' => $order->table->name ?? 'Takeaway',
                    'waiter_name' => $order->waiter->name ?? 'N/A',
                    'customer_name' => $order->customer->name ?? null,
                    'customer_id' => $order->customer_id,
                    'items_count' => $order->items->count(),
                    'total' => $order->items->sum('subtotal'),
                    'status' => $order->status,
                    'items' => $order->items->map(fn ($i) => [
                        'id' => $i->product_id,
                        'order_item_id' => $i->id,
                        'name' => $i->product->name ?? 'N/A',
                        'selling_price' => (float) $i->price,
                        'qty' => $i->quantity,
                        'stock' => $i->product->current_stock ?? 0,
                        'status' => $i->status,
                        'notes' => $i->notes,
                    ]),
                    'created_at' => $order->created_at->format('H:i'),
                ];
            }
        }

        return view('pos.index', compact('products', 'categories', 'tables', 'customers', 'settings', 'taxRate', 'serviceChargeRate', 'preloadOrder'));
    }

    public function pendingOrders()
    {
        $branchId = auth()->user()->branch_id;

        $orders = Order::with(['items.product', 'table', 'waiter', 'customer', 'bill'])
            ->where(function ($q) {
                $q->whereIn('status', ['pending', 'confirmed', 'preparing', 'ready', 'served'])
                  ->orWhereHas('bill', fn ($q) => $q->where('payment_status', 'unpaid'));
            })
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->latest()
            ->get()
            ->map(fn ($o) => [
                'id' => $o->id,
                'order_number' => $o->order_number,
                'table_name' => $o->table->name ?? 'Takeaway',
                'waiter_name' => $o->waiter->name ?? 'N/A',
                'customer_name' => $o->customer->name ?? null,
                'customer_id' => $o->customer_id,
                'items_count' => $o->items->count(),
                'total' => $o->items->sum('subtotal'),
                'status' => $o->status,
                'bill_requested' => $o->bill_requested,
                'payment_status' => $o->payment_status,
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

    public function pendingCount()
    {
        $branchId = auth()->user()->branch_id;

        $count = Order::where(function ($q) {
                $q->whereIn('status', ['pending', 'confirmed', 'preparing', 'ready', 'served'])
                  ->orWhereHas('bill', fn ($q) => $q->where('payment_status', 'unpaid'));
            })
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->count();

        return response()->json(['count' => $count]);
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
            $branchId = auth()->user()->branch_id;
            $item = OrderItem::where('id', $request->order_item_id)
                ->whereHas('order', fn ($q) => $q->when($branchId, fn ($q) => $q->where('branch_id', $branchId)))
                ->first();

            if (!$item) {
                return response()->json(['success' => false, 'message' => 'Order item not found or unauthorized'], 404);
            }

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
        $request->validate([
            'items' => 'required|array|min:1',
        ]);

        try {
            $bill = Bill::create([
                'bill_number' => Bill::generateBillNumber(),
                'items_data' => json_encode($request->items),
                'customer_id' => $request->customer_id,
                'discount_value' => $request->discount ?? 0,
                'discount_type' => $request->discount_type ?? 'percentage',
                'payment_status' => 'hold',
                'cashier_id' => auth()->id(),
                'branch_id' => auth()->user()->branch_id,
            ]);

            return response()->json(['success' => true, 'bill_id' => $bill->id]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function resumeHold(Bill $bill)
    {
        try {
            $branchId = auth()->user()->branch_id;
            if ($branchId && $bill->branch_id !== $branchId) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            $items = json_decode($bill->items_data, true);

            return response()->json([
                'success' => true,
                'items' => $items,
                'customer_id' => $bill->customer_id,
                'discount' => $bill->discount_value,
                'discount_type' => $bill->discount_type ?? 'percentage',
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function payment(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|integer|exists:products,id',
            'items.*.qty' => 'required|numeric|min:0.01',
            'items.*.price' => 'required|numeric|min:0',
            'payment_method' => 'required|string',
            'total' => 'required|numeric',
            'mobile_provider' => 'nullable|string',
            'reference_number' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $paidAmount = $request->amount_received ?? $request->total;
            $changeAmount = max(0, $paidAmount - $request->total);
            $subtotal = collect($request->items)->sum(fn ($i) => $i['price'] * $i['qty']);
            $discountValue = (float) ($request->discount ?? 0);
            $discountType = $request->discount_type ?? 'percentage';
            $discountAmount = $discountType === 'percentage'
                ? $subtotal * ($discountValue / 100)
                : $discountValue;
            $afterDiscount = $subtotal - $discountAmount;
            $taxAmount = $afterDiscount * ((float) ($request->tax_rate ?? 0) / 100);
            $serviceCharge = $afterDiscount * ((float) ($request->service_charge_rate ?? 0) / 100);

            // Check if there is an existing unpaid bill for this order
            $bill = null;
            if ($request->order_id) {
                $bill = Bill::where('order_id', $request->order_id)
                    ->where('payment_status', 'unpaid')
                    ->first();
            }

            if ($bill) {
                $bill->update([
                    'customer_id' => $request->customer_id,
                    'discount_value' => $discountValue,
                    'discount_type' => $discountType,
                    'discount_amount' => $discountAmount,
                    'subtotal' => $subtotal,
                    'tax_amount' => $taxAmount,
                    'service_charge' => $serviceCharge,
                    'total_amount' => $request->total,
                    'paid_amount' => $paidAmount,
                    'change_amount' => $changeAmount,
                    'payment_method' => $request->payment_method,
                    'mobile_provider' => $request->mobile_provider,
                    'reference_number' => $request->reference_number,
                    'payment_status' => 'paid',
                    'cashier_id' => auth()->id(),
                    'processed_by_role' => 'cashier',
                ]);
                $bill->items()->delete();
            } else {
                $bill = Bill::create([
                    'bill_number' => Bill::generateBillNumber(),
                    'order_id' => $request->order_id,
                    'customer_id' => $request->customer_id,
                    'discount_value' => $discountValue,
                    'discount_type' => $discountType,
                    'discount_amount' => $discountAmount,
                    'subtotal' => $subtotal,
                    'tax_amount' => $taxAmount,
                    'service_charge' => $serviceCharge,
                    'total_amount' => $request->total,
                    'paid_amount' => $paidAmount,
                    'change_amount' => $changeAmount,
                    'payment_method' => $request->payment_method,
                    'mobile_provider' => $request->mobile_provider,
                    'reference_number' => $request->reference_number,
                    'payment_status' => 'paid',
                    'cashier_id' => auth()->id(),
                    'processed_by_role' => 'cashier',
                    'branch_id' => auth()->user()->branch_id,
                ]);
            }

            foreach ($request->items as $item) {
                $product = Product::find($item['id']);
                if (!$product) {
                    throw new \Exception('Product not found: ID ' . $item['id']);
                }

                if ($product->current_stock < $item['qty']) {
                    throw new \Exception('Insufficient stock for ' . $product->name . ': only ' . $product->current_stock . ' available');
                }

                BillItem::create([
                    'bill_id' => $bill->id,
                    'product_id' => $item['id'],
                    'quantity' => $item['qty'],
                    'price' => $item['price'],
                    'subtotal' => $item['price'] * $item['qty'],
                ]);

                $product->decrement('current_stock', $item['qty']);
                $product->decrement('stock_value', (float) $product->cost_price * (float) $item['qty']);

                StockMovement::create([
                    'product_id' => $item['id'],
                    'quantity' => $item['qty'],
                    'type' => 'out',
                    'reference_type' => 'bill',
                    'reference_id' => $bill->id,
                    'notes' => 'Sale #' . $bill->bill_number,
                    'created_by' => auth()->id(),
                    'branch_id' => auth()->user()->branch_id,
                ]);
            }

            if ($request->order_id) {
                $order = Order::with('items')->find($request->order_id);

                $billedItemIds = $request->input('billed_item_ids');
                if (!empty($billedItemIds)) {
                    OrderItem::whereIn('id', $billedItemIds)->update(['status' => 'served']);

                    $pendingItems = $order->items()->whereNotIn('status', ['served', 'cancelled'])->count();
                    if ($pendingItems === 0) {
                        $order->update(['status' => 'completed', 'completed_at' => now()]);
                        if ($order->table_id) {
                            Table::where('id', $order->table_id)->update(['status' => 'available']);
                        }
                    }
                } else {
                    $order->items()->update(['status' => 'served']);
                    $order->update(['status' => 'completed', 'completed_at' => now()]);
                    if ($order->table_id) {
                        Table::where('id', $order->table_id)->update(['status' => 'available']);
                    }
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
