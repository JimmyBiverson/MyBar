<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Spirits', 'slug' => 'spirits', 'description' => 'Whiskey, vodka, gin, and other distilled beverages'],
            ['name' => 'Beer', 'slug' => 'beer', 'description' => 'Local and imported beers and ales'],
            ['name' => 'Wine', 'slug' => 'wine', 'description' => 'Red, white, and sparkling wines'],
            ['name' => 'Soft Drinks', 'slug' => 'soft-drinks', 'description' => 'Carbonated and non-carbonated non-alcoholic beverages'],
            ['name' => 'Cocktails', 'slug' => 'cocktails', 'description' => 'Mixed alcoholic and non-alcoholic cocktails'],
            ['name' => 'Food', 'slug' => 'food', 'description' => 'Main dishes and meals'],
            ['name' => 'Snacks', 'slug' => 'snacks', 'description' => 'Light bites and finger foods'],
            ['name' => 'Juice', 'slug' => 'juice', 'description' => 'Freshly squeezed and bottled fruit juices'],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }
    }
}
