<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Category;
use App\Models\Unit;
use App\Models\Branch;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $costPrice = $this->faker->randomFloat(2, 10, 500);
        $sellingPrice = $costPrice * $this->faker->randomFloat(2, 1.2, 3.0); // 20% to 300% markup
        
        return [
            'name' => $this->faker->words(2, true) . ' ' . $this->faker->randomElement(['Drink', 'Food', 'Snack']),
            'description' => $this->faker->sentence,
            'category_id' => Category::factory(),
            'unit_id' => Unit::factory(),
            'cost_price' => $costPrice,
            'selling_price' => $sellingPrice,
            'current_stock' => $this->faker->numberBetween(0, 100),
            'reorder_level' => $this->faker->numberBetween(5, 20),
            'tax_method' => $this->faker->randomElement(['inclusive', 'exclusive']),
            'tax_rate' => $this->faker->randomElement([0, 18, 5]), // 0%, 18%, or 5%
            'is_active' => true,
            'branch_id' => Branch::factory(),
            'stock_value' => $costPrice * $this->faker->numberBetween(0, 100),
        ];
    }
}