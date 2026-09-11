<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\MembershipPackage;
use App\Models\Payment;
use App\Models\PendingRegistration;
use App\Models\User;
use App\Services\QrisService;
use App\Services\WhatsAppService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class OnlineRegistrationController extends Controller
{
    /**
     * Memastikan tabel pending_registrations ada (Self-healing untuk Vercel/Production)
     */
    public static function ensureTableExists(): void
    {
        try {
            if (!Schema::hasTable('pending_registrations')) {
                try {
                    Artisan::call('migrate', ['--force' => true]);
                } catch (\Throwable $e) {
                    Log::warning('Auto-migration command failed, falling back to Schema::create: ' . $e->getMessage());
                }

                if (!Schema::hasTable('pending_registrations')) {
                    Schema::create('pending_registrations', function (Blueprint $table) {
                        $table->id();
                        $table->string('invoice_number', 50)->unique();
                        $table->foreignId('membership_package_id')->constrained('membership_packages')->cascadeOnDelete();
                        $table->string('name');
                        $table->string('email');
                        $table->string('phone', 30);
                        $table->string('gender', 5)->nullable();
                        $table->date('birth_date')->nullable();
                        $table->text('address')->nullable();
                        $table->string('password');
                        $table->decimal('amount', 12, 2);
                        $table->string('payment_method', 30)->default('qris');
                        $table->string('status', 20)->default('pending');
                        $table->timestamp('paid_at')->nullable();
                        $table->timestamp('expires_at')->nullable();
                        $table->timestamps();
                    });
                }
            }
        } catch (\Throwable $e) {
            Log::error('Failed to ensure pending_registrations table: ' . $e->getMessage());
        }
    }

    /**
     * Memulai checkout pendaftaran member online (Generate QRIS Dinamis)
     */
    public function initiate(Request $request)
    {
        self::ensureTableExists();
        $data = $request->validate([
            'membership_package_id' => ['required', 'exists:membership_packages,id'],
            'name'                  => ['required', 'string', 'max:255'],
            'email'                 => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone'                 => ['required', 'string', 'max:20'],
            'password'              => ['required', 'string', 'min:6'],
            'gender'                => ['nullable', 'string', 'in:L,P,male,female,pria,wanita'],
            'birth_date'            => ['nullable', 'date'],
            'address'               => ['nullable', 'string', 'max:500'],
        ], [
            'email.unique' => 'Email ini sudah terdaftar sebagai member. Silakan login atau gunakan email lain.',
            'password.min' => 'Password minimal 6 karakter.',
            'membership_package_id.required' => 'Pilih salah satu paket keanggotaan.',
        ]);

        $package = MembershipPackage::findOrFail($data['membership_package_id']);

        if (!$package->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Paket keanggotaan ini sedang tidak aktif.',
            ], 422);
        }

        // Normalize gender to 'L' or 'P'
        $gender = null;
        if (!empty($data['gender'])) {
            $g = strtolower($data['gender']);
            $gender = in_array($g, ['l', 'male', 'pria']) ? 'L' : (in_array($g, ['p', 'female', 'wanita']) ? 'P' : null);
        }

        // Buat nomor invoice pendaftaran unik: REG-YYMMDD-XXXX
        $invoiceNumber = 'REG-' . now()->format('ymd') . '-' . strtoupper(Str::random(5));

        $pending = PendingRegistration::create([
            'invoice_number'        => $invoiceNumber,
            'membership_package_id' => $package->id,
            'name'                  => trim($data['name']),
            'email'                 => strtolower(trim($data['email'])),
            'phone'                 => trim($data['phone']),
            'gender'                => $gender,
            'birth_date'            => $data['birth_date'] ?? null,
            'address'               => $data['address'] ?? null,
            'password'              => Hash::make($data['password']),
            'amount'                => $package->price,
            'payment_method'        => 'qris',
            'status'                => 'pending',
            'expires_at'            => now()->addMinutes(15),
        ]);

        // Generate QRIS Dinamis
        $qrisData = QrisService::generateGeneric(
            invoiceNumber: $invoiceNumber,
            amount: (int) $package->price,
            id: $pending->id,
            merchantName: 'GYMPULSE REGISTRATION'
        );

        return response()->json([
            'success' => true,
            'message' => 'Tagihan pendaftaran berhasil dibuat. Silakan selesaikan pembayaran.',
            'invoice_number' => $invoiceNumber,
            'invoice' => $invoiceNumber,
            'package' => [
                'id' => $package->id,
                'name' => $package->name,
                'duration_months' => $package->duration_months,
                'price' => (float) $package->price,
                'price_formatted' => 'Rp ' . number_format($package->price, 0, ',', '.'),
            ],
            'registration' => [
                'name' => $pending->name,
                'email' => $pending->email,
                'phone' => $pending->phone,
            ],
            'qris' => $qrisData,
            'qris_data' => $qrisData['qr_string'] ?? '',
            'qris_qr_url' => $qrisData['qr_url'] ?? '',
        ]);
    }

    /**
     * Polling status pembayaran pendaftaran online
     */
    public function status(string $invoiceNumber)
    {
        self::ensureTableExists();

        $pending = PendingRegistration::with('package')
            ->where('invoice_number', $invoiceNumber)
            ->first();

        if (!$pending) {
            return response()->json([
                'success' => false,
                'status'  => 'not_found',
                'message' => 'Data pendaftaran tidak ditemukan.',
            ], 404);
        }

        if ($pending->status === 'paid') {
            $user = User::where('email', $pending->email)->first();
            $member = $user ? $user->member : null;

            return response()->json([
                'success'       => true,
                'status'        => 'paid',
                'message'       => 'Pembayaran terkonfirmasi! Akun member Anda telah aktif.',
                'member_code'   => $member?->member_code,
                'member_number' => $member?->member_code,
                'member_name'   => $pending->name,
                'package_name'  => $pending->package?->name,
                'expire_date'   => optional($member?->expire_date)->translatedFormat('d F Y'),
                'login_url'     => route('login'),
            ]);
        }

        if ($pending->status === 'pending' && $pending->expires_at && $pending->expires_at->isPast()) {
            $pending->update(['status' => 'expired']);
            return response()->json([
                'success' => false,
                'status'  => 'expired',
                'message' => 'Sesi pembayaran pendaftaran telah kedaluwarsa.',
            ]);
        }

        return response()->json([
            'success' => true,
            'status'  => $pending->status,
            'message' => 'Menunggu konfirmasi pembayaran QRIS...',
        ]);
    }

    /**
     * Simulator pembayaran pendaftaran online (Development / Local Environment)
     */
    public function simulate(string $invoiceNumber)
    {
        self::ensureTableExists();

        $isProduction = config('services.midtrans.is_production', env('MIDTRANS_IS_PRODUCTION', false));
        if (app()->environment('production') && $isProduction) {
            return response()->json([
                'success' => false,
                'message' => 'Simulator pembayaran dinonaktifkan di mode production.',
            ], 403);
        }

        $pending = PendingRegistration::with('package')
            ->where('invoice_number', $invoiceNumber)
            ->first();

        if (!$pending) {
            return response()->json([
                'success' => false,
                'message' => 'Data pendaftaran tidak ditemukan.',
            ], 404);
        }

        if ($pending->status === 'paid') {
            return response()->json([
                'success'       => true,
                'message'       => 'Pendaftaran sudah berstatus lunas.',
                'member_code'   => $pending->email,
                'member_number' => $pending->email,
            ]);
        }

        $member = self::settleRegistration($pending);

        return response()->json([
            'success'       => true,
            'message'       => 'Pembayaran pendaftaran berhasil diverifikasi & akun member telah aktif!',
            'member_code'   => $member->member_code,
            'member_number' => $member->member_code,
            'member_name'   => $member->user->name,
            'package_name'  => $pending->package?->name,
            'expire_date'   => optional($member->expire_date)->translatedFormat('d F Y'),
        ]);
    }

    /**
     * Pembatalan checkout pendaftaran online
     */
    public function cancel(string $invoiceNumber)
    {
        self::ensureTableExists();

        $pending = PendingRegistration::where('invoice_number', $invoiceNumber)->first();

        if (!$pending) {
            return response()->json([
                'success' => false,
                'message' => 'Data pendaftaran tidak ditemukan.',
            ], 404);
        }

        if ($pending->status === 'pending') {
            $pending->update(['status' => 'cancelled']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Pendaftaran online telah dibatalkan.',
        ]);
    }

    /**
     * Transaksi atomik pembuatan User & Member aktif saat pembayaran lunas
     */
    public static function settleRegistration(PendingRegistration $pending): Member
    {
        return DB::transaction(function () use ($pending) {
            // 1. Cek apakah user sudah terbuat
            $user = User::where('email', $pending->email)->first();
            if (!$user) {
                $user = User::create([
                    'name'                 => $pending->name,
                    'email'                => $pending->email,
                    'phone'                => $pending->phone,
                    'password'             => $pending->password,
                    'role'                 => 'member',
                    'must_change_password' => false,
                ]);
            }

            $package = $pending->package ?: MembershipPackage::find($pending->membership_package_id);

            // 2. Cek apakah member sudah ada
            $member = Member::where('user_id', $user->id)->first();
            if (!$member) {
                $member = Member::create([
                    'user_id'               => $user->id,
                    'membership_package_id' => $package->id,
                    'member_code'           => Member::generateUniqueMemberCode(),
                    'gender'                => $pending->gender,
                    'birth_date'            => $pending->birth_date,
                    'address'               => $pending->address,
                    'join_date'             => now(),
                    'expire_date'           => now()->addMonths($package->duration_months),
                    'status'                => 'active',
                ]);
            }

            // 3. Catat payment lunas
            Payment::updateOrCreate(
                ['invoice_number' => $pending->invoice_number],
                [
                    'member_id'             => $member->id,
                    'membership_package_id' => $package->id,
                    'amount'                => $pending->amount,
                    'payment_method'        => $pending->payment_method ?: 'qris',
                    'type'                  => 'registration',
                    'payment_date'          => now(),
                    'status'                => 'paid',
                    'notes'                 => 'Pendaftaran Online via QRIS (Menunggu Pengambilan Kartu RFID di Kasir)',
                ]
            );

            // 4. Update status pending registration
            $pending->update([
                'status'  => 'paid',
                'paid_at' => now(),
            ]);

            // 5. Kirim notifikasi WhatsApp pendaftaran sukses
            try {
                app(WhatsAppService::class)->sendRegistrationNotice($member->fresh(['user', 'package']));
            } catch (\Throwable $e) {
                Log::warning('Gagal kirim notifikasi WA pendaftaran online: ' . $e->getMessage());
            }

            return $member;
        });
    }
}
