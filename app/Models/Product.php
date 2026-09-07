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

    protected $appends = [
        'image_url',
    ];

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function isLowStock(): bool
    {
        return $this->stock <= $this->min_stock_alert;
    }

    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image) {
            return null;
        }

        if (str_starts_with($this->image, 'http://') || str_starts_with($this->image, 'https://') || str_starts_with($this->image, '/')) {
            return $this->image;
        }

        if (str_starts_with($this->image, 'storage/')) {
            return '/' . $this->image;
        }

        return '/storage/' . $this->image;
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
