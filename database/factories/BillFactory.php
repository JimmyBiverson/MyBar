<?php

namespace Database\Factories;

use App\Models\Bill;
use App\Models\Order;
use App\Models\Customer;
use App\Models\User;
use App\Models\Branch;
use Illuminate\Database\Eloquent\Factories\Factory;

class BillFactory extends Factory
{
    protected $model = Bill::class;

    public function definition(): array
    {
        $subtotal = $this->faker->randomFloat(2, 100, 5000);
        $taxAmount = $subtotal * 0.18; // 18% tax
        $serviceCharge = $subtotal * 0.05; // 5% service charge
        $totalAmount = $subtotal + $taxAmount + $serviceCharge;
        
        return [
            'bill_number' => 'BILL-' . now()->format('Ymd') . '-' . str_pad($this->faker->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'order_id' => Order::factory(),
            'customer_id' => Customer::factory(),
            'waiter_id' => User::factory(),
            'cashier_id' => null,
            'subtotal' => $subtotal,
            'discount_type' => null,
            'discount_value' => 0,
            'discount_amount' => 0,
            'tax_amount' => $taxAmount,
            'service_charge' => $serviceCharge,
            'total_amount' => $totalAmount,
            'paid_amount' => $totalAmount,
            'change_amount' => 0,
            'payment_method' => $this->faker->randomElement(['cash', 'mobile_money', 'card']),
            'mobile_provider' => $this->faker->randomElement(['mtn', 'airtel']),
            'reference_number' => $this->faker->optional()->numerify('##########'),
            'payment_status' => 'paid',
            'notes' => $this->faker->optional()->sentence,
            'branch_id' => Branch::factory(),
            'processed_by_role' => 'waiter',
        ];
    }
}