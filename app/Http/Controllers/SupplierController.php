<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $suppliers = Supplier::when($request->search, fn ($q) => $q->where(function ($q) use ($request) {
            $q->where('name', 'like', "%{$request->search}%")
                ->orWhere('phone', 'like', "%{$request->search}%")
                ->orWhere('email', 'like', "%{$request->search}%");
        }))->orderBy('name')->paginate(15);

        return view('suppliers.index', compact('suppliers'));
    }

    public function create()
    {
        return view('suppliers.form');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255|unique:suppliers',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:255',
            'tax_id' => 'nullable|string|max:50',
            'is_active' => 'boolean',
            'branch_id' => 'nullable|exists:branches,id',
        ]);

        try {
            Supplier::create($request->only([
                'name', 'contact_person', 'phone', 'email', 'address',
                'city', 'tax_id', 'opening_balance', 'branch_id', 'is_active',
            ]));

            return redirect()->route('suppliers.index')
                ->with('success', 'Supplier created successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Failed to create supplier.');
        }
    }

    public function edit(Supplier $supplier)
    {
        return view('suppliers.form', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255|unique:suppliers,email,' . $supplier->id,
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:255',
            'tax_id' => 'nullable|string|max:50',
            'is_active' => 'boolean',
            'branch_id' => 'nullable|exists:branches,id',
        ]);

        try {
            $supplier->update($request->only([
                'name', 'contact_person', 'phone', 'email', 'address',
                'city', 'tax_id', 'opening_balance', 'branch_id', 'is_active',
            ]));

            return redirect()->route('suppliers.index')
                ->with('success', 'Supplier updated successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Failed to update supplier.');
        }
    }

    public function destroy(Supplier $supplier)
    {
        try {
            $supplier->delete();
            return redirect()->route('suppliers.index')
                ->with('success', 'Supplier deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Cannot delete supplier with associated records.');
        }
    }
}
