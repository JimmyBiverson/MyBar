<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use App\Models\Table;
use App\Models\Customer;
use App\Models\Branch;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'order_number' => 'ORD-' . now()->format('Ymd') . '-' . str_pad($this->faker->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'table_id' => Table::factory(),
            'waiter_id' => User::factory(),
            'customer_id' => Customer::factory(),
            'status' => $this->faker->randomElement(['pending', 'confirmed', 'preparing', 'ready', 'served', 'completed']),
            'order_type' => $this->faker->randomElement(['dine_in', 'takeaway']),
            'notes' => $this->faker->optional()->sentence,
            'branch_id' => Branch::factory(),
        ];
    }
}