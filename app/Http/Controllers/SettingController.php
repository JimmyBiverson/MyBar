<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::pluck('value', 'key');

        return view('settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        try {
            if ($request->hasFile('favicon')) {
                $file = $request->file('favicon');
                $name = 'favicon.' . $file->getClientOriginalExtension();
                $file->move(public_path('uploads'), $name);
                Setting::updateOrCreate(
                    ['key' => 'favicon'],
                    ['value' => '/uploads/' . $name, 'group_name' => 'general']
                );
            }

            if ($request->hasFile('site_logo')) {
                $file = $request->file('site_logo');
                $name = 'site_logo.' . $file->getClientOriginalExtension();
                $file->move(public_path('uploads'), $name);
                Setting::updateOrCreate(
                    ['key' => 'site_logo'],
                    ['value' => '/uploads/' . $name, 'group_name' => 'general']
                );
            }

            $conversionRate = $request->input('conversion_rate');
            $newCurrency = $request->input('currency');
            $oldCurrency = Setting::get('currency', 'UGX');
            $converted = false;
            $convertedCount = 0;

            if ($newCurrency && $newCurrency !== $oldCurrency && $conversionRate && (float) $conversionRate > 0 && (float) $conversionRate !== 1.0) {
                $rate = (float) $conversionRate;
                $converted = true;

                DB::transaction(function () use ($rate, &$convertedCount) {
                    $updates = [
                        'UPDATE products SET cost_price = ROUND(cost_price * ?, 2), selling_price = ROUND(selling_price * ?, 2), wholesale_price = ROUND(wholesale_price * ?, 2), stock_value = ROUND(stock_value * ?, 2)' => [4],
                        'UPDATE order_items SET price = ROUND(price * ?, 2), subtotal = ROUND(subtotal * ?, 2)' => [2],
                        'UPDATE bills SET subtotal = ROUND(subtotal * ?, 2), discount_value = ROUND(discount_value * ?, 2), discount_amount = ROUND(discount_amount * ?, 2), tax_amount = ROUND(tax_amount * ?, 2), service_charge = ROUND(service_charge * ?, 2), total_amount = ROUND(total_amount * ?, 2), paid_amount = ROUND(paid_amount * ?, 2), change_amount = ROUND(change_amount * ?, 2)' => [8],
                        'UPDATE bill_items SET price = ROUND(price * ?, 2), subtotal = ROUND(subtotal * ?, 2)' => [2],
                        'UPDATE payments SET amount = ROUND(amount * ?, 2)' => [1],
                        'UPDATE purchases SET total_amount = ROUND(total_amount * ?, 2), paid_amount = ROUND(paid_amount * ?, 2)' => [2],
                        'UPDATE purchase_items SET cost_price = ROUND(cost_price * ?, 2), subtotal = ROUND(subtotal * ?, 2)' => [2],
                        'UPDATE expenses SET amount = ROUND(amount * ?, 2)' => [1],
                        'UPDATE batches SET cost_price = ROUND(cost_price * ?, 2)' => [1],
                    ];

                    foreach ($updates as $sql => $count) {
                        $params = array_fill(0, $count[0], $rate);
                        $affected = DB::update($sql, $params);
                        $convertedCount += $affected;
                    }
                });
            }

            $excluded = ['_token', '_method', 'favicon', 'site_logo', 'conversion_rate'];
            foreach ($request->except($excluded) as $key => $value) {
                if (is_null($value)) {
                    $value = '';
                }
                Setting::updateOrCreate(
                    ['key' => $key],
                    ['value' => $value, 'group_name' => $request->input("group_{$key}", 'general')]
                );
            }

            // Clear setting cache so fresh values load immediately
            Setting::clearCache();

            $message = 'Settings updated successfully.';
            if ($converted) {
                $message .= " Currency converted from {$oldCurrency} to {$newCurrency} (rate: {$conversionRate}). {$convertedCount} record(s) updated.";
            }

            return redirect()->route('settings.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update settings: ' . $e->getMessage());
        }
    }

    public function backupDatabase()
    {
        return back()->with('info', 'Database backup feature coming soon.');
    }
}
