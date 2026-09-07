<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\RfidCard;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Endpoint ini dipanggil oleh alat RFID reader (mis. ESP32/ESP8266 + RC522/PN532)
 * setiap kali sebuah kartu ditempelkan.
 *
 * Contoh request dari perangkat:
 *   POST /api/rfid/scan
 *   Header: X-Device-Key: <RFID_DEVICE_KEY dari .env>
 *   Body (JSON): { "uid": "A1B2C3D4" }
 *
 * Response JSON dipakai perangkat untuk menampilkan pesan di LCD / buzzer:
 *   { "status": "success", "message": "...", "member_name": "...", "photo": "..." }
 */
class RfidScanController extends Controller
{
    public function scan(Request $request, WhatsAppService $whatsapp)
    {
        $deviceKey = $request->header('X-Device-Key');

        if (config('services.rfid.device_key') && $deviceKey !== config('services.rfid.device_key')) {
            return response()->json(['status' => 'error', 'message' => 'Perangkat tidak dikenali.'], 401);
        }

        $data = $request->validate([
            'uid' => ['required', 'string', 'max:50'],
        ]);

        \App\Models\ScanUid::updateOrCreate(
            ['id' => 1],
            ['uid' => trim($data['uid'])]
        );

        $card = RfidCard::with('member.user')->where('uid', $data['uid'])->first();

        if (! $card) {
            return response()->json([
                'status' => 'unknown_card',
                'message' => 'Kartu tidak terdaftar.',
            ], 404);
        }

        if ($card->status === 'blocked') {
            return response()->json([
                'status' => 'blocked',
                'message' => 'Kartu diblokir. Hubungi admin.',
            ], 403);
        }

        $member = $card->member;

        if (! $member) {
            return response()->json([
                'status' => 'unassigned',
                'message' => 'Kartu belum ditautkan ke member.',
            ], 404);
        }

        if ($member->status !== 'active' || ($member->expire_date && $member->expire_date->isPast())) {
            if ($member->expire_date && $member->expire_date->isPast() && $member->status === 'active') {
                $member->update(['status' => 'expired']);
            }

            return response()->json([
                'status' => 'expired',
                'message' => 'Membership tidak aktif / sudah berakhir.',
                'member_name' => $member->user->name,
                'expire_date' => optional($member->expire_date)->format('Y-m-d'),
            ], 403);
        }

        // Cari sesi presensi terbuka untuk member ini pada hari ini
        $openAttendance = Attendance::where('member_id', $member->id)
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
                    'status' => 'success',
                    'action' => 'checkin_duplicate_ignored',
                    'message' => 'Kartu baru saja di-tap (Check-in aktif). Mohon tunggu beberapa detik sebelum Check-out.',
                    'member_name' => $member->user->name,
                    'member_code' => $member->member_code,
                    'check_in_at' => $openAttendance->check_in_at->format('H:i:s'),
                ]);
            }

            $openAttendance->update([
                'check_out_at' => now(),
            ]);

            return response()->json([
                'status' => 'success',
                'action' => 'checkout',
                'message' => 'Check-out berhasil',
                'member_name' => $member->user->name,
                'member_code' => $member->member_code,
                'check_in_at' => $openAttendance->check_in_at->format('H:i:s'),
                'check_out_at' => $openAttendance->check_out_at ? $openAttendance->check_out_at->format('H:i:s') : now()->format('H:i:s'),
                'expire_date' => optional($member->expire_date)->format('Y-m-d'),
                'days_remaining' => $member->daysRemaining(),
            ]);
        }

        $attendance = Attendance::create([
            'member_id' => $member->id,
            'rfid_card_id' => $card->id,
            'method' => 'rfid',
            'check_in_at' => now(),
        ]);

        // Kirim notifikasi WA secara async supaya tidak memperlambat respons ke alat RFID
        try {
            $whatsapp->sendCheckInNotice($member);
        } catch (\Throwable $e) {
            Log::warning('Gagal kirim notifikasi check-in WA: ' . $e->getMessage());
        }

        return response()->json([
            'status' => 'success',
            'action' => 'checkin',
            'message' => 'Check-in berhasil',
            'member_name' => $member->user->name,
            'member_code' => $member->member_code,
            'check_in_at' => $attendance->check_in_at->format('H:i:s'),
            'expire_date' => optional($member->expire_date)->format('Y-m-d'),
            'days_remaining' => $member->daysRemaining(),
        ]);
    }
}
