<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissionGroups = [
            'Dashboard' => ['dashboard.view'],
            'POS' => ['pos.access', 'pos.hold', 'pos.split', 'pos.refund'],
            'Orders' => ['orders.view', 'orders.create', 'orders.edit', 'orders.delete', 'orders.update_status'],
            'Bills' => ['bills.view', 'bills.create', 'bills.edit', 'bills.delete', 'bills.print'],
            'Payments' => ['payments.view', 'payments.create', 'payments.refund'],
            'Products' => ['products.view', 'products.create', 'products.edit', 'products.delete'],
            'Categories' => ['categories.view', 'categories.create', 'categories.edit', 'categories.delete'],
            'Inventory' => ['inventory.view', 'inventory.create', 'inventory.edit', 'inventory.delete', 'inventory.adjust'],
            'Customers' => ['customers.view', 'customers.create', 'customers.edit', 'customers.delete'],
            'Suppliers' => ['suppliers.view', 'suppliers.create', 'suppliers.edit', 'suppliers.delete'],
            'Purchases' => ['purchases.view', 'purchases.create', 'purchases.edit', 'purchases.delete'],
            'Expenses' => ['expenses.view', 'expenses.create', 'expenses.edit', 'expenses.delete'],
            'Tables' => ['tables.view', 'tables.create', 'tables.edit', 'tables.delete'],
            'Users' => ['users.view', 'users.create', 'users.edit', 'users.delete'],
            'Roles' => ['roles.view', 'roles.create', 'roles.edit', 'roles.delete'],
            'Reports' => ['reports.view', 'reports.export', 'reports.print'],
            'Settings' => ['settings.view', 'settings.edit', 'settings.backup'],
            'Activity' => ['activity.logs'],
        ];

        $allPermissions = [];

        foreach ($permissionGroups as $group => $permissions) {
            foreach ($permissions as $permissionName) {
                $permission = Permission::firstOrCreate(
                    ['name' => $permissionName],
                    ['guard_name' => 'web']
                );
                $allPermissions[] = $permission;
            }
        }

        $permissionIds = collect($allPermissions)->pluck('id')->toArray();

        $superAdmin = Role::where('name', 'Super Admin')->first();
        $superAdmin->permissions()->sync(
            collect($permissionIds)->mapWithKeys(fn ($id) => [$id => ['permission_type' => 'allow']])->toArray()
        );

        $managerExcluded = ['users.view', 'users.create', 'users.edit', 'users.delete', 'roles.view', 'roles.create', 'roles.edit', 'roles.delete'];

        $manager = Role::where('name', 'Manager')->first();
        $managerPermissions = Permission::whereNotIn('name', $managerExcluded)->pluck('id');
        $manager->permissions()->sync(
            $managerPermissions->mapWithKeys(fn ($id) => [$id => ['permission_type' => 'allow']])->toArray()
        );

        $cashierPermissions = Permission::whereIn('name', [
            'dashboard.view',
            'pos.access', 'pos.hold', 'pos.split', 'pos.refund',
            'orders.view', 'orders.create', 'orders.update_status',
            'bills.view', 'bills.create', 'bills.print',
            'payments.view', 'payments.create',
            'customers.view', 'customers.create', 'customers.edit',
            'tables.view',
            'reports.view',
        ])->pluck('id');

        $cashier = Role::where('name', 'Cashier')->first();
        $cashier->permissions()->sync(
            $cashierPermissions->mapWithKeys(fn ($id) => [$id => ['permission_type' => 'allow']])->toArray()
        );
    }
}
