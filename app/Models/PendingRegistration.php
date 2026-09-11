<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PendingRegistration extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'membership_package_id',
        'name',
        'email',
        'phone',
        'gender',
        'birth_date',
        'address',
        'password',
        'amount',
        'payment_method',
        'status',
        'paid_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'birth_date' => 'date',
            'paid_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function package()
    {
        return $this->belongsTo(MembershipPackage::class, 'membership_package_id');
    }
}
