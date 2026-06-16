<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'group_name',
    ];

    protected static array $settingsCache = [];

    public static function get(string $key, mixed $default = null): mixed
    {
        if (!isset(static::$settingsCache[$key])) {
            $setting = static::where('key', $key)->first();
            static::$settingsCache[$key] = $setting ? $setting->value : $default;
        }

        return static::$settingsCache[$key];
    }

    public static function clearCache(): void
    {
        static::$settingsCache = [];
    }
}
