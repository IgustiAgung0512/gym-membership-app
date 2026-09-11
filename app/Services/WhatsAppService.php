<?php

namespace App\Services;

use App\Models\Member;
use App\Models\WhatsappLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Wrapper tipis di atas WhatsApp Gateway (default: Fonnte, https://fonnte.com).
 * Ganti method send() jika memakai provider lain (Wablas, WA Business API resmi, dll)
 * — cukup sesuaikan endpoint & payload, sisanya (logging, format nomor) tetap sama.
 */
class WhatsAppService
{
    protected string $apiUrl;
    protected ?string $token;

    public function __construct()
    {
        $this->apiUrl = config('services.whatsapp.url') ?: env('WHATSAPP_API_URL', 'https://api.fonnte.com/send');
        $this->token = config('services.whatsapp.token') ?: env('WHATSAPP_API_TOKEN') ?: env('WHATSAPP_TOKEN') ?: env('FONNTE_TOKEN');
    }

    public function sendRegistrationNotice(Member $member): bool
    {
        $expire = $member->expire_date?->translatedFormat('d M Y') ?? '-';

        $loginInfo = "\n🔑 *Akun Login Portal Member:*\n"
            . "• Email: *{$member->user->email}*\n"
            . "• Link Portal: " . url('/login') . "\n";

        if (!empty($member->temp_password)) {
            $loginInfo .= "• Password Sementara: *{$member->temp_password}*\n";
        }

        $message = "Halo *{$member->user->name}* 👋\n\n"
            . "Pendaftaran membership GymPulse kamu berhasil & telah lunas! 🎉\n\n"
            . "📋 *Detail Keanggotaan:*\n"
            . "• Nomor Member: *{$member->member_code}*\n"
            . "• Paket: *" . ($member->package->name ?? '-') . "*\n"
            . "• Masa Aktif s.d: *{$expire}*\n"
            . "• Status: *Aktif* ✅\n"
            . $loginInfo . "\n"
            . "🏷️ *Pengambilan Kartu RFID:*\n"
            . "Silakan datang ke front-desk GymPulse dan sebutkan No. Member (*{$member->member_code}*) untuk mengambil kartu akses RFID fisik kamu.\n\n"
            . "Sampai jumpa di gym & selamat berlatih! 💪";

        return $this->send($member, $member->user->phone, $message, 'registration');
    }

    public function sendRegistrationSuccess(Member $member, ?string $rawPassword = null): bool
    {
        $expire = $member->expire_date?->translatedFormat('d M Y') ?? '-';
        $loginInfo = '';
        if ($rawPassword) {
            $loginInfo = "\n🔑 *Akun Login Portal Member:*\n"
                . "• Email: *{$member->user->email}*\n"
                . "• Password: *{$rawPassword}*\n"
                . "• Link Login: " . url('/login') . "\n";
        }

        $message = "Halo *{$member->user->name}* 👋\n\n"
            . "Pendaftaran membership GymPulse kamu berhasil! 🎉\n\n"
            . "Kode Member: *{$member->member_code}*\n"
            . "Paket: *" . ($member->package->name ?? '-') . "*\n"
            . "Aktif sampai: *{$expire}*\n"
            . $loginInfo . "\n"
            . "Tunjukkan kartu RFID kamu di pintu masuk untuk check-in. "
            . "Sampai jumpa di gym! 💪";

        return $this->send($member, $member->user->phone, $message, 'registration');
    }

    public function sendRenewalSuccess(Member $member): bool
    {
        $expire = $member->expire_date?->translatedFormat('d M Y') ?? '-';
        $message = "Halo *{$member->user->name}* 👋\n\n"
            . "Pembayaran perpanjangan membership GymPulse kamu telah berhasil dikonfirmasi! 🎉\n\n"
            . "Paket: *" . ($member->package->name ?? '-') . "*\n"
            . "Masa Aktif Baru: *{$expire}*\n"
            . "Status: *Aktif* ✅\n\n"
            . "Terima kasih telah memperpanjang membership kamu. Selamat berlatih kembali! 💪";

        return $this->send($member, $member->user->phone, $message, 'renewal');
    }

    public function sendPasswordResetNotice(Member $member, string $tempPassword): bool
    {
        $message = "Halo *{$member->user->name}* 👋\n\n"
            . "Password akun portal member GymPulse kamu telah direset oleh Admin.\n\n"
            . "🔑 *Detail Login Akun:*\n"
            . "• Email: *{$member->user->email}*\n"
            . "• Password Sementara: *{$tempPassword}*\n"
            . "• Link Login: " . url('/login') . "\n\n"
            . "⚠️ _Silakan login dan kamu akan diminta untuk membuat password baru yang aman._\n\n"
            . "Sampai jumpa di gym! 💪";

        return $this->send($member, $member->user->phone, $message, 'password_reset');
    }

    public function sendCheckInNotice(Member $member): bool
    {
        $time = now()->translatedFormat('H:i');
        $message = "Check-in berhasil ✅\n\nHalo {$member->user->name}, kamu tercatat masuk gym pukul *{$time}*. Selamat berlatih! 💪";

        return $this->send($member, $member->user->phone, $message, 'checkin');
    }

    public function sendExpiryReminder(Member $member): bool
    {
        $expire = $member->expire_date?->translatedFormat('d M Y') ?? '-';
        $message = "Halo {$member->user->name}, membership GYM kamu akan berakhir pada *{$expire}*. "
            . "Segera perpanjang di kasir agar kamu tetap bisa check-in ya! 🙏";

        return $this->send($member, $member->user->phone, $message, 'expiry_reminder');
    }

    protected function send(?Member $member, ?string $phone, string $message, string $type): bool
    {
        $phone = $this->normalizePhone($phone);

        if (! $phone) {
            $this->log($member, $phone ?? '-', $type, $message, 'failed', 'Nomor telepon kosong/tidak valid');
            return false;
        }

        if (! $this->token) {
            // Belum dikonfigurasi -> dicatat sebagai gagal supaya kelihatan di dashboard admin
            $this->log($member, $phone, $type, $message, 'failed', 'WHATSAPP_TOKEN belum diatur di .env');
            return false;
        }

        try {
            $response = Http::asForm()
                ->withHeaders(['Authorization' => $this->token])
                ->timeout(10)
                ->post($this->apiUrl, [
                    'target' => $phone,
                    'message' => $message,
                ]);

            $status = $response->successful() ? 'sent' : 'failed';
            $this->log($member, $phone, $type, $message, $status, $response->body());

            return $status === 'sent';
        } catch (\Throwable $e) {
            Log::error('WhatsApp send failed: ' . $e->getMessage());
            $this->log($member, $phone, $type, $message, 'failed', $e->getMessage());
            return false;
        }
    }

    protected function normalizePhone(?string $phone): ?string
    {
        if (! $phone) {
            return null;
        }

        $phone = preg_replace('/[^0-9+]/', '', $phone);

        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        } elseif (str_starts_with($phone, '+62')) {
            $phone = substr($phone, 1);
        } elseif (! str_starts_with($phone, '62')) {
            $phone = '62' . $phone;
        }

        return $phone;
    }

    protected function log(?Member $member, string $phone, string $type, string $message, string $status, ?string $response): void
    {
        try {
            WhatsappLog::create([
                'member_id' => $member?->id,
                'phone' => $phone,
                'type' => $type,
                'message' => $message,
                'status' => $status,
                'response' => $response,
            ]);
        } catch (\Throwable $e) {
            Log::warning('Failed saving WhatsApp log to database: ' . $e->getMessage());
        }
    }
}
