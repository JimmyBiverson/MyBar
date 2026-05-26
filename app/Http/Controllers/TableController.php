<?php

namespace App\Http\Controllers;

use App\Models\Table;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class TableController extends Controller
{
    public function index(Request $request)
    {
        $tables = Table::when($request->search, fn ($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($branchId = auth()->user()->branch_id, fn ($q) => $q->where('branch_id', $branchId))
            ->orderBy('name')
            ->paginate(15);

        return view('tables.index', compact('tables'));
    }

    public function create()
    {
        return view('tables.form');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'capacity' => 'required|integer|min:1',
            'status' => 'required|string|in:available,occupied,reserved,maintenance',
            'branch_id' => 'nullable|exists:branches,id',
        ]);

        try {
            $table = Table::create([
                'name' => $request->name,
                'capacity' => $request->capacity,
                'status' => $request->status,
                'qr_code' => $request->qr_code ?? Str::random(16),
                'branch_id' => $request->branch_id ?? auth()->user()->branch_id,
            ]);

            $qrCodeSvg = QrCode::format('svg')
                ->size(200)
                ->generate(url('/waiter/tables?table=' . $table->id));
            $table->update(['qr_code' => (string) $qrCodeSvg]);

            return redirect()->route('tables.index')
                ->with('success', 'Table created successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Failed to create table.');
        }
    }

    public function edit(Table $table)
    {
        return view('tables.form', compact('table'));
    }

    public function update(Request $request, Table $table)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'capacity' => 'required|integer|min:1',
            'status' => 'required|string|in:available,occupied,reserved,maintenance',
        ]);

        try {
            $table->update($request->only(['name', 'capacity', 'status']));

            $qrCodeSvg = QrCode::format('svg')
                ->size(200)
                ->generate(url('/waiter/tables?table=' . $table->id));
            $table->update(['qr_code' => (string) $qrCodeSvg]);

            return redirect()->route('tables.index')
                ->with('success', 'Table updated successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Failed to update table.');
        }
    }

    public function showQr($id)
    {
        $table = Table::findOrFail($id);
        return response($table->qr_code, 200)
            ->header('Content-Type', 'image/svg+xml');
    }

    public function destroy(Table $table)
    {
        try {
            $table->delete();
            return redirect()->route('tables.index')
                ->with('success', 'Table deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Cannot delete table with active orders.');
        }
    }
}
