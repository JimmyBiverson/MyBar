<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['name' => 'Super Admin', 'description' => 'Has full system access and control over all features', 'guard_name' => 'web'],
            ['name' => 'Manager', 'description' => 'Manages daily operations and staff', 'guard_name' => 'web'],
            ['name' => 'Cashier', 'description' => 'Handles POS transactions and payments', 'guard_name' => 'web'],
            ['name' => 'Waiter', 'description' => 'Takes orders and serves customers', 'guard_name' => 'web'],
            ['name' => 'Kitchen Staff', 'description' => 'Prepares food and drinks', 'guard_name' => 'web'],
            ['name' => 'Store Keeper', 'description' => 'Manages inventory and stock levels', 'guard_name' => 'web'],
            ['name' => 'Accountant', 'description' => 'Handles financial transactions and reports', 'guard_name' => 'web'],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(
                ['name' => $role['name']],
                $role
            );
        }
    }
}
