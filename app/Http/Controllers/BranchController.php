<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BranchController extends Controller
{
    public function index(Request $request)
    {
        $branches = Branch::when($request->search, fn ($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->orderBy('name')
            ->paginate(15);

        return view('branches.index', compact('branches'));
    }

    public function create()
    {
        return view('branches.form');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:branches',
            'location' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255|unique:branches',
            'city' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        try {
            Branch::create($request->only([
                'name', 'location', 'address', 'phone', 'email', 'city', 'is_active',
            ]));

            return redirect()->route('branches.index')
                ->with('success', 'Branch created successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Failed to create branch.');
        }
    }

    public function edit(Branch $branch)
    {
        return view('branches.form', compact('branch'));
    }

    public function update(Request $request, Branch $branch)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:branches,name,' . $branch->id,
            'location' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255|unique:branches,email,' . $branch->id,
            'city' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        try {
            $branch->update($request->only([
                'name', 'location', 'address', 'phone', 'email', 'city', 'is_active',
            ]));

            return redirect()->route('branches.index')
                ->with('success', 'Branch updated successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Failed to update branch.');
        }
    }

    public function switchBranch(Branch $branch)
    {
        $user = Auth::user();
        if ($user->branch_id && $user->branch_id !== $branch->id && !$user->isAdmin()) {
            return back()->with('error', 'You are not authorized to switch to this branch.');
        }

        $user->update(['branch_id' => $branch->id]);
        session(['branch_name' => $branch->name]);

        return back()->with('success', 'Switched to ' . $branch->name);
    }

    public function destroy(Branch $branch)
    {
        try {
            $branch->delete();
            return redirect()->route('branches.index')
                ->with('success', 'Branch deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Cannot delete branch with associated records.');
        }
    }
}
