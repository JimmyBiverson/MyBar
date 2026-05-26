<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'table_id',
        'customer_id',
        'waiter_id',
        'status',
        'order_type',
        'notes',
        'branch_id',
        'received_at',
        'served_at',
        'completed_at',
    ];

    protected $appends = [
        'total',
        'items_count',
        'progress',
        'progress_label',
    ];

    public function table()
    {
        return $this->belongsTo(Table::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function waiter()
    {
        return $this->belongsTo(User::class, 'waiter_id');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function bill()
    {
        return $this->hasOne(Bill::class);
    }

    public function getTotalAttribute()
    {
        return $this->items->sum('subtotal');
    }

    public function getItemsCountAttribute()
    {
        return $this->items->count();
    }

    public function getProgressAttribute()
    {
        $steps = ['pending' => 1, 'confirmed' => 2, 'preparing' => 2, 'ready' => 3, 'served' => 4, 'completed' => 5];
        return $steps[$this->status] ?? 1;
    }

    public function getProgressLabelAttribute()
    {
        $labels = [1 => 'Placed', 2 => 'Received', 3 => 'Ready', 4 => 'Delivered', 5 => 'Paid'];
        return $labels[$this->progress] ?? 'Placed';
    }

    public function getPaymentStatusAttribute()
    {
        if ($this->relationLoaded('bill') && $this->bill) {
            return $this->bill->payment_status;
        }
        return null;
    }

    public function getBillRequestedAttribute()
    {
        return $this->relationLoaded('bill') && $this->bill !== null;
    }

    public static function generateOrderNumber()
    {
        $prefix = 'ORD-' . now()->format('Ymd') . '-';
        $last = static::where('order_number', 'like', $prefix . '%')
            ->orderBy('order_number', 'desc')
            ->value('order_number');

        $number = $last ? (int) substr($last, -4) + 1 : 1;

        return $prefix . str_pad($number, 4, '0', STR_PAD_LEFT);
    }
}
