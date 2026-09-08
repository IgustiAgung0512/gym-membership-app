<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\MembershipPackage;
use App\Models\Order;
use App\Models\Payment;
use App\Services\QrisService;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $member = $request->user()->member()->with('package', 'rfidCard')->firstOrFail();
        $attendances = $member->attendances()->latest('check_in_at')->limit(10)->get();
        $totalVisits = $member->attendances()->count();
        $visitsThisMonth = $member->attendances()->whereMonth('check_in_at', now()->month)->count();
        $packages = MembershipPackage::where('is_active', true)->orderBy('price', 'asc')->get();

        // Ambil riwayat checkout & pesanan toko terbaru member
        $recentOrders = Order::where('member_id', $member->id)
            ->whereIn('payment_status', ['paid', 'pending'])
            ->with(['items.product'])
            ->latest()
            ->take(5)
            ->get();

        return view('member.dashboard', compact(
            'member',
            'attendances',
            'totalVisits',
            'visitsThisMonth',
            'packages',
            'recentOrders'
        ));
    }

    /**
     * Inisiasi tagihan perpanjangan membership mandiri (QRIS Dinamis / Transfer)
     */
    public function initiateRenewal(Request $request)
    {
        $validated = $request->validate([
            'membership_package_id' => 'required|exists:membership_packages,id',
            'payment_method' => 'required|in:qris,transfer',
        ]);

        $user = Auth::user();
        $member = $user->member;
        if (!$member) {
            return response()->json([
                'success' => false,
                'message' => 'Data member tidak ditemukan.',
            ], 404);
        }

        $package = MembershipPackage::findOrFail($validated['membership_package_id']);

        return DB::transaction(function () use ($member, $package, $validated) {
            // Generate Invoice Number: RNW-YYMMDD-XXXX
            $todayCount = Payment::whereDate('created_at', today())->count() + 1;
            $invoiceNumber = 'RNW-' . now()->format('ymd') . '-' . str_pad($todayCount, 4, '0', STR_PAD_LEFT);

            // Buat record pembayaran dengan status pending
            $payment = Payment::create([
                'invoice_number' => $invoiceNumber,
                'member_id' => $member->id,
                'membership_package_id' => $package->id,
                'amount' => $package->price,
                'payment_method' => $validated['payment_method'],
                'type' => 'renewal',
                'payment_date' => now(),
                'status' => 'pending',
                'notes' => 'Perpanjangan Paket Mandiri via Portal Member',
            ]);

            // Hitung proyeksi masa aktif baru
            $currentExpire = $member->expire_date && $member->expire_date->isFuture()
                ? $member->expire_date
                : now();
            $newExpirePreview = (clone $currentExpire)->addMonths($package->duration_months)->translatedFormat('d F Y');

            // Generate Payload QRIS
            $qrisData = QrisService::generateForRenewal($payment);

            return response()->json([
                'success' => true,
                'message' => 'Tagihan perpanjangan berhasil dibuat. Silakan scan QRIS untuk membayar.',
                'payment' => $payment->load('package'),
                'package' => $package,
                'new_expire_preview' => $newExpirePreview,
                'qris' => $qrisData,
                'status_url' => route('member.renew.status', $payment->id),
                'simulate_url' => route('member.renew.simulate', $payment->id),
                'cancel_url' => route('member.renew.cancel', $payment->id),
            ]);
        });
    }

    /**
     * Polling status pembayaran perpanjangan membership
     */
    public function renewalStatus(Payment $payment)
    {
        $member = Auth::user()->member;
        if ($payment->member_id !== $member->id) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak: Anda bukan pemilik transaksi ini.',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'status' => $payment->status,
            'is_paid' => $payment->status === 'paid',
            'payment' => $payment->fresh(['package']),
            'member' => $member->fresh(['package']),
            'new_expire_date' => $member->fresh()->expire_date?->translatedFormat('d F Y'),
        ]);
    }

    /**
     * Simulator pembayaran sukses perpanjangan membership (Non-Production Only)
     */
    public function simulateRenewal(Payment $payment, WhatsAppService $whatsApp)
    {
        $member = Auth::user()->member;
        if ($payment->member_id !== $member->id) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak: Anda bukan pemilik transaksi ini.',
            ], 403);
        }

        // Keamanan: Cegah simulasi berjalan di production
        if (app()->isProduction() || config('services.midtrans.is_production', false)) {
            Log::warning("Blocked renewal simulation attempt in production for Payment {$payment->invoice_number} by Member {$member->id}");
            return response()->json([
                'success' => false,
                'message' => 'Mode simulasi dinonaktifkan di lingkungan produksi (Production). Silakan lakukan pembayaran menggunakan QRIS resmi.',
            ], 403);
        }

        if ($payment->status === 'pending') {
            DB::transaction(function () use ($payment, $member) {
                $payment->update([
                    'status' => 'paid',
                    'payment_date' => now(),
                ]);

                $package = $payment->package ?: MembershipPackage::findOrFail($payment->membership_package_id);

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

                Log::info("Simulated renewal payment SUCCESS for Payment {$payment->invoice_number} by Member ID {$member->id}");
            });

            // Kirim konfirmasi notifikasi WhatsApp
            try {
                $whatsApp->sendRenewalSuccess($member->fresh(['package', 'user']));
            } catch (\Throwable $e) {
                Log::warning("Failed to send WhatsApp renewal notification: " . $e->getMessage());
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Simulasi pembayaran QRIS berhasil! Membership Anda aktif kembali.',
            'payment' => $payment->fresh(['package']),
            'member' => $member->fresh(['package']),
            'new_expire_date' => $member->fresh()->expire_date?->translatedFormat('d F Y'),
        ]);
    }

    /**
     * Batalkan transaksi perpanjangan pending
     */
    public function cancelRenewal(Payment $payment)
    {
        $member = Auth::user()->member;
        if ($payment->member_id !== $member->id) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak.',
            ], 403);
        }

        if ($payment->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi yang sudah lunas tidak dapat dibatalkan mandiri.',
            ], 422);
        }

        $payment->update([
            'status' => 'cancelled',
            'notes' => ($payment->notes ? $payment->notes . ' | ' : '') . 'Dibatalkan oleh member',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Transaksi perpanjangan berhasil dibatalkan.',
        ]);
    }

    /**
     * Fallback perpanjangan manual / direct transfer via standard form submit
     */
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
            $todayCount = Payment::whereDate('created_at', today())->count() + 1;
            $invoiceNumber = 'RNW-' . now()->format('ymd') . '-' . str_pad($todayCount, 4, '0', STR_PAD_LEFT);

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
                'invoice_number' => $invoiceNumber,
                'member_id' => $member->id,
                'membership_package_id' => $package->id,
                'amount' => $package->price,
                'payment_method' => $validated['payment_method'],
                'type' => 'renewal',
                'payment_date' => now(),
                'status' => 'paid',
                'notes' => 'Perpanjangan Paket Mandiri via Portal Member',
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
