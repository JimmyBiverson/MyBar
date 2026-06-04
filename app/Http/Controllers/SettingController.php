<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

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

            foreach ($request->except('_token', '_method', 'favicon', 'site_logo') as $key => $value) {
                if (is_null($value)) {
                    $value = '';
                }
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
