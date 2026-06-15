<?php

namespace Database\Seeders;

use App\Models\Bill;
use App\Models\BillItem;
use App\Models\Expense;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\StockMovement;
use App\Models\Table as RestaurantTable;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class SampleDataSeeder extends Seeder
{
    public function run(): void
    {
        $branchId = 1;
        $waiterId = 4;
        $cashierId = 3;
        $kitchenId = 5;
        $storeId = 6;
        $adminId = 1;

        $productIds = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];

        $products = [];
        foreach ($productIds as $id) {
            $products[$id] = Product::find($id);
        }

        $this->command->info('Creating tables...');
        $this->createTables($branchId);

        $this->command->info('Creating completed orders (last 30 days)...');
        $this->createHistoricalOrders($branchId, $waiterId, $cashierId, $products);

        $this->command->info('Creating in-progress orders...');
        $this->createInProgressOrders($branchId, $waiterId, $kitchenId, $products);

        $this->command->info('Creating purchases...');
        $this->createPurchases($branchId, $storeId, $products);

        $this->command->info('Creating expenses...');
        $this->createExpenses($branchId, $adminId);

        $this->command->info('Sample data created successfully!');
    }

    private function createTables(int $branchId): void
    {
        $tables = [
            ['name' => 'Table 1', 'capacity' => 2, 'status' => 'available'],
            ['name' => 'Table 2', 'capacity' => 4, 'status' => 'available'],
            ['name' => 'Table 3', 'capacity' => 4, 'status' => 'available'],
            ['name' => 'Table 4', 'capacity' => 6, 'status' => 'available'],
            ['name' => 'Table 5', 'capacity' => 8, 'status' => 'available'],
            ['name' => 'VIP Lounge', 'capacity' => 10, 'status' => 'available'],
        ];

        foreach ($tables as $table) {
            RestaurantTable::firstOrCreate(
                ['name' => $table['name'], 'branch_id' => $branchId],
                [
                    'capacity' => $table['capacity'],
                    'status' => $table['status'],
                    'branch_id' => $branchId,
                ]
            );
        }
    }

    private function createHistoricalOrders(int $branchId, int $waiterId, int $cashierId, array $products): void
    {
        $paymentMethods = ['cash', 'mobile_money', 'card'];
        $customers = [1, 2, 3, 4, 5];

        $ordersConfig = [
            // Today's completed orders (for dashboard today stats)
            ['daysAgo' => 0, 'hour' => 10, 'customer' => 1, 'table' => 1, 'type' => 'dine_in', 'items' => [[4, 3], [6, 2]], 'method' => 'cash'],
            ['daysAgo' => 0, 'hour' => 12, 'customer' => 2, 'table' => 3, 'type' => 'dine_in', 'items' => [[8, 2], [9, 1], [6, 3]], 'method' => 'mobile_money'],
            ['daysAgo' => 0, 'hour' => 14, 'customer' => 3, 'table' => null, 'type' => 'takeaway', 'items' => [[2, 1], [5, 4]], 'method' => 'card'],
            ['daysAgo' => 0, 'hour' => 16, 'customer' => 4, 'table' => 2, 'type' => 'dine_in', 'items' => [[1, 1], [10, 2], [6, 1]], 'method' => 'cash'],
            ['daysAgo' => 0, 'hour' => 18, 'customer' => 5, 'table' => 4, 'type' => 'dine_in', 'items' => [[7, 2], [8, 1], [9, 2], [5, 6]], 'method' => 'mobile_money'],

            // Yesterday
            ['daysAgo' => 1, 'hour' => 11, 'customer' => 1, 'table' => null, 'type' => 'takeaway', 'items' => [[4, 6], [6, 3]], 'method' => 'cash'],
            ['daysAgo' => 1, 'hour' => 13, 'customer' => 3, 'table' => 5, 'type' => 'dine_in', 'items' => [[9, 2], [8, 3], [6, 4], [10, 1]], 'method' => 'card'],
            ['daysAgo' => 1, 'hour' => 19, 'customer' => 2, 'table' => 2, 'type' => 'dine_in', 'items' => [[3, 2], [5, 4], [8, 1]], 'method' => 'mobile_money'],

            // 3 days ago
            ['daysAgo' => 3, 'hour' => 12, 'customer' => 4, 'table' => 1, 'type' => 'dine_in', 'items' => [[1, 1], [4, 2], [6, 1]], 'method' => 'cash'],
            ['daysAgo' => 3, 'hour' => 20, 'customer' => 5, 'table' => 4, 'type' => 'dine_in', 'items' => [[2, 1], [9, 1], [7, 1]], 'method' => 'card'],

            // 7 days ago
            ['daysAgo' => 7, 'hour' => 14, 'customer' => 1, 'table' => 3, 'type' => 'dine_in', 'items' => [[4, 8], [5, 6], [10, 2]], 'method' => 'cash'],
            ['daysAgo' => 7, 'hour' => 18, 'customer' => 3, 'table' => null, 'type' => 'takeaway', 'items' => [[8, 4], [9, 3], [6, 5]], 'method' => 'mobile_money'],

            // 14 days ago
            ['daysAgo' => 14, 'hour' => 12, 'customer' => 2, 'table' => 5, 'type' => 'dine_in', 'items' => [[3, 1], [5, 3], [8, 2], [10, 1]], 'method' => 'card'],
            ['daysAgo' => 14, 'hour' => 19, 'customer' => 4, 'table' => 2, 'type' => 'dine_in', 'items' => [[1, 2], [7, 3]], 'method' => 'cash'],

            // 21 days ago
            ['daysAgo' => 21, 'hour' => 13, 'customer' => 5, 'table' => 1, 'type' => 'dine_in', 'items' => [[9, 2], [4, 4], [6, 2]], 'method' => 'mobile_money'],

            // 28 days ago
            ['daysAgo' => 28, 'hour' => 15, 'customer' => 1, 'table' => 4, 'type' => 'dine_in', 'items' => [[2, 1], [5, 3], [10, 1]], 'method' => 'cash'],
        ];

        $orderNumber = 0;

        foreach ($ordersConfig as $cfg) {
            $orderNumber++;
            $date = Carbon::now()->subDays($cfg['daysAgo'])->setHour($cfg['hour'])->setMinute(rand(0, 59));

            $subtotal = 0;
            $itemsData = [];
            foreach ($cfg['items'] as $itemDef) {
                $prodId = $itemDef[0];
                $qty = $itemDef[1];
                $price = $products[$prodId]->selling_price;
                $lineTotal = $price * $qty;
                $subtotal += $lineTotal;
                $itemsData[] = ['product_id' => $prodId, 'quantity' => $qty, 'price' => $price, 'subtotal' => $lineTotal];
            }

            $order = Order::create([
                'order_number' => 'ORD-' . $date->format('Ymd') . '-' . str_pad((string) $orderNumber, 4, '0', STR_PAD_LEFT),
                'table_id' => $cfg['table'],
                'customer_id' => $cfg['customer'],
                'waiter_id' => $waiterId,
                'status' => 'completed',
                'order_type' => $cfg['type'],
                'notes' => null,
                'branch_id' => $branchId,
                'received_at' => (clone $date)->addMinutes(2),
                'served_at' => (clone $date)->addMinutes(15),
                'completed_at' => (clone $date)->addMinutes(30),
                'created_at' => $date,
                'updated_at' => (clone $date)->addMinutes(30),
            ]);

            foreach ($itemsData as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'subtotal' => $item['subtotal'],
                    'status' => 'served',
                    'created_at' => $date,
                    'updated_at' => $date,
                ]);
            }

            $taxRate = 18 / 100;
            $serviceChargeRate = 5 / 100;
            $taxAmount = round($subtotal * $taxRate, 0);
            $serviceCharge = round($subtotal * $serviceChargeRate, 0);
            $totalAmount = $subtotal + $taxAmount + $serviceCharge;
            $paidAmount = $totalAmount;
            $changeAmount = 0;

            if ($cfg['method'] === 'cash') {
                $paidAmount = $totalAmount + rand(1000, 10000);
                $changeAmount = $paidAmount - $totalAmount;
            }

            $isMobileMoney = $cfg['method'] === 'mobile_money';
            $mobileProviders = ['mtn', 'airtel'];

            $bill = Bill::create([
                'bill_number' => 'BILL-' . $date->format('Ymd') . '-' . str_pad((string) $orderNumber, 4, '0', STR_PAD_LEFT),
                'order_id' => $order->id,
                'customer_id' => $cfg['customer'],
                'waiter_id' => $waiterId,
                'cashier_id' => $cashierId,
                'subtotal' => $subtotal,
                'discount_type' => null,
                'discount_value' => 0,
                'discount_amount' => 0,
                'tax_amount' => $taxAmount,
                'service_charge' => $serviceCharge,
                'total_amount' => $totalAmount,
                'paid_amount' => $paidAmount,
                'change_amount' => $changeAmount,
                'payment_method' => $cfg['method'],
                'mobile_provider' => $isMobileMoney ? $mobileProviders[array_rand($mobileProviders)] : null,
                'reference_number' => $isMobileMoney ? 'REF' . $date->format('Ymd') . str_pad((string) $order->id, 4, '0', STR_PAD_LEFT) : null,
                'payment_status' => 'paid',
                'notes' => null,
                'branch_id' => $branchId,
                'processed_by_role' => 'cashier',
                'created_at' => $date,
                'updated_at' => $date,
            ]);

            foreach ($itemsData as $item) {
                BillItem::create([
                    'bill_id' => $bill->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'subtotal' => $item['subtotal'],
                    'created_at' => $date,
                    'updated_at' => $date,
                ]);

                $product = $products[$item['product_id']];
                $product->decrement('current_stock', $item['quantity']);
            }

            Payment::create([
                'bill_id' => $bill->id,
                'amount' => $paidAmount,
                'payment_method' => $cfg['method'],
                'reference_no' => $isMobileMoney ? 'PAY-' . $date->format('Ymd') . '-' . $order->id : null,
                'paid_at' => (clone $date)->addMinutes(30),
                'notes' => null,
                'branch_id' => $branchId,
                'created_at' => $date,
                'updated_at' => $date,
            ]);

            foreach ($itemsData as $item) {
                StockMovement::create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'type' => 'out',
                    'reference_type' => 'bill',
                    'reference_id' => $bill->id,
                    'notes' => 'Sale via POS - Order #' . $order->order_number,
                    'created_by' => $cashierId,
                    'branch_id' => $branchId,
                    'created_at' => $date,
                    'updated_at' => $date,
                ]);
            }
        }
    }

    private function createInProgressOrders(int $branchId, int $waiterId, int $kitchenId, array $products): void
    {
        $now = Carbon::now();

        // Order 1: Pending (just placed, at kitchen)
        $order1 = Order::create([
            'order_number' => Order::generateOrderNumber(),
            'table_id' => 1,
            'customer_id' => 1,
            'waiter_id' => $waiterId,
            'status' => 'pending',
            'order_type' => 'dine_in',
            'notes' => 'Extra ice please',
            'branch_id' => $branchId,
        ]);
        OrderItem::create(['order_id' => $order1->id, 'product_id' => 4, 'quantity' => 2, 'price' => $products[4]->selling_price, 'subtotal' => $products[4]->selling_price * 2, 'status' => 'pending']);
        OrderItem::create(['order_id' => $order1->id, 'product_id' => 10, 'quantity' => 1, 'price' => $products[10]->selling_price, 'subtotal' => $products[10]->selling_price * 1, 'status' => 'pending']);
        RestaurantTable::where('id', 1)->update(['status' => 'occupied']);

        // Order 2: Preparing (kitchen accepted)
        $order2 = Order::create([
            'order_number' => Order::generateOrderNumber(),
            'table_id' => 3,
            'customer_id' => 2,
            'waiter_id' => $waiterId,
            'status' => 'preparing',
            'order_type' => 'dine_in',
            'notes' => null,
            'branch_id' => $branchId,
            'received_at' => now()->subMinutes(10),
        ]);
        OrderItem::create(['order_id' => $order2->id, 'product_id' => 8, 'quantity' => 2, 'price' => $products[8]->selling_price, 'subtotal' => $products[8]->selling_price * 2, 'status' => 'preparing']);
        OrderItem::create(['order_id' => $order2->id, 'product_id' => 9, 'quantity' => 1, 'price' => $products[9]->selling_price, 'subtotal' => $products[9]->selling_price * 1, 'status' => 'preparing']);
        OrderItem::create(['order_id' => $order2->id, 'product_id' => 6, 'quantity' => 2, 'price' => $products[6]->selling_price, 'subtotal' => $products[6]->selling_price * 2, 'status' => 'preparing']);
        RestaurantTable::where('id', 3)->update(['status' => 'occupied']);

        // Order 3: Ready (awaiting serving)
        $order3 = Order::create([
            'order_number' => Order::generateOrderNumber(),
            'table_id' => 5,
            'customer_id' => 3,
            'waiter_id' => $waiterId,
            'status' => 'ready',
            'order_type' => 'dine_in',
            'notes' => 'Well done wings',
            'branch_id' => $branchId,
            'received_at' => now()->subMinutes(20),
        ]);
        OrderItem::create(['order_id' => $order3->id, 'product_id' => 5, 'quantity' => 4, 'price' => $products[5]->selling_price, 'subtotal' => $products[5]->selling_price * 4, 'status' => 'ready']);
        OrderItem::create(['order_id' => $order3->id, 'product_id' => 9, 'quantity' => 2, 'price' => $products[9]->selling_price, 'subtotal' => $products[9]->selling_price * 2, 'status' => 'ready']);
        OrderItem::create(['order_id' => $order3->id, 'product_id' => 10, 'quantity' => 1, 'price' => $products[10]->selling_price, 'subtotal' => $products[10]->selling_price * 1, 'status' => 'ready']);
        RestaurantTable::where('id', 5)->update(['status' => 'occupied']);

        // Order 4: Served + Bill Requested (awaiting payment)
        $order4 = Order::create([
            'order_number' => Order::generateOrderNumber(),
            'table_id' => 2,
            'customer_id' => 4,
            'waiter_id' => $waiterId,
            'status' => 'served',
            'order_type' => 'dine_in',
            'notes' => null,
            'branch_id' => $branchId,
            'received_at' => now()->subMinutes(35),
            'served_at' => now()->subMinutes(10),
        ]);

        $subtotal4 = 0;
        $items4 = [
            ['product_id' => 1, 'quantity' => 1],
            ['product_id' => 4, 'quantity' => 3],
            ['product_id' => 7, 'quantity' => 1],
        ];
        foreach ($items4 as $item) {
            $price = $products[$item['product_id']]->selling_price;
            $lineTotal = $price * $item['quantity'];
            $subtotal4 += $lineTotal;
            OrderItem::create([
                'order_id' => $order4->id,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'price' => $price,
                'subtotal' => $lineTotal,
                'status' => 'served',
            ]);
        }

        $tax4 = round($subtotal4 * 0.18, 0);
        $sc4 = round($subtotal4 * 0.05, 0);
        $total4 = $subtotal4 + $tax4 + $sc4;

        Bill::create([
            'bill_number' => Bill::generateBillNumber(),
            'order_id' => $order4->id,
            'customer_id' => 4,
            'waiter_id' => $waiterId,
            'subtotal' => $subtotal4,
            'discount_type' => null,
            'discount_value' => 0,
            'discount_amount' => 0,
            'tax_amount' => $tax4,
            'service_charge' => $sc4,
            'total_amount' => $total4,
            'paid_amount' => 0,
            'change_amount' => 0,
            'payment_status' => 'unpaid',
            'branch_id' => $branchId,
        ]);

        RestaurantTable::where('id', 2)->update(['status' => 'occupied']);
    }

    private function createPurchases(int $branchId, int $storeId, array $products): void
    {
        // Purchase 1: Completed - Beverages restock
        $purchase1 = Purchase::create([
            'reference_no' => 'PO-2026-001',
            'supplier_id' => 1,
            'total_amount' => 315000,
            'paid_amount' => 315000,
            'status' => 'completed',
            'payment_method' => 'cash',
            'notes' => 'Monthly beverage restock',
            'created_by' => $storeId,
            'branch_id' => $branchId,
            'created_at' => now()->subDays(5),
            'updated_at' => now()->subDays(5),
        ]);
        PurchaseItem::create(['purchase_id' => $purchase1->id, 'product_id' => 4, 'quantity' => 100, 'cost_price' => 2500, 'subtotal' => 250000]);
        PurchaseItem::create(['purchase_id' => $purchase1->id, 'product_id' => 5, 'quantity' => 50, 'cost_price' => 3000, 'subtotal' => 150000]);
        PurchaseItem::create(['purchase_id' => $purchase1->id, 'product_id' => 6, 'quantity' => 50, 'cost_price' => 1500, 'subtotal' => 75000]);

        // Purchase 2: Completed - Spirits order
        $purchase2 = Purchase::create([
            'reference_no' => 'PO-2026-002',
            'supplier_id' => 4,
            'total_amount' => 900000,
            'paid_amount' => 500000,
            'status' => 'completed',
            'payment_method' => 'mobile_money',
            'notes' => 'Premium spirits - partial payment',
            'created_by' => $storeId,
            'branch_id' => $branchId,
            'created_at' => now()->subDays(10),
            'updated_at' => now()->subDays(10),
        ]);
        PurchaseItem::create(['purchase_id' => $purchase2->id, 'product_id' => 1, 'quantity' => 10, 'cost_price' => 45000, 'subtotal' => 450000]);
        PurchaseItem::create(['purchase_id' => $purchase2->id, 'product_id' => 2, 'quantity' => 10, 'cost_price' => 35000, 'subtotal' => 350000]);
        PurchaseItem::create(['purchase_id' => $purchase2->id, 'product_id' => 3, 'quantity' => 5, 'cost_price' => 20000, 'subtotal' => 100000]);

        // Purchase 3: Pending - Food supplies
        $purchase3 = Purchase::create([
            'reference_no' => 'PO-2026-003',
            'supplier_id' => 3,
            'total_amount' => 240000,
            'paid_amount' => 0,
            'status' => 'pending',
            'payment_method' => null,
            'notes' => 'Weekly food supplies',
            'created_by' => $storeId,
            'branch_id' => $branchId,
            'created_at' => now()->subDays(1),
            'updated_at' => now()->subDays(1),
        ]);
        PurchaseItem::create(['purchase_id' => $purchase3->id, 'product_id' => 8, 'quantity' => 50, 'cost_price' => 3000, 'subtotal' => 150000]);
        PurchaseItem::create(['purchase_id' => $purchase3->id, 'product_id' => 9, 'quantity' => 10, 'cost_price' => 8000, 'subtotal' => 80000]);
        PurchaseItem::create(['purchase_id' => $purchase3->id, 'product_id' => 10, 'quantity' => 10, 'cost_price' => 5000, 'subtotal' => 50000]);

        foreach ($purchase1->items as $item) {
            StockMovement::create([
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'type' => 'in',
                'reference_type' => 'purchase',
                'reference_id' => $purchase1->id,
                'notes' => 'Stock received - PO #PO-2026-001',
                'created_by' => $storeId,
                'branch_id' => $branchId,
                'created_at' => now()->subDays(5),
                'updated_at' => now()->subDays(5),
            ]);
            $products[$item->product_id]->increment('current_stock', $item->quantity);
        }

        foreach ($purchase2->items as $item) {
            StockMovement::create([
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'type' => 'in',
                'reference_type' => 'purchase',
                'reference_id' => $purchase2->id,
                'notes' => 'Stock received - PO #PO-2026-002',
                'created_by' => $storeId,
                'branch_id' => $branchId,
                'created_at' => now()->subDays(10),
                'updated_at' => now()->subDays(10),
            ]);
            $products[$item->product_id]->increment('current_stock', $item->quantity);
        }
    }

    private function createExpenses(int $branchId, int $adminId): void
    {
        $expenses = [
            ['description' => 'Electricity bill (June)', 'amount' => 450000, 'category' => 'Utilities', 'days_ago' => 2, 'payment_method' => 'cash'],
            ['description' => 'Water bill (June)', 'amount' => 120000, 'category' => 'Utilities', 'days_ago' => 2, 'payment_method' => 'cash'],
            ['description' => 'Cleaning supplies', 'amount' => 85000, 'category' => 'Supplies', 'days_ago' => 5, 'payment_method' => 'cash'],
            ['description' => 'Internet subscription', 'amount' => 150000, 'category' => 'Utilities', 'days_ago' => 10, 'payment_method' => 'mobile_money'],
            ['description' => 'Kitchen equipment maintenance', 'amount' => 200000, 'category' => 'Maintenance', 'days_ago' => 7, 'payment_method' => 'cash'],
            ['description' => 'Staff lunch', 'amount' => 95000, 'category' => 'Staff Welfare', 'days_ago' => 1, 'payment_method' => 'cash'],
            ['description' => 'Security services (monthly)', 'amount' => 300000, 'category' => 'Security', 'days_ago' => 3, 'payment_method' => 'mobile_money'],
            ['description' => 'Waste collection', 'amount' => 60000, 'category' => 'Services', 'days_ago' => 15, 'payment_method' => 'cash'],
        ];

        foreach ($expenses as $exp) {
            Expense::create([
                'description' => $exp['description'],
                'amount' => $exp['amount'],
                'category' => $exp['category'],
                'expense_date' => now()->subDays($exp['days_ago'])->toDateString(),
                'payment_method' => $exp['payment_method'],
                'reference_no' => null,
                'notes' => null,
                'created_by' => $adminId,
                'branch_id' => $branchId,
                'created_at' => now()->subDays($exp['days_ago']),
                'updated_at' => now()->subDays($exp['days_ago']),
            ]);
        }
    }
}
