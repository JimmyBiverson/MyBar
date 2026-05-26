<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::orderBy('group_name')->orderBy('key')->get()
            ->groupBy('group_name');

        return view('settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        try {
            foreach ($request->except('_token', '_method') as $key => $value) {
                Setting::updateOrCreate(
                    ['key' => $key],
                    ['value' => $value, 'group_name' => $request->input("group_{$key}", 'general')]
                );
            }

            return redirect()->route('settings.index')
                ->with('success', 'Settings updated successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update settings: ' . $e->getMessage());
        }
    }

    public function backupDatabase()
    {
        return back()->with('info', 'Database backup feature coming soon.');
    }
}
