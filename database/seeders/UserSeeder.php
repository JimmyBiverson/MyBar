<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['name' => 'Admin', 'email' => 'admin@mybar.com', 'password' => Hash::make('password123'), 'pin_code' => Hash::make('1001'), 'role' => 'Super Admin'],
            ['name' => 'Manager', 'email' => 'manager@mybar.com', 'password' => Hash::make('password123'), 'pin_code' => Hash::make('2001'), 'role' => 'Manager'],
            ['name' => 'Cashier', 'email' => 'cashier@mybar.com', 'password' => Hash::make('password123'), 'pin_code' => Hash::make('3001'), 'role' => 'Cashier'],
            ['name' => 'Waiter', 'email' => 'waiter@mybar.com', 'password' => Hash::make('password123'), 'pin_code' => Hash::make('4001'), 'role' => 'Waiter'],
            ['name' => 'Kitchen', 'email' => 'kitchen@mybar.com', 'password' => Hash::make('password123'), 'pin_code' => Hash::make('5001'), 'role' => 'Kitchen Staff'],
            ['name' => 'Store', 'email' => 'store@mybar.com', 'password' => Hash::make('password123'), 'pin_code' => Hash::make('6001'), 'role' => 'Store Keeper'],
        ];

        foreach ($users as $userData) {
            $role = Role::where('name', $userData['role'])->first();

            User::firstOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => $userData['password'],
                    'pin_code' => $userData['pin_code'],
                    'role_id' => $role->id,
                    'branch_id' => 1,
                    'is_active' => true,
                    'status' => 'active',
                ]
            );
        }
    }
}
