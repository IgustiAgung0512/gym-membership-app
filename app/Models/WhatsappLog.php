<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsappLog extends Model
{
    use HasFactory;

    protected $fillable = ['member_id', 'phone', 'type', 'message', 'status', 'response'];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }
}
