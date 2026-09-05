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
        'category',
        'price',
        'cost_price',
        'stock',
        'min_stock_alert',
        'unit',
        'image',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'cost_price' => 'decimal:2',
            'stock' => 'integer',
            'min_stock_alert' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function isLowStock(): bool
    {
        return $this->stock <= $this->min_stock_alert;
    }

    public function getCategoryLabelAttribute(): string
    {
        return match ($this->category) {
            'drinks' => 'Minuman & Elektrolit',
            'supplements' => 'Suplemen & Whey',
            'snacks' => 'Camilan Sehat',
            'gear' => 'Aksesoris & Apparel',
            default => 'Lainnya',
        };
    }

    public function getCategoryIconAttribute(): string
    {
        return match ($this->category) {
            'drinks' => '🥤',
            'supplements' => '💪',
            'snacks' => '🥑',
            'gear' => '🏋️',
            default => '📦',
        };
    }
}
