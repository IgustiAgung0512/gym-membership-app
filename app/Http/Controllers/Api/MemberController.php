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
        $deviceKey = $request->header('X-Device-Key') 
            ?? $request->header('x-device-key') 
            ?? $request->input('device_key') 
            ?? $request->query('device_key');

        $configuredKey = config('services.rfid.device_key');
        if (!empty($configuredKey) && $deviceKey !== $configuredKey) {
            return response()->json([
                'success' => false,
                'reason' => 'unauthorized_device',
                'message' => 'Perangkat tidak dikenali. Pastikan header X-Device-Key atau parameter device_key sesuai.',
            ], 401);
        }

        $rawUid = $request->input('uid') ?? $request->query('uid') ?? '';
        $rawUid = trim((string) $rawUid);

        if (empty($rawUid)) {
            return response()->json([
                'success' => false,
                'message' => 'Parameter UID tidak boleh kosong.',
            ], 400);
        }

        $trimmedColonUid = strtoupper(trim($rawUid, " :\t\n\r\0\x0B"));
        $cleanUid = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $rawUid));
        $storedUid = !empty($trimmedColonUid) ? $trimmedColonUid : $cleanUid;

        $checkUid = ScanUid::find(1);
        if (!$checkUid) {
            $checkUid = ScanUid::create([
                'uid' => $storedUid,
            ]);
        } else {
            $checkUid->uid = $storedUid;
            $checkUid->touch();
            $checkUid->save();
        }

        $card = RfidCard::with([
            'member.user',
        ])
            ->where(function ($query) use ($rawUid, $cleanUid, $trimmedColonUid) {
                $query->where('uid', $rawUid)
                    ->orWhere('uid', $trimmedColonUid)
                    ->orWhere('uid', $cleanUid);
            })
            ->first();

        // Kartu belum pernah didaftarkan, atau sudah didaftarkan tapi belum
        // dihubungkan ke member manapun -> jangan buat attendance apa pun.
        if (!$card || !$card->member) {
            return response()->json([
                'success' => false,
                'reason' => 'unregistered',
                'message' => 'Kartu belum terdaftar (' . ($cleanUid ?: $rawUid) . ').',
                'scanned_uid' => $cleanUid ?: $rawUid,
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

        // Cari sesi presensi terbuka untuk member ini pada hari ini
        $openAttendance = Attendance::with([
            'member.user',
            'rfidCard',
        ])
            ->where('member_id', $member->id)
            ->where('rfid_card_id', $card->id)
            ->where('method', 'rfid')
            ->whereDate('check_in_at', today())
            ->whereNull('check_out_at')
            ->latest('id')
            ->first();

        if ($openAttendance) {
            // Proteksi double-tap kartu (jika tap kedua terjadi dalam jeda < 3 detik)
            if ($openAttendance->check_in_at && $openAttendance->check_in_at->diffInSeconds(now()) < 3) {
                return response()->json([
                    'success' => true,
                    'action'  => 'checkin_duplicate_ignored',
                    'message' => 'Kartu baru saja di-tap (Check-in aktif). Mohon tunggu beberapa detik sebelum Check-out.',
                    'member_name' => $member->user->name,
                    'member_code' => $member->member_code,
                    'check_in_at' => $openAttendance->check_in_at->format('H:i:s'),
                    'attendance'  => $openAttendance,
                ], 200);
            }

            // Ada sesi yang masih terbuka -> tap ini = CHECK-OUT.
            $openAttendance->update([
                'check_out_at' => now(),
            ]);

            $attendance = $openAttendance->fresh([
                'member.user',
                'rfidCard',
            ]);

            return response()->json([
                'success'     => true,
                'action'      => 'checkout',
                'message'     => 'Check-out berhasil untuk ' . $member->user->name . '!',
                'member_name' => $member->user->name,
                'member_code' => $member->member_code,
                'check_in_at' => $attendance->check_in_at->format('H:i:s'),
                'check_out_at'=> $attendance->check_out_at ? $attendance->check_out_at->format('H:i:s') : null,
                'attendance'  => $attendance,
            ], 200);
        } else {
            // Tidak ada sesi terbuka hari ini -> tap ini = CHECK-IN baru.
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

            // Kirim notifikasi WA check-in ke member
            try {
                app(\App\Services\WhatsAppService::class)->sendCheckInNotice($member);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Gagal kirim notifikasi check-in WA: ' . $e->getMessage());
            }

            return response()->json([
                'success'     => true,
                'action'      => 'checkin',
                'message'     => 'Check-in berhasil untuk ' . $member->user->name . '!',
                'member_name' => $member->user->name,
                'member_code' => $member->member_code,
                'check_in_at' => $attendance->check_in_at->format('H:i:s'),
                'attendance'  => $attendance,
            ], 200);
        }
    }
}