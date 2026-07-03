<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Support\Facades\Storage;

class AppController extends Controller
{
    public function manifest()
    {
        $favicon = Setting::get('favicon', '/mybar_icon.png');
        $siteLogo = Setting::get('site_logo');
        $icon192 = $favicon;
        $icon512 = $siteLogo ?: $favicon;

        $mimeTypes = [
            'png' => 'image/png',
            'ico' => 'image/x-icon',
            'svg' => 'image/svg+xml',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
        ];
        $type192 = $mimeTypes[pathinfo($icon192, PATHINFO_EXTENSION)] ?? 'image/png';
        $type512 = $mimeTypes[pathinfo($icon512, PATHINFO_EXTENSION)] ?? 'image/png';

        return response()->json([
            'name' => config('app.name', 'MyBar POS'),
            'short_name' => 'MyBar',
            'start_url' => '/',
            'display' => 'standalone',
            'display_override' => ['window-controls-overlay', 'standalone', 'minimal-ui'],
            'background_color' => '#f8f9fa',
            'theme_color' => '#7367f0',
            'categories' => ['business', 'food', 'productivity'],
            'description' => 'Bar & Restaurant Point of Sale System',
            'icons' => [
                [
                    'src' => $icon192,
                    'sizes' => '192x192',
                    'type' => $type192,
                    'purpose' => 'any',
                ],
                [
                    'src' => $icon512,
                    'sizes' => '512x512',
                    'type' => $type512,
                    'purpose' => 'any maskable',
                ],
            ],
        ]);
    }

    public function downloadApk()
    {
        $path = 'apk/mybar.apk';

        if (!Storage::disk('public')->exists($path)) {
            return response()->json([
                'success' => false,
                'message' => 'APK not available yet. Please check back later.',
            ], 404);
        }

        return Storage::disk('public')->download($path, 'MyBarPOS.apk');
    }

    public function desktopShortcut()
    {
        $url = url('/');
        $name = config('app.name', 'MyBar POS');

        $favicon = Setting::get('favicon');
        $siteLogo = Setting::get('site_logo');
        $iconUrl = $favicon ? url($favicon) : ($siteLogo ? url($siteLogo) : $url . '/mybar_icon.png');

        $content = "[InternetShortcut]\r\nURL={$url}\r\nIDList=\r\nIconIndex=0\r\nIconFile={$iconUrl}\r\n";

        return response($content, 200, [
            'Content-Type' => 'application/internet-shortcut',
            'Content-Disposition' => 'attachment; filename="' . $name . '.url"',
        ]);
    }
}
