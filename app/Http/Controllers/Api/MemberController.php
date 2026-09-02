<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\RfidCard;
use App\Models\ScanUid;
use Carbon\Carbon;
use Illuminate\Http\Request;

class MemberController extends Controller
{
    public function store(Request $request)
    {
        $checkUid = ScanUid::find(1);

        if (!$checkUid) {
            $data = $request->validate([
                'uid'  => 'required|string|max:255',
            ]);
            ScanUid::create($data);
        } else {
            $checkUid->update([
                'uid' => $request->uid
            ]);
        }

        $uid = trim($checkUid->uid);

        $card = RfidCard::with([
            'member.user',
        ])
            ->where('uid', $uid)
            ->first();

        $member = $card->member;

        $attendance = Attendance::with([
            'member.user',
            'rfidCard',
        ])
            ->where('member_id', $member->id)
            ->where('rfid_card_id', $card->id)
            ->where('method', 'rfid')
            ->whereDate('check_in_at', today())
            ->latest('id')
            ->first();

        if (!$attendance) {
            $attendance = Attendance::create([
                'member_id' => $member->id,
                'rfid_card_id' => $card->id,
                'method' => 'rfid',
                'check_in_at' => now(),
            ]);
            $attendance->load([
                'member.user',
                'rfidCard',
            ]);
        } else {
            $attendance = Attendance::find($attendance->id);
            if (is_null($attendance->check_out_at)) {
                $attendance->update([
                    'check_out_at' => now() // Shortcut Laravel untuk Carbon::now()
                ]);
            }
        }

        return response()->json([
            'success'   => true,
            'message' => 'Check-in berhasil!',
            'attendance' => $attendance,
        ], 200);
        
    }
}