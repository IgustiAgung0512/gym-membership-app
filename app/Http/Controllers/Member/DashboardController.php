<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\MembershipPackage;
use App\Models\Payment;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $member = $request->user()->member()->with('package', 'rfidCard')->firstOrFail();
        $attendances = $member->attendances()->latest('check_in_at')->limit(10)->get();
        $totalVisits = $member->attendances()->count();
        $visitsThisMonth = $member->attendances()->whereMonth('check_in_at', now()->month)->count();
        $packages = MembershipPackage::where('is_active', true)->orderBy('price', 'asc')->get();

        return view('member.dashboard', compact(
            'member',
            'attendances',
            'totalVisits',
            'visitsThisMonth',
            'packages'
        ));
    }

    public function renew(Request $request, WhatsAppService $whatsApp)
    {
        $validated = $request->validate([
            'membership_package_id' => 'required|exists:membership_packages,id',
            'payment_method' => 'required|in:qris,transfer',
        ]);

        $member = $request->user()->member;
        if (!$member) {
            abort(404, 'Data member tidak ditemukan.');
        }

        $package = MembershipPackage::findOrFail($validated['membership_package_id']);

        DB::transaction(function () use ($member, $package, $validated) {
            // Hitung tanggal kedaluwarsa baru
            $currentExpire = $member->expire_date && $member->expire_date->isFuture()
                ? $member->expire_date
                : now();

            $newExpire = (clone $currentExpire)->addMonths($package->duration_months);

            $member->update([
                'membership_package_id' => $package->id,
                'expire_date' => $newExpire,
                'status' => 'active',
            ]);

            // Catat pembayaran berstatus paid agar langsung masuk rekap kasir & admin
            Payment::create([
                'member_id' => $member->id,
                'membership_package_id' => $package->id,
                'amount' => $package->price,
                'payment_method' => $validated['payment_method'],
                'payment_date' => now(),
                'status' => 'paid',
                'type' => 'renewal',
            ]);
        });

        // Kirim konfirmasi notifikasi WhatsApp
        try {
            $whatsApp->sendRenewalSuccess($member->fresh(['package', 'user']));
        } catch (\Throwable $e) {
            // Lanjut jika WhatsApp offline
        }

        return redirect()->route('member.dashboard')
            ->with('success', "Pembayaran berhasil! Membership {$package->name} Anda aktif s/d " . $member->fresh()->expire_date->translatedFormat('d F Y') . ".");
    }
}
