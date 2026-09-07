<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'member_id',
        'customer_name',
        'subtotal',
        'discount',
        'total_amount',
        'cost_total',
        'payment_method',
        'cash_received',
        'cash_change',
        'payment_status',
        'notes',
        'created_by',
        'pickup_status',
        'picked_up_at',
        'picked_up_by',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'cost_total' => 'decimal:2',
            'cash_received' => 'decimal:2',
            'cash_change' => 'decimal:2',
            'picked_up_at' => 'datetime',
        ];
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function cashier()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function picker()
    {
        return $this->belongsTo(User::class, 'picked_up_by');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getPaymentMethodLabelAttribute(): string
    {
        return match ($this->payment_method) {
            'cash' => 'Tunai (Cash)',
            'qris' => 'QRIS',
            'transfer' => 'Transfer Bank',
            default => 'Lainnya',
        };
    }

    public function getPaymentMethodIconAttribute(): string
    {
        return match ($this->payment_method) {
            'cash' => '💵',
            'qris' => '📱',
            'transfer' => '🏦',
            default => '💳',
        };
    }

    public function getProfitAttribute(): float
    {
        return (float) $this->total_amount - (float) $this->cost_total;
    }

    public function isReadyForPickup(): bool
    {
        return $this->payment_status === 'paid' && $this->pickup_status === 'ready_for_pickup';
    }

    public function isPickedUp(): bool
    {
        return $this->pickup_status === 'picked_up';
    }
}
