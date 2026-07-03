<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['key' => 'business_name', 'value' => 'MyBar Restaurant', 'group_name' => 'general'],
            ['key' => 'business_tagline', 'value' => 'Good Drinks, Great Vibes', 'group_name' => 'general'],
            ['key' => 'business_email', 'value' => 'info@mybar.com', 'group_name' => 'general'],
            ['key' => 'business_phone', 'value' => '+256-700-000000', 'group_name' => 'general'],
            ['key' => 'business_address', 'value' => '123 Main Street, Kampala', 'group_name' => 'general'],
            ['key' => 'currency', 'value' => 'UGX', 'group_name' => 'general'],
            ['key' => 'currency_symbol', 'value' => 'UGX', 'group_name' => 'general'],
            ['key' => 'currency_position', 'value' => 'before', 'group_name' => 'general'],
            ['key' => 'decimal_digits', 'value' => '0', 'group_name' => 'general'],
            ['key' => 'thousand_separator', 'value' => ',', 'group_name' => 'general'],
            ['key' => 'decimal_separator', 'value' => '.', 'group_name' => 'general'],
            ['key' => 'timezone', 'value' => 'Africa/Kampala', 'group_name' => 'general'],
            ['key' => 'date_format', 'value' => 'd/m/Y', 'group_name' => 'general'],
            ['key' => 'default_language', 'value' => 'en', 'group_name' => 'general'],
            ['key' => 'tax_rate', 'value' => '18', 'group_name' => 'billing'],
            ['key' => 'tax_label', 'value' => 'VAT', 'group_name' => 'billing'],
            ['key' => 'service_charge_rate', 'value' => '5', 'group_name' => 'billing'],
            ['key' => 'receipt_footer', 'value' => 'Thank you for your visit! Come again!', 'group_name' => 'receipt'],
            ['key' => 'receipt_show_tax', 'value' => '1', 'group_name' => 'receipt'],
            ['key' => 'receipt_show_logo', 'value' => '1', 'group_name' => 'receipt'],
            ['key' => 'low_stock_threshold', 'value' => '10', 'group_name' => 'inventory'],
            ['key' => 'medium_stock_threshold', 'value' => '20', 'group_name' => 'inventory'],
            ['key' => 'enable_negative_stock', 'value' => '0', 'group_name' => 'inventory'],
            ['key' => 'auto_lock_minutes', 'value' => '30', 'group_name' => 'general'],
        ];

        foreach ($settings as $setting) {
            Setting::firstOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
