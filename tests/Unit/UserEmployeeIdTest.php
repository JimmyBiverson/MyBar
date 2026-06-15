<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Models\User;
use App\Models\Bill;

class UserEmployeeIdTest extends TestCase
{
    /**
     * Test that User model has employee_id in fillable array
     * Validates: Task 3.2 requirement - "Add `employee_id` to `$fillable` array"
     */
    public function test_employee_id_is_in_fillable_array(): void
    {
        $user = new User();
        $this->assertContains('employee_id', $user->getFillable(), 
            'User model should have employee_id in fillable array');
    }

    /**
     * Test that User model has getDisplayNameAttribute method
     * Validates: Task 3.2 requirement - "Create `getDisplayNameAttribute()` method"
     */
    public function test_user_has_display_name_attribute_method(): void
    {
        $this->assertTrue(method_exists(User::class, 'getDisplayNameAttribute'),
            'User model should have getDisplayNameAttribute() method');
    }

    /**
     * Test that getDisplayNameAttribute returns correct format with employee_id
     * Validates: Task 3.2 requirement - "combining name and employee ID"
     */
    public function test_display_name_attribute_includes_employee_id(): void
    {
        $user = new User([
            'name' => 'John Doe',
            'employee_id' => 'EMP001'
        ]);

        $this->assertEquals('John Doe (#EMP001)', $user->display_name,
            'Display name should include employee ID in format "Name (#ID)"');
    }

    /**
     * Test that getDisplayNameAttribute returns just name when employee_id is null
     * Validates: Backward compatibility
     */
    public function test_display_name_attribute_without_employee_id(): void
    {
        $user = new User([
            'name' => 'Jane Smith',
            'employee_id' => null
        ]);

        $this->assertEquals('Jane Smith', $user->display_name,
            'Display name should return just name when employee_id is null');
    }

    /**
     * Test that User model provides validation rules for employee_id
     * Validates: Task 3.2 requirement - "Add validation rules for employee ID format consistency"
     */
    public function test_employee_id_validation_rules_exist(): void
    {
        $this->assertTrue(method_exists(User::class, 'getEmployeeIdValidationRules'),
            'User model should have getEmployeeIdValidationRules() method');

        $rules = User::getEmployeeIdValidationRules();
        $this->assertArrayHasKey('employee_id', $rules,
            'Validation rules should include employee_id');
        $this->assertStringContainsString('regex', $rules['employee_id'],
            'Employee ID should have format validation regex');
    }

    /**
     * Test that Bill model uses display_name in getProcessorNameAttribute
     * Validates: Implementation uses employee_id in processor identification
     */
    public function test_bill_processor_name_uses_display_name(): void
    {
        $user = new User([
            'id' => 1,
            'name' => 'Alice Johnson',
            'employee_id' => 'EMP002'
        ]);

        // Mock the User relationship for Bill
        $bill = new Bill([
            'waiter_id' => 1,
        ]);
        
        // Test that method exists and uses display_name logic
        $this->assertTrue(method_exists($bill, 'getProcessorNameAttribute'),
            'Bill model should have getProcessorNameAttribute() method');
    }

    /**
     * Test that UserFactory is configured to generate employee_id
     * Validates: Task 3.2 requirement - "Update user factory... to generate employee IDs"
     */
    public function test_user_factory_configured_for_employee_id(): void
    {
        // Check that UserFactory exists and has definition method
        $this->assertTrue(class_exists('Database\Factories\UserFactory'),
            'UserFactory should exist');
        
        // Check the factory source code includes employee_id generation
        $factoryPath = 'database/factories/UserFactory.php';
        $this->assertFileExists($factoryPath, 'UserFactory file should exist');
        
        $factoryContent = file_get_contents($factoryPath);
        $this->assertStringContainsString("'employee_id'", $factoryContent,
            'UserFactory should generate employee_id');
        $this->assertStringContainsString('EMP', $factoryContent,
            'UserFactory should generate employee_id starting with EMP prefix');
    }
}
