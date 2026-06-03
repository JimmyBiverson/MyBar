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

    public static function get(string $key, mixed $default = null): mixed
    {
        static $cache = [];

        if (!isset($cache[$key])) {
            $setting = static::where('key', $key)->first();
            $cache[$key] = $setting ? $setting->value : $default;
        }

        return $cache[$key];
    }
}
