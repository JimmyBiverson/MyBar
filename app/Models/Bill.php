<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bill extends Model
{
    use HasFactory;

    protected $appends = [
        'total',
        'paid',
        'balance',
        'discount',
        'tax',
        'invoice_no',
        'items_count',
        'processor_name',
        'processor_badge_class',
        'processor_label',
        'full_waiter_identification',
        'waiter_identification',
    ];

    protected $fillable = [
        'bill_number',
        'order_id',
        'customer_id',
        'waiter_id',
        'cashier_id',
        'subtotal',
        'discount_type',
        'discount_value',
        'discount_amount',
        'tax_amount',
        'service_charge',
        'total_amount',
        'paid_amount',
        'change_amount',
        'payment_method',
        'mobile_provider',
        'reference_number',
        'payment_status',
        'notes',
        'branch_id',
        'items_data',
        'processed_by_role',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'discount_value' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'service_charge' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'change_amount' => 'decimal:2',
        ];
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function waiter()
    {
        return $this->belongsTo(User::class, 'waiter_id');
    }

    public function cashier()
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function items()
    {
        return $this->hasMany(BillItem::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function getTotalAttribute()
    {
        return $this->total_amount;
    }

    public function getPaidAttribute()
    {
        return $this->paid_amount;
    }

    public function getBalanceAttribute()
    {
        return max(0, $this->total_amount - $this->paid_amount);
    }

    public function getDiscountAttribute()
    {
        return $this->discount_value;
    }

    public function getTaxAttribute()
    {
        return $this->tax_amount;
    }

    public function getInvoiceNoAttribute()
    {
        return $this->bill_number;
    }

    public function getItemsCountAttribute()
    {
        return $this->items->count();
    }

    public function getProcessorNameAttribute()
    {
        $processor = $this->cashier ?? $this->waiter;
        if (!$processor) {
            return 'N/A';
        }
        return $processor->display_name;
    }

    public function getProcessorBadgeClassAttribute()
    {
        return $this->processed_by_role === 'waiter' ? 'bg-info' : 'bg-secondary';
    }

    public function getProcessorLabelAttribute()
    {
        $processor = $this->cashier ?? $this->waiter;
        $roleLabel = ucfirst($this->processed_by_role ?? 'cashier');
        
        // For waiters, include employee ID in the label
        if ($this->processed_by_role === 'waiter' && $processor && $processor->employee_id) {
            return "{$roleLabel} (#{$processor->employee_id})";
        }
        
        return $roleLabel;
    }

    public function processor(): ?User
    {
        return $this->cashier ?? $this->waiter;
    }

    /**
     * Get full waiter identification for administrative views.
     * Returns a formatted string with waiter name and employee ID.
     * Example: "John Doe - Employee #EMP001" or "John Doe" if no employee ID.
     * 
     * @return string Full waiter identification
     */
    public function getFullWaiterIdentificationAttribute(): string
    {
        $waiter = $this->waiter;
        
        if (!$waiter) {
            return 'N/A';
        }
        
        if ($waiter->employee_id) {
            return "{$waiter->name} - Employee #{$waiter->employee_id}";
        }
        
        return $waiter->name;
    }

    /**
     * Get waiter identification for administrative views (short format).
     * Returns formatted string: "Name (#EmployeeID)" or just "Name" if no ID.
     * 
     * @return string Waiter identification in short format
     */
    public function getWaiterIdentificationAttribute(): string
    {
        if ($this->processed_by_role !== 'waiter') {
            return 'N/A';
        }
        
        $waiter = $this->waiter;
        
        if (!$waiter) {
            return 'N/A';
        }
        
        if ($waiter->employee_id) {
            return "{$waiter->name} (#{$waiter->employee_id})";
        }
        
        return $waiter->name;
    }

    public static function generateBillNumber()
    {
        $prefix = 'BILL-' . now()->format('Ymd') . '-';
        $last = static::where('bill_number', 'like', $prefix . '%')
            ->orderBy('bill_number', 'desc')
            ->value('bill_number');

        $number = $last ? (int) substr($last, -4) + 1 : 1;

        return $prefix . str_pad($number, 4, '0', STR_PAD_LEFT);
    }
}
