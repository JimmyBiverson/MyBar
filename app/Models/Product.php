<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'sku',
        'barcode',
        'category_id',
        'unit_id',
        'brand',
        'description',
        'cost_price',
        'selling_price',
        'wholesale_price',
        'tax_method',
        'tax_rate',
        'reorder_level',
        'current_stock',
        'opening_stock',
        'stock_value',
        'image',
        'is_active',
        'branch_id',
    ];

    protected $appends = ['stock_status', 'stock'];

    protected function casts(): array
    {
        return [
            'cost_price' => 'decimal:2',
            'selling_price' => 'decimal:2',
            'wholesale_price' => 'decimal:2',
            'current_stock' => 'decimal:2',
            'opening_stock' => 'decimal:2',
            'stock_value' => 'decimal:2',
            'reorder_level' => 'decimal:2',
            'tax_rate' => 'decimal:2',
        ];
    }

    public function getStockAttribute()
    {
        return $this->current_stock;
    }

    public function getStockStatusAttribute(): string
    {
        $lowThreshold = (float) Setting::get('low_stock_threshold', 10);
        $mediumThreshold = (float) Setting::get('medium_stock_threshold', 20);
        $stock = (float) $this->current_stock;

        if ($stock <= $lowThreshold) {
            return 'low';
        }

        if ($stock <= $mediumThreshold) {
            return 'medium';
        }

        return 'good';
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function batches()
    {
        return $this->hasMany(Batch::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function billItems()
    {
        return $this->hasMany(BillItem::class);
    }
}
