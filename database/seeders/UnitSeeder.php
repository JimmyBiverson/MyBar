<?php

namespace Database\Seeders;

use App\Models\Unit;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    public function run(): void
    {
        $units = [
            ['name' => 'Piece', 'short_code' => 'pc'],
            ['name' => 'Bottle', 'short_code' => 'btl'],
            ['name' => 'Glass', 'short_code' => 'gls'],
            ['name' => 'Can', 'short_code' => 'can'],
            ['name' => 'Litre', 'short_code' => 'L'],
            ['name' => 'Kg', 'short_code' => 'kg'],
            ['name' => 'Gram', 'short_code' => 'g'],
            ['name' => 'Pack', 'short_code' => 'pk'],
            ['name' => 'Box', 'short_code' => 'bx'],
            ['name' => 'Shot', 'short_code' => 'sh'],
        ];

        foreach ($units as $unit) {
            Unit::firstOrCreate(
                ['name' => $unit['name']],
                $unit
            );
        }
    }
}
