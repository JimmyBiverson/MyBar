<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\Branch;
use Illuminate\Http\Request;

class BatchController extends Controller
{
    public function index(Request $request)
    {
        $query = Batch::with(['product', 'supplier', 'branch']);

        if ($search = $request->search) {
            $query->where(function ($q) use ($search) {
                $q->where('batch_no', 'like', "%{$search}%")
                    ->orWhereHas('product', fn ($pq) => $pq->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->product_id) {
            $query->where('product_id', $request->product_id);
        }

        if ($branchId = auth()->user()->branch_id) {
            $query->where('branch_id', $branchId);
        }

        $batches = $query->latest()->paginate(15);

        return view('batches.index', compact('batches'));
    }

    public function create()
    {
        $products = Product::where('is_active', true)->get();
        $suppliers = Supplier::all();
        $purchases = Purchase::all();

        return view('batches.form', compact('products', 'suppliers', 'purchases'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'batch_no' => 'required|string|max:255|unique:batches',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric|min:0',
            'cost_price' => 'required|numeric|min:0',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'purchase_id' => 'nullable|exists:purchases,id',
            'expiry_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        try {
            Batch::create([
                'batch_no' => $request->batch_no,
                'product_id' => $request->product_id,
                'quantity' => $request->quantity,
                'remaining' => $request->quantity,
                'cost_price' => $request->cost_price,
                'supplier_id' => $request->supplier_id,
                'purchase_id' => $request->purchase_id,
                'expiry_date' => $request->expiry_date,
                'notes' => $request->notes,
                'branch_id' => $request->branch_id ?? auth()->user()->branch_id,
                'created_by' => auth()->id(),
            ]);

            return redirect()->route('batches.index')
                ->with('success', 'Batch created successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Failed to create batch: ' . $e->getMessage());
        }
    }

    public function edit(Batch $batch)
    {
        $products = Product::where('is_active', true)->get();
        $suppliers = Supplier::all();
        $purchases = Purchase::all();

        return view('batches.form', compact('batch', 'products', 'suppliers', 'purchases'));
    }

    public function update(Request $request, Batch $batch)
    {
        $request->validate([
            'batch_no' => 'required|string|max:255|unique:batches,batch_no,' . $batch->id,
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric|min:0',
            'remaining' => 'required|numeric|min:0',
            'cost_price' => 'required|numeric|min:0',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'purchase_id' => 'nullable|exists:purchases,id',
            'expiry_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        try {
            $batch->update($request->only([
                'batch_no', 'product_id', 'quantity', 'remaining', 'cost_price',
                'supplier_id', 'purchase_id', 'expiry_date', 'notes',
            ]));

            return redirect()->route('batches.index')
                ->with('success', 'Batch updated successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Failed to update batch.');
        }
    }

    public function destroy(Batch $batch)
    {
        try {
            $batch->delete();
            return redirect()->route('batches.index')
                ->with('success', 'Batch deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete batch.');
        }
    }
}
