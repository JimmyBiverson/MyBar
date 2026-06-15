<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class BillController extends Controller
{
    public function index(Request $request)
    {
        $query = Bill::with(['customer', 'waiter', 'cashier', 'order']);

        if ($request->from_date) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->to_date) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        if ($request->payment_status) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->processed_by_role) {
            $query->where('processed_by_role', $request->processed_by_role);
        }

        if ($request->search) {
            $query->where('bill_number', 'like', "%{$request->search}%");
        }

        if ($branchId = auth()->user()->branch_id) {
            $query->where('branch_id', $branchId);
        }

        $bills = $query->withCount('items')->latest()->paginate(15);

        $todayStats = Bill::whereDate('created_at', today())
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->selectRaw("COUNT(*) as total_count")
            ->selectRaw("SUM(CASE WHEN processed_by_role = 'waiter' THEN 1 ELSE 0 END) as waiter_count")
            ->selectRaw("SUM(CASE WHEN processed_by_role = 'waiter' THEN total_amount ELSE 0 END) as waiter_total")
            ->selectRaw("SUM(CASE WHEN processed_by_role = 'cashier' THEN 1 ELSE 0 END) as cashier_count")
            ->selectRaw("SUM(CASE WHEN processed_by_role = 'cashier' THEN total_amount ELSE 0 END) as cashier_total")
            ->first();

        return view('billing.index', compact('bills', 'todayStats'));
    }

    public function show(Bill $bill)
    {
        $bill->load(['items.product', 'customer', 'waiter', 'cashier', 'order', 'payments']);
        return view('billing.show', compact('bill'));
    }

    public function print(Bill $bill)
    {
        $bill->load(['items.product', 'customer', 'waiter', 'cashier', 'order']);
        $settings = Setting::whereIn('key', [
            'currency_symbol', 'currency_position',
            'site_name', 'site_address', 'site_phone',
        ])->pluck('value', 'key');

        return view('billing.print', compact('bill', 'settings'));
    }

    public function receiptContent(Bill $bill)
    {
        $bill->load(['items.product', 'customer', 'waiter', 'cashier', 'order']);
        $settings = Setting::whereIn('key', [
            'currency_symbol', 'currency_position',
            'site_name', 'site_address', 'site_phone',
        ])->pluck('value', 'key');

        $html = view('billing.receipt-partial', compact('bill', 'settings'))->render();
        return response()->json(['html' => $html]);
    }

    public function exportPdf(Bill $bill)
    {
        $bill->load(['items.product', 'customer', 'waiter', 'cashier', 'order']);
        $settings = Setting::whereIn('key', [
            'currency_symbol', 'currency_position',
            'site_name', 'site_address', 'site_phone',
        ])->pluck('value', 'key');

        $pdf = Pdf::loadView('billing.pdf', compact('bill', 'settings'));
        return $pdf->download("bill-{$bill->bill_number}.pdf");
    }
}
