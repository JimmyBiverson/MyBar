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
