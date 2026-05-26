<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    public function index(Request $request)
    {
        $units = Unit::when($request->search, fn ($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->orderBy('name')
            ->paginate(15);

        return view('units.index', compact('units'));
    }

    public function create()
    {
        return view('units.form');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:units',
            'short_code' => 'nullable|string|max:50|unique:units',
        ]);

        try {
            Unit::create($request->only(['name', 'short_code']));
            return redirect()->route('units.index')
                ->with('success', 'Unit created successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Failed to create unit.');
        }
    }

    public function edit(Unit $unit)
    {
        return view('units.form', compact('unit'));
    }

    public function update(Request $request, Unit $unit)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:units,name,' . $unit->id,
            'short_code' => 'nullable|string|max:50|unique:units,short_code,' . $unit->id,
        ]);

        try {
            $unit->update($request->only(['name', 'short_code']));
            return redirect()->route('units.index')
                ->with('success', 'Unit updated successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Failed to update unit.');
        }
    }

    public function destroy(Unit $unit)
    {
        try {
            $unit->delete();
            return redirect()->route('units.index')
                ->with('success', 'Unit deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Cannot delete unit with associated products.');
        }
    }
}
