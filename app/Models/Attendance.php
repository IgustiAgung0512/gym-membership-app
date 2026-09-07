<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = ['member_id', 'rfid_card_id', 'method', 'check_in_at', 'check_out_at'];

    protected $appends = ['duration_formatted'];

    protected function casts(): array
    {
        return [
            'check_in_at' => 'datetime',
            'check_out_at' => 'datetime',
        ];
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function rfidCard()
    {
        return $this->belongsTo(RfidCard::class);
    }

    /**
     * Format durasi sesi secara dinamis: Jam, Menit, dan Detik.
     */
    public function getDurationFormattedAttribute(): string
    {
        if (!$this->check_in_at) {
            return '-';
        }

        $end = $this->check_out_at ?? now();
        $totalSeconds = max(0, (int) $this->check_in_at->diffInSeconds($end));

        if ($totalSeconds < 60) {
            return $totalSeconds . ' Detik';
        }

        $hours = floor($totalSeconds / 3600);
        $minutes = floor(($totalSeconds % 3600) / 60);
        $seconds = $totalSeconds % 60;

        $parts = [];
        if ($hours > 0) {
            $parts[] = $hours . ' Jam';
        }
        if ($minutes > 0) {
            $parts[] = $minutes . ' Menit';
        }
        if ($seconds > 0) {
            $parts[] = $seconds . ' Detik';
        }

        return implode(' ', $parts);
    }
}
