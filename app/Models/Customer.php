<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'address',
        'city',
        'tax_id',
        'opening_balance',
        'balance',
        'loyalty_points',
        'branch_id',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'opening_balance' => 'decimal:2',
            'balance' => 'decimal:2',
            'loyalty_points' => 'integer',
        ];
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function bills()
    {
        return $this->hasMany(Bill::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
