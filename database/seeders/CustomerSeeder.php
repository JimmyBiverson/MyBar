<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $customers = [
            [
                'name' => 'John Mukasa',
                'phone' => '+256-701-123456',
                'email' => 'john.mukasa@email.com',
                'address' => 'Plot 45, Kololo',
                'city' => 'Kampala',
                'branch_id' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Sarah Nabatanzi',
                'phone' => '+256-702-234567',
                'email' => 'sarah.nabatanzi@email.com',
                'address' => 'Suite 12, Bugolobi',
                'city' => 'Kampala',
                'branch_id' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'David Ssempijja',
                'phone' => '+256-703-345678',
                'email' => 'david.ssempijja@email.com',
                'address' => 'House 8, Makindye',
                'city' => 'Kampala',
                'branch_id' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Grace Akello',
                'phone' => '+256-704-456789',
                'email' => 'grace.akello@email.com',
                'address' => 'Plot 23, Ntinda',
                'city' => 'Kampala',
                'branch_id' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Peter Wasswa',
                'phone' => '+256-705-567890',
                'email' => 'peter.wasswa@email.com',
                'address' => 'Block 7, Kawempe',
                'city' => 'Kampala',
                'branch_id' => 1,
                'is_active' => true,
            ],
        ];

        foreach ($customers as $customer) {
            Customer::firstOrCreate(
                ['email' => $customer['email']],
                $customer
            );
        }
    }
}
