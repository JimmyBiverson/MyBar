<?php

namespace Database\Seeders;

use App\Models\Branch;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    public function run(): void
    {
        Branch::firstOrCreate(
            ['name' => 'Main Branch'],
            [
                'location' => 'Kampala',
                'address' => '123 Main Street, Kampala',
                'phone' => '+256-700-000000',
                'email' => 'main@mybar.com',
                'city' => 'Kampala',
                'is_active' => true,
            ]
        );
    }
}
