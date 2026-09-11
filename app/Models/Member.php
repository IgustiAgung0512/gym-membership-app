<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'membership_package_id', 'member_code', 'photo',
        'address', 'birth_date', 'gender', 'join_date', 'expire_date', 'status',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'join_date' => 'date',
            'expire_date' => 'date',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function package()
    {
        return $this->belongsTo(MembershipPackage::class, 'membership_package_id');
    }

    public function rfidCard()
    {
        return $this->hasOne(RfidCard::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function whatsappLogs()
    {
        return $this->hasMany(WhatsappLog::class);
    }

    public function isExpiringSoon(int $days = 7): bool
    {
        return $this->expire_date && now()->diffInDays($this->expire_date, false) <= $days
            && now()->diffInDays($this->expire_date, false) >= 0;
    }

    public function daysRemaining(): ?int
    {
        if (! $this->expire_date) {
            return null;
        }

        return (int) now()->startOfDay()->diffInDays($this->expire_date, false);
    }

    /**
     * Generate Nomor Member Unik & Anti-Duplikat (GYM-YYMM-XXXX)
     */
    public static function generateUniqueMemberCode(): string
    {
        $prefix = 'GYM-' . now()->format('ym') . '-';
        
        $lastMember = self::where('member_code', 'LIKE', "{$prefix}%")
            ->orderBy('member_code', 'desc')
            ->first();

        $nextNumber = 1;
        if ($lastMember && preg_match('/-(\d+)$/', $lastMember->member_code, $matches)) {
            $nextNumber = ((int) $matches[1]) + 1;
        } else {
            $nextNumber = self::count() + 1;
        }

        $code = $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

        // Fail-safe jika kode sudah terpakai
        while (self::where('member_code', $code)->exists()) {
            $nextNumber++;
            $code = $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
        }

        return $code;
    }
}
