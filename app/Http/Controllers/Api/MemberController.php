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
        $request->validate([
            'uid' => 'required|string|max:255',
        ]);

        $checkUid = ScanUid::find(1);

        if (!$checkUid) {
            $checkUid = ScanUid::create([
                'uid' => $request->uid,
            ]);
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

        // Kartu belum pernah didaftarkan, atau sudah didaftarkan tapi belum
        // dihubungkan ke member manapun -> jangan buat attendance apa pun.
        if (!$card || !$card->member) {
            return response()->json([
                'success' => false,
                'reason' => 'unregistered',
                'message' => 'Kartu belum terdaftar.',
            ], 404);
        }

        $member = $card->member;

        // Member ditemukan tapi statusnya bukan aktif (expired/inactive)
        // -> jangan catat kehadiran.
        if ($member->status !== 'active') {
            return response()->json([
                'success' => false,
                'reason' => $member->status === 'expired' ? 'expired' : 'inactive',
                'message' => $member->status === 'expired'
                    ? 'Member sudah expired.'
                    : 'Member tidak aktif.',
                'member_code' => $member->member_code,
            ], 403);
        }

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