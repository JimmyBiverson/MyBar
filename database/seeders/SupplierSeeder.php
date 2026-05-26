<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        $suppliers = [
            [
                'name' => 'Kampala Beverages Ltd',
                'contact_person' => 'Robert Kato',
                'phone' => '+256-712-111111',
                'email' => 'info@kampalabeverages.com',
                'address' => 'Plot 10, Industrial Area',
                'city' => 'Kampala',
                'branch_id' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Uganda Breweries Supply',
                'contact_person' => 'Alice Nanyonjo',
                'phone' => '+256-713-222222',
                'email' => 'orders@ugandabreweries.com',
                'address' => 'Block 5, Luzira',
                'city' => 'Kampala',
                'branch_id' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Fresh Foods Uganda',
                'contact_person' => 'Joseph Okello',
                'phone' => '+256-714-333333',
                'email' => 'sales@freshfoodsug.com',
                'address' => 'Suite 3, Nakawa',
                'city' => 'Kampala',
                'branch_id' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Prime Spirits Importers',
                'contact_person' => 'Catherine Namugenyi',
                'phone' => '+256-715-444444',
                'email' => 'info@primespirits.com',
                'address' => 'Plot 15, Entebbe Road',
                'city' => 'Kampala',
                'branch_id' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'City Snacks Distributors',
                'contact_person' => 'Samuel Muwonge',
                'phone' => '+256-716-555555',
                'email' => 'orders@citysnacks.co.ug',
                'address' => 'House 20, Wandegeya',
                'city' => 'Kampala',
                'branch_id' => 1,
                'is_active' => true,
            ],
        ];

        foreach ($suppliers as $supplier) {
            Supplier::firstOrCreate(
                ['email' => $supplier['email']],
                $supplier
            );
        }
    }
}
