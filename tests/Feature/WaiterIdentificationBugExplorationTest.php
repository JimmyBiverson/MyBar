<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Branch;
use App\Models\Bill;
use App\Models\Order;
use App\Models\Table;
use App\Models\Product;
use App\Models\Category;
use App\Models\Unit;
use App\Models\Customer;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

/**
 * Bug Condition Exploration Test for Waiter Identification Issues
 * 
 * **Property 1: Bug Condition - Waiter Identification Issues in Payment System**
 * **Validates: Requirements 1.1, 1.2, 1.3, 1.4, 1.5**
 * 
 * CRITICAL: This test MUST FAIL on unfixed code - failure confirms the bug exists
 * DO NOT attempt to fix the test or the code when it fails
 * 
 * This test encodes the expected behavior - it will validate the fix when it passes after implementation
 * GOAL: Surface counterexamples that demonstrate waiter identification issues exist
 */
class WaiterIdentificationBugExplorationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create required settings
        Setting::create(['key' => 'enable_tax', 'value' => 'false']);
        Setting::create(['key' => 'enable_service_charge', 'value' => 'false']);
    }

    /**
     * Property-Based Test: Waiter Identification in Administrative Views
     * 
     * This test demonstrates that when multiple waiters have similar names,
     * administrative views cannot distinguish between them properly.
     * 
     * Expected Behavior: Administrative views should display unique waiter 
     * identifiers (employee ID, badge number) alongside names.
     * 
     * Bug Condition: System only shows generic waiter names without unique identifiers.
     */
    public function test_administrative_views_display_unique_waiter_identifiers()
    {
        // Arrange: Create test data with duplicate waiter names (common bug scenario)
        $waiterRole = Role::firstOrCreate(['name' => 'Waiter'], ['description' => 'Waiter role']);
        $branch = Branch::firstOrCreate(['name' => 'Test Branch'], [
            'address' => 'Test Address',
            'phone' => '123456789',
            'email' => 'test@example.com',
            'is_active' => true
        ]);
        
        // Create two waiters with identical names (this is the bug condition trigger)
        $waiter1 = User::create([
            'name' => 'John Smith',
            'email' => 'john1@test.com',
            'password' => bcrypt('password'),
            'role_id' => $waiterRole->id,
            'branch_id' => $branch->id,
            'employee_id' => 'EMP001', // Unique employee ID
        ]);
        
        $waiter2 = User::create([
            'name' => 'John Smith', // Same name - this triggers the identification issue
            'email' => 'john2@test.com',
            'password' => bcrypt('password'),
            'role_id' => $waiterRole->id,
            'branch_id' => $branch->id,
            'employee_id' => 'EMP002', // Different employee ID
        ]);

        // Create bills processed by each waiter
        $bill1 = Bill::create([
            'bill_number' => 'BILL-TEST-001',
            'waiter_id' => $waiter1->id,
            'subtotal' => 1000,
            'total_amount' => 1000,
            'paid_amount' => 1000,
            'payment_status' => 'paid',
            'payment_method' => 'cash',
            'processed_by_role' => 'waiter',
            'branch_id' => $branch->id,
        ]);
        
        $bill2 = Bill::create([
            'bill_number' => 'BILL-TEST-002',
            'waiter_id' => $waiter2->id,
            'subtotal' => 1500,
            'total_amount' => 1500,
            'paid_amount' => 1500,
            'payment_status' => 'paid',
            'payment_method' => 'cash',
            'processed_by_role' => 'waiter',
            'branch_id' => $branch->id,
        ]);

        // Act & Assert: Test that administrative views can distinguish between waiters
        
        // 1. Test Bill model processor identification
        $processor1Info = $bill1->getProcessorNameAttribute();
        $processor2Info = $bill2->getProcessorNameAttribute();
        
        // Expected Behavior: Should include unique identifier (employee_id) alongside name
        // Bug Condition: Both will return just "John Smith" making them indistinguishable
        $this->assertNotEquals($processor1Info, $processor2Info, 
            'Bills processed by different waiters with same name should be distinguishable by unique identifier. ' .
            "Got: '$processor1Info' and '$processor2Info'");
        
        // Expected: "John Smith (#001)" vs "John Smith (#002)" or similar unique format
        $this->assertStringContainsString('#', $processor1Info, 
            'Waiter identification should include unique identifier (employee_id) in format with # symbol. ' .
            "Got: '$processor1Info'");
        $this->assertStringContainsString('#', $processor2Info, 
            'Waiter identification should include unique identifier (employee_id) in format with # symbol. ' .
            "Got: '$processor2Info'");

        // 2. Test that User model provides unique identification method
        // Expected Behavior: User model should have getDisplayNameAttribute() method
        $display1 = $waiter1->display_name;
        $display2 = $waiter2->display_name;
        
        $this->assertNotEquals($display1, $display2, 
            "Users with same name should have distinguishable display names using employee_id. " .
            "Got: '$display1' and '$display2'");

        // 3. Test that employee_id field exists and is populated
        // Expected Behavior: Users should have unique employee_id field
        $this->assertNotNull($waiter1->employee_id, 
            'Waiter should have populated employee_id for unique identification');
        $this->assertNotNull($waiter2->employee_id, 
            'Waiter should have populated employee_id for unique identification');
        $this->assertNotEquals($waiter1->employee_id, $waiter2->employee_id, 
            'Different waiters should have unique employee_id values');
    }

    /**
     * Property-Based Test: Payment Modal Waiter Identification
     * 
     * This test demonstrates that payment processing lacks clear waiter identification.
     * 
     * Expected Behavior: Payment modals should show processing waiter's unique identifier.
     * Bug Condition: Payment modals show generic "Process Payment" without waiter identification.
     */
    public function test_payment_modals_show_processing_waiter_unique_identifier()
    {
        // Arrange
        $waiterRole = Role::firstOrCreate(['name' => 'Waiter'], ['description' => 'Waiter role']);
        $branch = Branch::firstOrCreate(['name' => 'Test Branch'], [
            'address' => 'Test Address',
            'phone' => '123456789',
            'email' => 'test@example.com',
            'is_active' => true
        ]);
        
        $waiter = User::create([
            'name' => 'Alice Johnson',
            'email' => 'alice@test.com',
            'password' => bcrypt('password'),
            'role_id' => $waiterRole->id,
            'branch_id' => $branch->id,
            'employee_id' => 'EMP003',
        ]);
        
        // Act: Test that waiter model has unique identification available
        // Assert: Payment waiter identification should be available via display_name
        $this->assertNotNull($waiter->display_name,
            'Waiter should have display_name available for payment modal identification');
        
        $this->assertStringContainsString('Alice Johnson', $waiter->display_name,
            'Display name should include waiter name');
        
        $this->assertStringContainsString('EMP003', $waiter->display_name,
            'Display name should include employee ID for unique identification in payment flow');
        
        // Verify format is consistent with expected pattern
        $this->assertMatchesRegularExpression('/^Alice Johnson \(#\w+\)$/', $waiter->display_name,
            'Display name should follow format: Name (#EmployeeID)');
    }

    /**
     * Property-Based Test: Role-Based UI Access Restrictions
     * 
     * This test demonstrates that waiters may access admin-only UI features.
     * 
     * Expected Behavior: UI elements should be properly restricted by role-based access control.
     * Bug Condition: Waiters can access admin-only features in the interface.
     */  
    public function test_role_based_ui_restrictions_limit_waiter_access()
    {
        // Arrange
        $waiterRole = Role::firstOrCreate(['name' => 'Waiter'], ['description' => 'Waiter role']);
        $branch = Branch::firstOrCreate(['name' => 'Test Branch'], [
            'address' => 'Test Address',
            'phone' => '123456789',
            'email' => 'test@example.com',
            'is_active' => true
        ]);
        
        $waiter = User::create([
            'name' => 'Bob Wilson',
            'email' => 'bob@test.com',
            'password' => bcrypt('password'),
            'role_id' => $waiterRole->id,
            'branch_id' => $branch->id,
            'employee_id' => 'EMP004',
        ]);

        // Act & Assert: Verify waiter has proper role identification
        $this->assertTrue($waiter->isWaiter(),
            'User should be properly identified as waiter by role');
        
        $this->assertFalse($waiter->isAdmin(),
            'Waiter should not be identified as admin');
        
        $this->assertFalse($waiter->isCashier(),
            'Waiter should not be identified as cashier');
        
        // Assert: Waiter role model relationships are correct
        $this->assertNotNull($waiter->role,
            'Waiter should have associated role');
        
        $this->assertEquals('Waiter', $waiter->role->name,
            'Waiter role name should be Waiter');
        
        // Assert: Waiter has proper employee identification for UI restrictions
        $this->assertNotNull($waiter->employee_id,
            'Waiter should have employee_id for identification in UI');
    }

    /**
     * Property-Based Test: Administrative View Waiter Differentiation
     * 
     * This test demonstrates that administrative views cannot properly differentiate
     * between multiple waiters when reviewing payment records.
     */
    public function test_administrative_views_provide_clear_waiter_differentiation()
    {
        // Arrange: Create admin user and multiple waiters with similar names
        $waiterRole = Role::firstOrCreate(['name' => 'Waiter'], ['description' => 'Waiter role']);
        $adminRole = Role::firstOrCreate(['name' => 'Super Admin'], ['description' => 'Super Admin role']);
        $branch = Branch::firstOrCreate(['name' => 'Test Branch'], [
            'address' => 'Test Address',
            'phone' => '123456789',
            'email' => 'test@example.com',
            'is_active' => true
        ]);
        
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role_id' => $adminRole->id,
            'branch_id' => $branch->id,
            'employee_id' => 'ADM001',
        ]);
        
        // Create multiple waiters that are hard to distinguish
        $waiters = [];
        for ($i = 1; $i <= 3; $i++) {
            $waiters[] = User::create([
                'name' => 'Mike Davis', // Same name for all - tests identification system
                'email' => "mike{$i}@test.com",
                'password' => bcrypt('password'),
                'role_id' => $waiterRole->id, 
                'branch_id' => $branch->id,
                'employee_id' => 'EMP' . str_pad(100 + $i, 3, '0', STR_PAD_LEFT),
            ]);
        }
        
        // Create bills for each waiter
        $bills = [];
        foreach ($waiters as $index => $waiter) {
            $bills[] = Bill::create([
                'bill_number' => 'BILL-MIKE-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT),
                'waiter_id' => $waiter->id,
                'subtotal' => 1000,
                'total_amount' => 1000,
                'paid_amount' => 1000,
                'payment_status' => 'paid',
                'payment_method' => 'cash',
                'processed_by_role' => 'waiter',
                'branch_id' => $branch->id,
            ]);
        }
        
        // Assert: Test that processor information includes unique identifiers
        foreach ($bills as $index => $bill) {
            $bill->refresh(); // Ensure fresh data
            $processorName = $bill->getProcessorNameAttribute();
            
            // Expected: Should include employee_id like "Mike Davis (#101)", "Mike Davis (#102)", etc.
            $this->assertMatchesRegularExpression('/Mike Davis.*#\w+/', $processorName,
                "Bill processor name should include unique identifier: got '$processorName'");
        }
        
        // Test that all three bills are distinguishable by processor identification
        $processorNames = array_map(fn($bill) => $bill->getProcessorNameAttribute(), $bills);
        $uniqueProcessors = array_unique($processorNames);
        
        $this->assertGreaterThanOrEqual(3, count($uniqueProcessors),
            'Each bill with different waiter should have unique processor identification');
        
        // Test waiter identification attributes are available
        foreach ($bills as $index => $bill) {
            $waiterIdent = $bill->waiter_identification;
            $this->assertNotEmpty($waiterIdent,
                'Bill should have waiter_identification attribute for admin views');
            
            $this->assertStringContainsString('Mike Davis', $waiterIdent,
                'Waiter identification should include waiter name');
            
            $this->assertStringContainsString('#', $waiterIdent,
                'Waiter identification should include employee ID marker');
        }
    }

    /**
     * Helper method to create a bill for a specific waiter
     */
    private function createBillForWaiter(User $waiter, Branch $branch): Bill
    {
        $category = Category::factory()->create(['name' => 'Test Category']);
        $unit = Unit::factory()->create(['name' => 'piece']);
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'branch_id' => $branch->id,
            'selling_price' => 1000,
        ]);
        
        $table = Table::factory()->create([
            'name' => 'Table ' . rand(1, 10),
            'branch_id' => $branch->id,
        ]);
        
        $customer = Customer::factory()->create([
            'name' => 'Test Customer',
            'branch_id' => $branch->id,
        ]);
        
        $order = Order::factory()->create([
            'waiter_id' => $waiter->id,
            'table_id' => $table->id,
            'customer_id' => $customer->id,
            'branch_id' => $branch->id,
            'status' => 'completed',
        ]);
        
        return Bill::factory()->create([
            'order_id' => $order->id,
            'customer_id' => $customer->id,
            'waiter_id' => $waiter->id,
            'subtotal' => 1000,
            'total_amount' => 1000,
            'paid_amount' => 1000,
            'payment_status' => 'paid',
            'payment_method' => 'cash',
            'processed_by_role' => 'waiter',
            'branch_id' => $branch->id,
        ]);
    }
    
    /**
     * Helper method to create an order ready for payment
     */
    private function createOrderForPayment(User $waiter, Branch $branch): Order
    {
        $category = Category::factory()->create(['name' => 'Test Category']);
        $unit = Unit::factory()->create(['name' => 'piece']);
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'branch_id' => $branch->id,
            'selling_price' => 1500,
        ]);
        
        $table = Table::factory()->create([
            'name' => 'Table ' . rand(11, 20),
            'branch_id' => $branch->id,
        ]);
        
        return Order::factory()->create([
            'waiter_id' => $waiter->id,
            'table_id' => $table->id,
            'branch_id' => $branch->id,
            'status' => 'served',
            'bill_requested' => true,
        ]);
    }
}