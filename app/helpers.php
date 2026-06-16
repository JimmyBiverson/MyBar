<?php

if (!function_exists('formatCurrency')) {
    function formatCurrency(float $amount, ?int $decimals = null, ?string $symbol = null): string
    {
        if (!class_exists(\App\Models\Setting::class)) {
            return number_format($amount, $decimals ?? 0);
        }

        $symbol = $symbol ?? \App\Models\Setting::get('currency_symbol', 'UGX');
        $position = \App\Models\Setting::get('currency_position', 'before');
        $decimals = $decimals ?? (int) \App\Models\Setting::get('decimal_digits', 0);
        $thSep = \App\Models\Setting::get('thousand_separator', ',');
        $decSep = \App\Models\Setting::get('decimal_separator', '.');

        $formatted = number_format($amount, $decimals, $decSep, $thSep);

        return $position === 'before'
            ? $symbol . ' ' . $formatted
            : $formatted . ' ' . $symbol;
    }
}

if (!function_exists('currencySymbol')) {
    function currencySymbol(): string
    {
        if (!class_exists(\App\Models\Setting::class)) {
            return 'UGX';
        }

        return \App\Models\Setting::get('currency_symbol', 'UGX');
    }
}
