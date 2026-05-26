<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $spirits = Category::where('slug', 'spirits')->first()->id;
        $beer = Category::where('slug', 'beer')->first()->id;
        $softDrinks = Category::where('slug', 'soft-drinks')->first()->id;
        $juice = Category::where('slug', 'juice')->first()->id;
        $food = Category::where('slug', 'food')->first()->id;
        $snacks = Category::where('slug', 'snacks')->first()->id;

        $bottle = Unit::where('name', 'Bottle')->first()->id;
        $glass = Unit::where('name', 'Glass')->first()->id;
        $pack = Unit::where('name', 'Pack')->first()->id;

        $products = [
            [
                'name' => 'Johnnie Walker Black Label',
                'sku' => 'SP-001',
                'category_id' => $spirits,
                'unit_id' => $bottle,
                'cost_price' => 45000,
                'selling_price' => 75000,
                'wholesale_price' => 60000,
                'current_stock' => 50,
                'reorder_level' => 10,
                'description' => 'Premium blended Scotch whisky',
            ],
            [
                'name' => 'Jameson Irish Whiskey',
                'sku' => 'SP-002',
                'category_id' => $spirits,
                'unit_id' => $bottle,
                'cost_price' => 35000,
                'selling_price' => 60000,
                'wholesale_price' => 50000,
                'current_stock' => 40,
                'reorder_level' => 10,
                'description' => 'Smooth triple-distilled Irish whiskey',
            ],
            [
                'name' => 'Smirnoff Vodka',
                'sku' => 'SP-003',
                'category_id' => $spirits,
                'unit_id' => $bottle,
                'cost_price' => 20000,
                'selling_price' => 40000,
                'wholesale_price' => 30000,
                'current_stock' => 60,
                'reorder_level' => 15,
                'description' => 'Premium vodka, perfect for mixing',
            ],
            [
                'name' => 'Club Beer',
                'sku' => 'BR-001',
                'category_id' => $beer,
                'unit_id' => $bottle,
                'cost_price' => 2500,
                'selling_price' => 5000,
                'wholesale_price' => 3500,
                'current_stock' => 200,
                'reorder_level' => 50,
                'description' => 'Refreshing lager beer',
            ],
            [
                'name' => 'Nile Special Beer',
                'sku' => 'BR-002',
                'category_id' => $beer,
                'unit_id' => $bottle,
                'cost_price' => 3000,
                'selling_price' => 6000,
                'wholesale_price' => 4000,
                'current_stock' => 150,
                'reorder_level' => 50,
                'description' => 'Ugandan premium lager',
            ],
            [
                'name' => 'Coca Cola',
                'sku' => 'SD-001',
                'category_id' => $softDrinks,
                'unit_id' => $bottle,
                'cost_price' => 1500,
                'selling_price' => 3000,
                'wholesale_price' => 2000,
                'current_stock' => 300,
                'reorder_level' => 50,
                'description' => 'Classic carbonated soft drink',
            ],
            [
                'name' => 'Fresh Orange Juice',
                'sku' => 'JU-001',
                'category_id' => $juice,
                'unit_id' => $glass,
                'cost_price' => 2000,
                'selling_price' => 5000,
                'wholesale_price' => 3500,
                'current_stock' => 80,
                'reorder_level' => 20,
                'description' => 'Freshly squeezed orange juice',
            ],
            [
                'name' => 'French Fries',
                'sku' => 'FD-001',
                'category_id' => $food,
                'unit_id' => $pack,
                'cost_price' => 3000,
                'selling_price' => 8000,
                'wholesale_price' => 5000,
                'current_stock' => 100,
                'reorder_level' => 25,
                'description' => 'Crispy golden french fries',
            ],
            [
                'name' => 'Chicken Wings',
                'sku' => 'FD-002',
                'category_id' => $food,
                'unit_id' => $pack,
                'cost_price' => 8000,
                'selling_price' => 18000,
                'wholesale_price' => 12000,
                'current_stock' => 60,
                'reorder_level' => 15,
                'description' => 'Spicy grilled chicken wings',
            ],
            [
                'name' => 'Mixed Nuts',
                'sku' => 'SN-001',
                'category_id' => $snacks,
                'unit_id' => $pack,
                'cost_price' => 5000,
                'selling_price' => 10000,
                'wholesale_price' => 7000,
                'current_stock' => 75,
                'reorder_level' => 20,
                'description' => 'Assorted roasted nuts',
            ],
        ];

        foreach ($products as $product) {
            Product::firstOrCreate(
                ['sku' => $product['sku']],
                [
                    'name' => $product['name'],
                    'category_id' => $product['category_id'],
                    'unit_id' => $product['unit_id'],
                    'cost_price' => $product['cost_price'],
                    'selling_price' => $product['selling_price'],
                    'wholesale_price' => $product['wholesale_price'],
                    'current_stock' => $product['current_stock'],
                    'opening_stock' => $product['current_stock'],
                    'stock_value' => $product['cost_price'] * $product['current_stock'],
                    'reorder_level' => $product['reorder_level'],
                    'description' => $product['description'],
                    'branch_id' => 1,
                    'is_active' => true,
                ]
            );
        }
    }
}
