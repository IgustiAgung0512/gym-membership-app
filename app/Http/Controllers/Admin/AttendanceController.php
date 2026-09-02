<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Member;
use App\Models\Payment;
use App\Models\RfidCard;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        /*
        |--------------------------------------------------------------------------
        | STATISTIK DASHBOARD
        |--------------------------------------------------------------------------
        */

        $totalActive = Member::where('status', 'active')->count();

        $newThisMonth = Member::whereMonth('join_date', now()->month)
            ->whereYear('join_date', now()->year)
            ->count();

        $expiringSoon = Member::where('status', 'active')
            ->whereNotNull('expire_date')
            ->whereBetween('expire_date', [
                now(),
                now()->addDays(7),
            ])
            ->count();

        $todayCheckins = Attendance::whereDate(
            'check_in_at',
            today()
        )->count();

        $revenueThisMonth = Payment::whereMonth(
                'payment_date',
                now()->month
            )
            ->whereYear(
                'payment_date',
                now()->year
            )
            ->where('status', 'paid')
            ->sum('amount');


        /*
        |--------------------------------------------------------------------------
        | KUNJUNGAN 7 HARI TERAKHIR
        |--------------------------------------------------------------------------
        */

        $attendanceLast7Days = collect(range(6, 0))->map(function ($daysAgo) {
            $date = now()->subDays($daysAgo);

            return [
                'label' => $date->translatedFormat('D'),
                'count' => Attendance::whereDate(
                    'check_in_at',
                    $date
                )->count(),
            ];
        });


        /*
        |--------------------------------------------------------------------------
        | CHECK-IN TERBARU
        |--------------------------------------------------------------------------
        */

        $recentCheckins = Attendance::with([
                'member.user',
                'rfidCard',
            ])
            ->latest('check_in_at')
            ->limit(8)
            ->get();


        /*
        |--------------------------------------------------------------------------
        | MEMBER TERBARU
        |--------------------------------------------------------------------------
        */

        $recentMembers = Member::with([
                'user',
                'package',
            ])
            ->latest('join_date')
            ->limit(5)
            ->get();


        /*
        |--------------------------------------------------------------------------
        | DASHBOARD VIEW
        |--------------------------------------------------------------------------
        */

        return view('admin.dashboard', compact(
            'totalActive',
            'newThisMonth',
            'expiringSoon',
            'todayCheckins',
            'revenueThisMonth',
            'attendanceLast7Days',
            'recentCheckins',
            'recentMembers'
        ));
    }


    /**
     * ========================================================================
     * RFID REALTIME HANDLER (QUEUE BASED - 1 TAP = 1 AKSI)
     * ========================================================================
     */
    public function latestRfidCheckin()
    {
        // 1. Ambil data scan antrean dari ESP32
        $scan = DB::table('scan_uids')->latest('id')->first();

        // Jika tidak ada antrean scan baru dari ESP32, hentikan proses (browser polling diam)
        if (!$scan || empty($scan->uid)) {
            return response()->json([
                'exists' => false,
            ]);
        }

        $uid = trim(strtoupper($scan->uid));

        // 2. HAPUS scan dari tabel scan_uids agar polling detik berikutnya tidak memprosesnya lagi
        DB::table('scan_uids')->where('id', $scan->id)->delete();

        // 3. Cari kartu RFID & validasi member
        $card = RfidCard::with(['member.user'])
            ->where('uid', $uid)
            ->first();

        if (!$card || !$card->member || $card->status === 'blocked' || $card->member->status !== 'active') {
            return response()->json([
                'exists' => false,
            ]);
        }

        $member = $card->member;

        // 4. Cari sesi absensi terakhir member yang BELUM Check-Out
        $activeAttendance = Attendance::with(['member.user', 'rfidCard'])
            ->where('member_id', $member->id)
            ->where('method', 'rfid')
            ->whereNull('check_out_at')
            ->latest('id')
            ->first();

        // 5. Eksekusi Check-In / Check-Out
        if ($activeAttendance) {
            // TAP KEDUA -> LAKUKAN CHECK-OUT
            $activeAttendance->update([
                'check_out_at' => now(),
            ]);

            $activeAttendance->refresh();
            $attendance = $activeAttendance;
            $action = 'checkout';
        } else {
            // TAP PERTAMA (atau sesi baru) -> LAKUKAN CHECK-IN
            $attendance = Attendance::create([
                'member_id'    => $member->id,
                'rfid_card_id' => $card->id,
                'method'       => 'rfid',
                'check_in_at'  => now(),
                'check_out_at' => null,
            ]);

            $attendance->load(['member.user', 'rfidCard']);
            $action = 'checkin';
        }

        // 6. Siapkan foto member
        $photo = null;
        if ($attendance->member?->photo) {
            $photo = asset('storage/' . $attendance->member->photo);
        }

        // 7. Response JSON ke Dashboard
        return response()->json([
            'exists'        => true,
            'id'            => $attendance->id,
            'name'          => $attendance->member->user->name ?? '-',
            'member_code'   => $attendance->member->member_code ?? '-',
            'uid'           => $attendance->rfidCard->uid ?? '-',
            'photo'         => $photo,
            'time'          => $attendance->check_in_at ? $attendance->check_in_at->format('H:i:s') : '-',
            'checkout_time' => $attendance->check_out_at ? $attendance->check_out_at->format('H:i:s') : null,
            'date'          => $attendance->check_in_at ? $attendance->check_in_at->format('d M Y') : '-',
            'action'        => $action,
        ]);
    }
}