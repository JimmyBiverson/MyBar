<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $customers = Customer::when($request->search, fn ($q) => $q->where(function ($q) use ($request) {
            $q->where('name', 'like', "%{$request->search}%")
                ->orWhere('phone', 'like', "%{$request->search}%")
                ->orWhere('email', 'like', "%{$request->search}%");
        }))->orderBy('name')->paginate(15);

        return view('customers.index', compact('customers'));
    }

    public function create()
    {
        return view('customers.form');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255|unique:customers',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:255',
            'tax_id' => 'nullable|string|max:50',
            'is_active' => 'boolean',
            'branch_id' => 'nullable|exists:branches,id',
        ]);

        try {
            Customer::create($request->only([
                'name', 'phone', 'email', 'address', 'city',
                'tax_id', 'opening_balance', 'branch_id', 'is_active',
            ]));

            return redirect()->route('customers.index')
                ->with('success', 'Customer created successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Failed to create customer.');
        }
    }

    public function edit(Customer $customer)
    {
        return view('customers.form', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255|unique:customers,email,' . $customer->id,
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:255',
            'tax_id' => 'nullable|string|max:50',
            'is_active' => 'boolean',
            'branch_id' => 'nullable|exists:branches,id',
        ]);

        try {
            $customer->update($request->only([
                'name', 'phone', 'email', 'address', 'city',
                'tax_id', 'opening_balance', 'branch_id', 'is_active',
            ]));

            return redirect()->route('customers.index')
                ->with('success', 'Customer updated successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Failed to update customer.');
        }
    }

    public function destroy(Customer $customer)
    {
        try {
            $customer->delete();
            return redirect()->route('customers.index')
                ->with('success', 'Customer deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Cannot delete customer with associated records.');
        }
    }
}
