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

        $openAttendance = Attendance::with([
            'member.user',
            'rfidCard',
        ])
            ->where('member_id', $member->id)
            ->where('rfid_card_id', $card->id)
            ->where('method', 'rfid')
            ->whereNull('check_out_at')
            ->latest('id')
            ->first();

        if ($openAttendance) {
            // Ada sesi yang masih terbuka (belum checkout) -> tap ini = CHECK-OUT.
            $openAttendance->update([
                'check_out_at' => now(),
            ]);

            $attendance = $openAttendance->fresh([
                'member.user',
                'rfidCard',
            ]);
        } else {
            // Tidak ada sesi terbuka -> tap ini = CHECK-IN baru.
            // Tidak dibatasi tanggal, jadi member boleh checkin/checkout
            // berkali-kali dalam sehari (misal pagi & sore).
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
        }

        return response()->json([
            'success'   => true,
            'message' => 'Check-in berhasil!',
            'attendance' => $attendance,
        ], 200);
        
    }
}