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
        $membershipTransactionsCount = Payment::whereMonth('payment_date', now()->month)
            ->whereYear('payment_date', now()->year)
            ->where('status', 'paid')
            ->count();

        $storeOrdersThisMonth = \App\Models\Order::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->where('payment_status', 'paid')
            ->get();

        $storeRevenueThisMonth = $storeOrdersThisMonth->sum('total_amount');
        $storeCostThisMonth = $storeOrdersThisMonth->sum('cost_total');
        $storeProfitThisMonth = $storeRevenueThisMonth - $storeCostThisMonth;
        $storeOrdersCount = $storeOrdersThisMonth->count();
        $storeProfitMargin = $storeRevenueThisMonth > 0 ? round(($storeProfitThisMonth / $storeRevenueThisMonth) * 100, 1) : 0;

        $totalCombinedRevenue = $revenueThisMonth + $storeRevenueThisMonth;
        $totalCombinedProfit = $revenueThisMonth + $storeProfitThisMonth; // Membership 100% gross + Store Net Profit

        $lowStockCount = \App\Models\Product::whereRaw('stock <= min_stock_alert')->count();
        $recentOrders = \App\Models\Order::with('items')->latest()->take(5)->get();

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
            'revenueThisMonth', 'membershipTransactionsCount',
            'storeRevenueThisMonth', 'storeCostThisMonth', 'storeProfitThisMonth', 'storeProfitMargin', 'storeOrdersCount',
            'totalCombinedRevenue', 'totalCombinedProfit', 'lowStockCount',
            'attendanceLast7Days', 'recentCheckins', 'recentMembers', 'recentOrders'
        ));
    }
    public function latestRfidCheckin()
    {
        $recentCheckins = Attendance::with('member.user')->latest('check_in_at')->limit(8)->get()->map(function ($a) {
            return [
                'id' => $a->id,
                'name' => $a->member->user->name ?? 'Member',
                'member_code' => $a->member->member_code ?? '-',
                'photo' => $a->member?->photo ? route('admin.members.photo', $a->member) : null,
                'initial' => strtoupper(substr($a->member->user->name ?? '?', 0, 1)),
                'time' => $a->check_in_at ? $a->check_in_at->format('H:i') . ' WIB' : '-',
                'checkout_time' => $a->check_out_at ? $a->check_out_at->format('H:i') . ' WIB' : null,
            ];
        });
        $todayCheckins = Attendance::whereDate('check_in_at', today())->count();

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
                'recent_checkins' => $recentCheckins,
                'today_checkins' => $todayCheckins,
            ]);
        }

        // Dipakai frontend untuk dedup polling (supaya pesan peringatan
        // tidak "nempel" terus dan bisa reset kalau kartu yang sama di-tap ulang).
        $scanAt = $scan->updated_at ?? $scan->created_at;

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
        | UID BELUM TERDAFTAR / KARTU BELUM TERHUBUNG DENGAN MEMBER
        |--------------------------------------------------------------------------
        */

        if (!$card || !$card->member) {
            return response()->json([
                'exists' => false,
                'reason' => 'unregistered',
                'uid' => $uid,
                'scan_at' => $scanAt,
                'recent_checkins' => $recentCheckins,
                'today_checkins' => $todayCheckins,
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
                'reason' => 'blocked',
                'uid' => $uid,
                'name' => $card->member->user->name,
                'member_code' => $card->member->member_code,
                'scan_at' => $scanAt,
                'recent_checkins' => $recentCheckins,
                'today_checkins' => $todayCheckins,
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
                'reason' => $member->status === 'expired' ? 'expired' : 'inactive',
                'uid' => $uid,
                'name' => $member->user->name,
                'member_code' => $member->member_code,
                'scan_at' => $scanAt,
                'recent_checkins' => $recentCheckins,
                'today_checkins' => $todayCheckins,
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
                'exists' => false,
                'recent_checkins' => $recentCheckins,
                'today_checkins' => $todayCheckins,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | 9. SIAPKAN FOTO MEMBER
        |--------------------------------------------------------------------------
        */

        $photo = null;

        if ($attendance->member?->photo) {
            $photo = route('admin.members.photo', $attendance->member);
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
            'photo' => $photo,
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
            'recent_checkins' => $recentCheckins,
            'today_checkins' => $todayCheckins,
        ]);
    }
}