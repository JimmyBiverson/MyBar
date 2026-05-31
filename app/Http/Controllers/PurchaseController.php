<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    public function index(Request $request)
    {
        $query = Purchase::with('supplier', 'createdBy', 'branch')
            ->where('branch_id', auth()->user()?->branch_id);

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('reference_no', 'like', '%' . $request->search . '%')
                    ->orWhereHas('supplier', fn($s) => $s->where('name', 'like', '%' . $request->search . '%'));
            });
        }

        $purchases = $query->latest()->paginate(20);
        return view('purchases.index', compact('purchases'));
    }

    public function create()
    {
        $suppliers = Supplier::where('branch_id', auth()->user()?->branch_id)->where('is_active', true)->get();
        $products = Product::where('branch_id', auth()->user()?->branch_id)->where('is_active', true)->get();
        return view('purchases.create', compact('suppliers', 'products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'nullable|exists:suppliers,id',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.cost_price' => 'required|numeric|min:0',
        ]);

        try {
            DB::transaction(function () use ($validated, $request) {
                $totalAmount = collect($validated['items'])->sum(fn($item) => $item['quantity'] * $item['cost_price']);

                $purchase = Purchase::create([
                    'reference_no' => 'PO-' . now()->format('Ymd') . '-' . str_pad(Purchase::count() + 1, 4, '0', STR_PAD_LEFT),
                    'supplier_id' => $validated['supplier_id'] ?? null,
                    'total_amount' => $totalAmount,
                    'paid_amount' => 0,
                    'status' => 'pending',
                    'notes' => $validated['notes'] ?? null,
                    'created_by' => auth()->id(),
                    'branch_id' => auth()->user()?->branch_id,
                ]);

                foreach ($validated['items'] as $item) {
                    PurchaseItem::create([
                        'purchase_id' => $purchase->id,
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'cost_price' => $item['cost_price'],
                        'subtotal' => $item['quantity'] * $item['cost_price'],
                    ]);

                    $product = Product::findOrFail($item['product_id']);
                    $product->increment('current_stock', $item['quantity']);
                    $product->increment('stock_value', $item['quantity'] * $item['cost_price']);

                    StockMovement::create([
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'type' => 'in',
                        'reference_type' => 'purchase',
                        'reference_id' => $purchase->id,
                        'notes' => 'Purchase #' . $purchase->reference_no,
                        'created_by' => auth()->id(),
                        'branch_id' => auth()->user()?->branch_id,
                    ]);
                }
            });

            return redirect()->route('purchases.index')->with('success', 'Purchase created successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to create purchase: ' . $e->getMessage())->withInput();
        }
    }

    public function edit(Purchase $purchase)
    {
        $suppliers = Supplier::where('branch_id', auth()->user()?->branch_id)->where('is_active', true)->get();
        $products = Product::where('branch_id', auth()->user()?->branch_id)->where('is_active', true)->get();
        $purchase->load('items.product');
        return view('purchases.edit', compact('purchase', 'suppliers', 'products'));
    }

    public function update(Request $request, Purchase $purchase)
    {
        $validated = $request->validate([
            'supplier_id' => 'nullable|exists:suppliers,id',
            'status' => 'required|in:pending,received,cancelled',
            'notes' => 'nullable|string',
        ]);

        try {
            $purchase->update($validated);

            if ($request->status === 'received' && $purchase->status !== 'received') {
                foreach ($purchase->items as $item) {
                    $product = Product::findOrFail($item->product_id);
                    $product->increment('current_stock', $item->quantity);
                    $product->increment('stock_value', $item->quantity * $item->cost_price);

                    StockMovement::create([
                        'product_id' => $item->product_id,
                        'quantity' => $item->quantity,
                        'type' => 'in',
                        'reference_type' => 'purchase',
                        'reference_id' => $purchase->id,
                        'notes' => 'Purchase received #' . $purchase->reference_no,
                        'created_by' => auth()->id(),
                        'branch_id' => auth()->user()?->branch_id,
                    ]);
                }
            }

            return redirect()->route('purchases.index')->with('success', 'Purchase updated successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update purchase: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy(Purchase $purchase)
    {
        try {
            DB::transaction(function () use ($purchase) {
                foreach ($purchase->items as $item) {
                    Product::where('id', $item->product_id)->decrement('current_stock', $item->quantity);
                }
                $purchase->items()->delete();
                $purchase->delete();
            });

            return redirect()->route('purchases.index')->with('success', 'Purchase deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete purchase: ' . $e->getMessage());
        }
    }
}
