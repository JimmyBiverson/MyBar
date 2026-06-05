<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $query = Expense::with('createdBy');

        if ($request->from_date) {
            $query->whereDate('expense_date', '>=', $request->from_date);
        }

        if ($request->to_date) {
            $query->whereDate('expense_date', '<=', $request->to_date);
        }

        if ($request->category) {
            $query->where('category', $request->category);
        }

        if ($branchId = auth()->user()->branch_id) {
            $query->where('branch_id', $branchId);
        }

        $expenses = $query->latest()->paginate(15);

        return view('expenses.index', compact('expenses'));
    }

    public function create()
    {
        $categories = Expense::select('category')->distinct()->pluck('category');
        return view('expenses.form', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'category' => 'required|string|max:255',
            'expense_date' => 'required|date',
            'payment_method' => 'nullable|string|max:50',
            'reference_no' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:1000',
            'branch_id' => 'nullable|exists:branches,id',
        ]);

        try {
            Expense::create([
                'description' => $request->description,
                'amount' => $request->amount,
                'category' => $request->category,
                'expense_date' => $request->expense_date,
                'payment_method' => $request->payment_method,
                'reference_no' => $request->reference_no,
                'notes' => $request->notes,
                'created_by' => auth()->id(),
                'branch_id' => $request->branch_id ?? auth()->user()->branch_id,
            ]);

            return redirect()->route('expenses.index')
                ->with('success', 'Expense recorded successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Failed to record expense.');
        }
    }

    public function edit(Expense $expense)
    {
        $categories = Expense::select('category')->distinct()->pluck('category');
        return view('expenses.form', compact('expense', 'categories'));
    }

    public function update(Request $request, Expense $expense)
    {
        $request->validate([
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'category' => 'required|string|max:255',
            'expense_date' => 'required|date',
            'payment_method' => 'nullable|string|max:50',
            'reference_no' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            $expense->update($request->only([
                'description', 'amount', 'category', 'expense_date',
                'payment_method', 'reference_no', 'notes',
            ]));

            return redirect()->route('expenses.index')
                ->with('success', 'Expense updated successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Failed to update expense.');
        }
    }

    public function destroy(Expense $expense)
    {
        try {
            $expense->delete();
            return redirect()->route('expenses.index')
                ->with('success', 'Expense deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete expense.');
        }
    }
}
