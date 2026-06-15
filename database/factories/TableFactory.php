<?php

namespace Database\Factories;

use App\Models\Table;
use App\Models\Branch;
use Illuminate\Database\Eloquent\Factories\Factory;

class TableFactory extends Factory
{
    protected $model = Table::class;

    public function definition(): array
    {
        return [
            'name' => 'Table ' . $this->faker->numberBetween(1, 50),
            'capacity' => $this->faker->numberBetween(2, 8),
            'status' => $this->faker->randomElement(['available', 'occupied', 'reserved']),
            'branch_id' => Branch::factory(),
        ];
    }
}