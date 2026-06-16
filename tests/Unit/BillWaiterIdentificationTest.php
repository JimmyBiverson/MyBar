<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Bill;
use App\Models\User;
use App\Models\Role;
use App\Models\Branch;
use App\Models\Order;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Unit Tests for Bill Model Waiter Identification Enhancement (Task 3.3)
 * 
 * Tests the updated Bill model methods that provide unique waiter identification
 * for administrative views while ensuring backward compatibility.
 */
class BillWaiterIdentificationTest extends TestCase
{
    use RefreshDatabase;

    protected Bill $bill;
    protected User $waiter;
    protected User $cashier;
    protected Branch $branch;
    protected Role $waiterRole;
    protected Role $cashierRole;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->branch = Branch::factory()->create();
        
        // Create or retrieve roles
        $this->waiterRole = Role::where('name', 'Waiter')->first();
        if (!$this->waiterRole) {
            $this->waiterRole = Role::create(['name' => 'Waiter', 'description' => 'Waiter']);
        }
        
        $this->cashierRole = Role::where('name', 'Cashier')->first();
        if (!$this->cashierRole) {
            $this->cashierRole = Role::create(['name' => 'Cashier', 'description' => 'Cashier']);
        }
        
        // Create users with explicit role_id
        $this->waiter = User::create([
            'name' => 'John Smith',
            'email' => 'john@test.com',
            'password' => bcrypt('password'),
            'role_id' => $this->waiterRole->id,
            'branch_id' => $this->branch->id,
            'employee_id' => 'EMP001',
        ]);
        
        $this->cashier = User::create([
            'name' => 'Jane Doe',
            'email' => 'jane@test.com',
            'password' => bcrypt('password'),
            'role_id' => $this->cashierRole->id,
            'branch_id' => $this->branch->id,
            'employee_id' => 'CASH001',
        ]);
        
        // Create a bill with explicit waiter and branch
        $order = Order::factory()->create([
            'waiter_id' => $this->waiter->id,
            'branch_id' => $this->branch->id,
        ]);
        
        $customer = Customer::factory()->create([
            'branch_id' => $this->branch->id,
        ]);
        
        $this->bill = Bill::create([
            'bill_number' => Bill::generateBillNumber(),
            'order_id' => $order->id,
            'customer_id' => $customer->id,
            'waiter_id' => $this->waiter->id,
            'cashier_id' => null,
            'subtotal' => 1000,
            'tax_amount' => 180,
            'service_charge' => 50,
            'total_amount' => 1230,
            'paid_amount' => 1230,
            'change_amount' => 0,
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'processed_by_role' => 'waiter',
            'branch_id' => $this->branch->id,
        ]);
    }

    private function createBill($waiter, $cashier = null, $processedByRole = 'waiter')
    {
        $order = Order::factory()->create([
            'waiter_id' => $waiter->id,
            'branch_id' =processor_name_includes_employee_id()
    {
        $processorName = $this->bill->getProcessorNameAttribute();
        
        // Should include name and employee ID
        $this->assertStringContainsString('John Smith', $processorName);
        $this->assertStringContainsString('EMP001', $processorName);
        
        // Format should be: "Name (#EmployeeID)"
        $this->assertMatchesRegularExpression('/John Smith.*#EMP001/', $processorName);
    }

    /**
     * Test: getProcessorNameAttribute backward compatibility without employee ID
     * 
     * Validates: Requirements 2.1 (backward compatibility)
     * System should gracefully handle bills from waiters without employee ID.
     */
    public function test_processor_name_backward_compatibility_without_employee_id()
    {
        $waiterRole = Role::where('name', 'Waiter')->first();
        $waiterNoId = User::create([
            'name' => 'Old Waiter',
            'email' => 'oldwaiter@test.com',
            'password' => bcrypt('password'),
            'role_id' => $waiterRole->id,
            'branch_id' => $this->branch->id,
            'employee_id' => null,
        ]);
        
        $order = Order::factory()->create(['branch_id' => $this->branch->id, 'waiter_id' => $waiterNoId->id]);
        $customer = Customer::factory()->create(['branch_id' => $this->branch->id]);
        
        $billNoId = Bill::create([
            'bill_number' => Bill::generateBillNumber(),
            'order_id' => $order->id,
            'customer_id' => $customer->id,
            'waiter_id' => $waiterNoId->id,
            'subtotal' => 1000,
            'tax_amount' => 180,
            'total_amount' => 1180,
            'paid_amount' => 1180,
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'processed_by_role' => 'waiter',
            'branch_id' => $this->branch->id,
        ]);
        
        $processorName = $billNoId->getProcessorNameAttribute();
        
        // Should still show waiter name even without employee ID
        $this->assertEquals('Old Waiter', $processorName);
    }

    /**
     * Test: getProcessorLabelAttribute shows employee ID for waiters
     * 
     * Validates: Requirements 1.4, 2.1, 2.4
     * The processor label should include employee ID for waiter-processed bills.
     */
    public function test_processor_label_includes_employee_id_for_waiters()
    {
        $label = $this->bill->getProcessorLabelAttribute();
        
        // Should show "Waiter (#{employee_id})"
        $this->assertStringContainsString('Waiter', $label);
        $this->assertStringContainsString('EMP001', $label);
        $this->assertMatchesRegularExpression('/Waiter \(#EMP001\)/', $label);
    }

    /**
     * Test: getProcessorLabelAttribute for cashier without employee ID in label
     * 
     * Validates: Requirements 2.4
     * Cashier labels should not show employee ID in the label (only for waiters).
     */
    public function test_processor_label_cashier_format()
    {
        $cashierBill = Bill::factory()->create([
            'cashier_id' => $this->cashier->id,
            'processed_by_role' => 'cashier',
            'branch_id' => $this->branch->id,
        ]);
        
        $label = $cashierBill->getProcessorLabelAttribute();
        
        // For cashiers, should just be "Cashier" (no employee ID in label)
        $this->assertEquals('Cashier', $label);
    }

    /**
     * Test: getProcessorLabelAttribute backward compatibility without employee ID
     * 
     * Validates: Requirements 2.1 (backward compatibility)
     * Should gracefully show just "Waiter" if no employee ID.
     */
    public function test_processor_label_backward_compatibility_no_employee_id()
    {
        $waiterRole = Role::where('name', 'Waiter')->first();
        $waiterNoId = User::create([
            'name' => 'Legacy Waiter',
            'email' => 'legacywaiter@test.com',
            'password' => bcrypt('password'),
            'role_id' => $waiterRole->id,
            'branch_id' => $this->branch->id,
            'employee_id' => null,
        ]);
        
        $billNoId = Bill::factory()->create([
            'waiter_id' => $waiterNoId->id,
            'processed_by_role' => 'waiter',
            'branch_id' => $this->branch->id,
        ]);
        
        $label = $billNoId->getProcessorLabelAttribute();
        
        // Should just be "Waiter" without employee ID
        $this->assertEquals('Waiter', $label);
    }

    /**
     * Test: getFullWaiterIdentificationAttribute returns complete waiter info
     * 
     * Validates: Requirements 1.1, 1.4, 2.1, 2.4
     * Returns full identification for administrative views.
     */
    public function test_full_waiter_identification_attribute()
    {
        $fullId = $this->bill->getFullWaiterIdentificationAttribute();
        
        // Should include full waiter info: "Name - Employee #ID"
        $this->assertStringContainsString('John Smith', $fullId);
        $this->assertStringContainsString('Employee #EMP001', $fullId);
        $this->assertEquals('John Smith - Employee #EMP001', $fullId);
    }

    /**
     * Test: getFullWaiterIdentificationAttribute backward compatibility
     * 
     * Validates: Requirements 2.1 (backward compatibility)
     * Should handle bills from before employee ID implementation.
     */
    public function test_full_waiter_identification_backward_compatibility()
    {
        $waiterRole = Role::where('name', 'Waiter')->first();
        $waiterNoId = User::create([
            'name' => 'Legacy Waiter',
            'email' => 'legacywaiter2@test.com',
            'password' => bcrypt('password'),
            'role_id' => $waiterRole->id,
            'branch_id' => $this->branch->id,
            'employee_id' => null,
        ]);
        
        $billNoId = Bill::factory()->create([
            'waiter_id' => $waiterNoId->id,
            'processed_by_role' => 'waiter',
            'branch_id' => $this->branch->id,
        ]);
        
        $fullId = $billNoId->getFullWaiterIdentificationAttribute();
        
        // Should just be waiter name without employee ID
        $this->assertEquals('Legacy Waiter', $fullId);
    }

    /**
     * Test: getWaiterIdentificationAttribute returns short format
     * 
     * Validates: Requirements 1.1, 1.4, 2.1, 2.4
     * Returns short format identification: "Name (#ID)".
     */
    public function test_waiter_identification_attribute_short_format()
    {
        $identification = $this->bill->getWaiterIdentificationAttribute();
        
        // Should be "Name (#EmployeeID)"
        $this->assertStringContainsString('John Smith', $identification);
        $this->assertStringContainsString('(#EMP001)', $identification);
        $this->assertEquals('John Smith (#EMP001)', $identification);
    }

    /**
     * Test: getWaiterIdentificationAttribute for cashier bills
     * 
     * Validates: Requirements 1.4
     * Should return 'N/A' for cashier-processed bills.
     */
    public function test_waiter_identification_na_for_cashier_bills()
    {
        $cashierBill = Bill::factory()->create([
            'cashier_id' => $this->cashier->id,
            'processed_by_role' => 'cashier',
            'branch_id' => $this->branch->id,
        ]);
        
        $identification = $cashierBill->getWaiterIdentificationAttribute();
        
        // Should be N/A since this is a cashier bill, not waiter
        $this->assertEquals('N/A', $identification);
    }

    /**
     * Test: getWaiterIdentificationAttribute backward compatibility
     * 
     * Validates: Requirements 2.1 (backward compatibility)
     * Should handle bills from before employee ID implementation.
     */
    public function test_waiter_identification_backward_compatibility()
    {
        $waiterRole = Role::where('name', 'Waiter')->first();
        $waiterNoId = User::create([
            'name' => 'Legacy Waiter',
            'email' => 'legacywaiter3@test.com',
            'password' => bcrypt('password'),
            'role_id' => $waiterRole->id,
            'branch_id' => $this->branch->id,
            'employee_id' => null,
        ]);
        
        $billNoId = Bill::factory()->create([
            'waiter_id' => $waiterNoId->id,
            'processed_by_role' => 'waiter',
            'branch_id' => $this->branch->id,
        ]);
        
        $identification = $billNoId->getWaiterIdentificationAttribute();
        
        // Should just be waiter name without employee ID
        $this->assertEquals('Legacy Waiter', $identification);
    }

    /**
     * Test: Attributes are in $appends array
     * 
     * Validates: Requirements 2.1, 2.4
     * New identification attributes should be automatically appended to JSON.
     */
    public function test_waiter_identification_attributes_in_appends()
    {
        $appends = $this->bill->getAppends();
        
        $this->assertContains('full_waiter_identification', $appends);
        $this->assertContains('waiter_identification', $appends);
    }

    /**
     * Test: JSON serialization includes identification attributes
     * 
     * Validates: Requirements 1.1, 1.4, 2.1, 2.4
     * Identification attributes should be in JSON for API responses.
     */
    public function test_identification_attributes_in_json()
    {
        $json = $this->bill->toArray();
        
        $this->assertArrayHasKey('full_waiter_identification', $json);
        $this->assertArrayHasKey('waiter_identification', $json);
        
        $this->assertStringContainsString('John Smith', $json['full_waiter_identification']);
        $this->assertStringContainsString('John Smith', $json['waiter_identification']);
    }

    /**
     * Test: Multiple waiters with same name are distinguishable
     * 
     * Validates: Requirements 1.1, 1.4, 2.1, 2.4
     * The core fix - multiple waiters with same name must be distinguishable.
     */
    public function test_distinguishable_waiters_with_same_name()
    {
        $waiterRole = Role::where('name', 'Waiter')->first();
        $waiter2 = User::create([
            'name' => 'John Smith', // Same name
            'email' => 'john2@test.com',
            'password' => bcrypt('password'),
            'role_id' => $waiterRole->id,
            'branch_id' => $this->branch->id,
            'employee_id' => 'EMP002', // Different employee ID
        ]);
        
        $bill2 = Bill::factory()->create([
            'waiter_id' => $waiter2->id,
            'processed_by_role' => 'waiter',
            'branch_id' => $this->branch->id,
        ]);
        
        $id1 = $this->bill->getWaiterIdentificationAttribute();
        $id2 = $bill2->getWaiterIdentificationAttribute();
        
        // Same names but different employee IDs should be distinguishable
        $this->assertNotEquals($id1, $id2);
        
        // Both should include the name
        $this->assertStringContainsString('John Smith', $id1);
        $this->assertStringContainsString('John Smith', $id2);
        
        // But have different identifiers
        $this->assertStringContainsString('EMP001', $id1);
        $this->assertStringContainsString('EMP002', $id2);
    }

    /**
     * Test: Processor name follows display_name pattern
     * 
     * Validates: Requirements 1.1, 2.1
     * Should match the User model's display_name format.
     */
    public function test_processor_name_matches_user_display_name()
    {
        $processorName = $this->bill->getProcessorNameAttribute();
        $displayName = $this->waiter->getDisplayNameAttribute();
        
        // Should be the same as User's display_name
        $this->assertEquals($displayName, $processorName);
    }

    /**
     * Test: N/A handling when waiter not found
     * 
     * Validates: Requirements 2.1 (robustness)
     * Should handle cases where waiter is null.
     */
    public function test_na_handling_when_waiter_not_found()
    {
        $billNoWaiter = Bill::factory()->create([
            'waiter_id' => null,
            'cashier_id' => null,
            'processed_by_role' => 'waiter',
            'branch_id' => $this->branch->id,
        ]);
        
        $fullId = $billNoWaiter->getFullWaiterIdentificationAttribute();
        $identification = $billNoWaiter->getWaiterIdentificationAttribute();
        
        $this->assertEquals('N/A', $fullId);
        $this->assertEquals('N/A', $identification);
    }
}
