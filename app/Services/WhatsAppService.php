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
        $this->apiUrl = config('services.whatsapp.url', 'https://api.fonnte.com/send');
        $this->token = config('services.whatsapp.token');
    }

    public function sendRegistrationNotice(Member $member): bool
    {
        $expire = $member->expire_date?->translatedFormat('d M Y') ?? '-';

        $message = "Halo *{$member->user->name}* 👋\n\n"
            . "Pendaftaran membership GYM kamu berhasil! 🎉\n\n"
            . "Kode Member: *{$member->member_code}*\n"
            . "Paket: *" . ($member->package->name ?? '-') . "*\n"
            . "Aktif sampai: *{$expire}*\n\n"
            . "Tunjukkan kartu RFID kamu di pintu masuk untuk check-in. "
            . "Sampai jumpa di gym! 💪";

        return $this->send($member, $member->user->phone, $message, 'registration');
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
        WhatsappLog::create([
            'member_id' => $member?->id,
            'phone' => $phone,
            'type' => $type,
            'message' => $message,
            'status' => $status,
            'response' => $response,
        ]);
    }
}
