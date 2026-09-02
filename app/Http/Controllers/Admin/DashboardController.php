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
        $totalActive = Member::where('status', 'active')->count();
        $newThisMonth = Member::whereMonth('join_date', now()->month)->whereYear('join_date', now()->year)->count();
        $expiringSoon = Member::where('status', 'active')
            ->whereNotNull('expire_date')
            ->whereBetween('expire_date', [now(), now()->addDays(7)])
            ->count();
        $todayCheckins = Attendance::whereDate('check_in_at', today())->count();

        $revenueThisMonth = Payment::whereMonth('payment_date', now()->month)
            ->whereYear('payment_date', now()->year)
            ->where('status', 'paid')
            ->sum('amount');

        $attendanceLast7Days = collect(range(6, 0))->map(function ($daysAgo) {
            $date = now()->subDays($daysAgo);
            return [
                'label' => $date->translatedFormat('D'),
                'count' => Attendance::whereDate('check_in_at', $date)->count(),
            ];
        });

        $recentCheckins = Attendance::with('member.user')->latest('check_in_at')->limit(8)->get();
        $recentMembers = Member::with('user', 'package')->latest('join_date')->limit(5)->get();

        return view('admin.dashboard', compact(
            'totalActive', 'newThisMonth', 'expiringSoon', 'todayCheckins',
            'revenueThisMonth', 'attendanceLast7Days', 'recentCheckins', 'recentMembers'
        ));
    }
    public function latestRfidCheckin()
    {
        /*
        |--------------------------------------------------------------------------
        | 1. AMBIL UID TERBARU DARI scan_uids
        |--------------------------------------------------------------------------
        */

        $scan = DB::table('scan_uids')
            ->latest('id')
            ->first();


        /*
        |--------------------------------------------------------------------------
        | Tidak ada scan
        |--------------------------------------------------------------------------
        */

        if (!$scan || !$scan->uid) {
            return response()->json([
                'exists' => false,
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | 2. BERSIHKAN UID
        |--------------------------------------------------------------------------
        */

        $uid = trim($scan->uid);


        /*
        |--------------------------------------------------------------------------
        | 3. CARI KARTU RFID
        |--------------------------------------------------------------------------
        */

        $card = RfidCard::with([
            'member.user',
        ])
            ->where('uid', $uid)
            ->first();


        /*
        |--------------------------------------------------------------------------
        | UID BELUM TERDAFTAR
        |--------------------------------------------------------------------------
        */

        if (!$card) {
            return response()->json([
                'exists' => false,
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | KARTU BELUM TERHUBUNG DENGAN MEMBER
        |--------------------------------------------------------------------------
        */

        if (!$card->member) {
            return response()->json([
                'exists' => false,
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | 4. CEK KARTU DIBLOKIR
        |--------------------------------------------------------------------------
        */

        if ($card->status === 'blocked') {
            return response()->json([
                'exists' => false,
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | 5. AMBIL MEMBER
        |--------------------------------------------------------------------------
        */

        $member = $card->member;


        /*
        |--------------------------------------------------------------------------
        | 6. CEK STATUS MEMBER
        |--------------------------------------------------------------------------
        */

        if ($member->status !== 'active') {
            return response()->json([
                'exists' => false,
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | 7. CEK APAKAH SUDAH CHECK-IN HARI INI
        |--------------------------------------------------------------------------
        |
        | Penting karena dashboard melakukan polling setiap 1 detik.
        |
        */

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
            return response()->json([
                'status'  => 'error',
                'message' => 'Data presensi hari ini tidak ditemukan.'
            ], 404);
        }

            
        /*
        |--------------------------------------------------------------------------
        | 9. SIAPKAN FOTO MEMBER
        |--------------------------------------------------------------------------
        */

        $photo = null;

        if ($attendance->member?->photo) {
            $photo = asset(
                'storage/' . $attendance->member->photo
            );
        }


        /*
        |--------------------------------------------------------------------------
        | 10. KIRIM DATA KE DASHBOARD
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'exists' => true,
            'id' => $attendance->id,
        
            'name' => $attendance->member->user->name,
            'member_code' => $attendance->member->member_code,
            'uid' => $attendance->rfidCard->uid ?? '-',
        
            'time' => $attendance->check_in_at
                ? $attendance->check_in_at->format('H:i:s')
                : '-',
        
            'checkout_time' => $attendance->check_out_at
                ? $attendance->check_out_at->format('H:i:s')
                : null,
        
            'date' => $attendance->check_in_at
                ? $attendance->check_in_at->format('d M Y')
                : '-',
        
            'action' => $attendance->check_out_at
                ? 'checkout'
                : 'checkin',
        ]);
    }
}