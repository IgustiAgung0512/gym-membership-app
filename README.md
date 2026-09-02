# GymPulse — Aplikasi Membership GYM (Laravel)

Aplikasi manajemen membership gym dengan:
- **Dashboard Admin**: kelola member, paket membership, kartu RFID, absensi, dan log WhatsApp.
- **Dashboard Member**: kartu member digital, sisa masa aktif, riwayat check-in.
- **Integrasi RFID**: endpoint API untuk alat RFID reader (ESP32/ESP8266 + RC522) melakukan check-in otomatis.
- **Integrasi WhatsApp**: notifikasi otomatis saat member baru terdaftar & saat check-in (via gateway seperti Fonnte).
- **Responsif**: dibangun dengan Tailwind CDN + Alpine.js, tampil rapi di HP maupun desktop.

Catatan: proyek ini dibuat tanpa menjalankan `composer install` di lingkungan pembuatannya (tidak ada akses ke Packagist), jadi folder `vendor/`, `public/index.php`, dan file inti framework lain **belum** disertakan. Ikuti langkah instalasi di bawah — hanya makan 5–10 menit.

## 1. Instalasi

```bash
# 1. Buat proyek Laravel 11 kosong
composer create-project laravel/laravel gym-membership-app
cd gym-membership-app

# 2. Salin seluruh isi folder hasil generate ini ke proyek baru,
#    timpa (overwrite) file yang sama: app/, bootstrap/app.php, config/services.php,
#    database/, resources/views/, routes/, composer.json, .env.example

# 3. Install dependency (tidak ada tambahan package di luar bawaan Laravel)
composer install

# 4. Siapkan .env
cp .env.example .env
php artisan key:generate

# 5. Buat database MySQL kosong bernama `gym_membership` (atau sesuaikan .env),
#    lalu jalankan migrasi + data contoh
php artisan migrate --seed

# 6. Jalankan server
php artisan serve
```

Buka `http://localhost:8000`.

**Login demo (dari seeder):**
- Admin: `admin@gym.test` / `password`
- Member: `budi@gym.test` / `password`

## 2. Konfigurasi WhatsApp

Aplikasi ini memakai pola HTTP gateway generik (default contoh: [Fonnte](https://fonnte.com), populer & mudah untuk WA gateway di Indonesia). Ganti provider lain (Wablas, WA Business API resmi, dll) cukup dengan menyesuaikan `app/Services/WhatsAppService.php`.

Di `.env`:
```
WHATSAPP_API_URL=https://api.fonnte.com/send
WHATSAPP_API_TOKEN=isi-token-dari-dashboard-provider
```

Notifikasi otomatis terkirim saat:
1. Admin mendaftarkan member baru (`sendRegistrationNotice`)
2. Member check-in via RFID (`sendCheckInNotice`)
3. (Terjadwal) H-3 sebelum membership berakhir (`sendExpiryReminder`, lihat `routes/console.php` — aktifkan cron dengan `php artisan schedule:work` saat development, atau daftarkan cron `* * * * * php artisan schedule:run` di server produksi).

Semua pengiriman (berhasil/gagal) tercatat di menu **Log WhatsApp** pada dashboard admin.

## 3. Integrasi Alat RFID

Endpoint yang dipanggil alat reader setiap kartu ditempelkan:

```
POST /api/rfid/scan
Header: X-Device-Key: <RFID_DEVICE_KEY dari .env>
Content-Type: application/json

{ "uid": "A1B2C3D4" }
```

Contoh respons sukses:
```json
{
  "status": "success",
  "message": "Check-in berhasil",
  "member_name": "Budi Santoso",
  "member_code": "GYM-2601-0001",
  "check_in_at": "07:12:03",
  "days_remaining": 18
}
```
Status lain yang mungkin dikembalikan: `unknown_card`, `blocked`, `unassigned`, `expired` — alat bisa menampilkan pesan/menyalakan LED merah sesuai status.

### Contoh firmware ESP32 + RC522 (ringkas)
```cpp
#include <WiFi.h>
#include <HTTPClient.h>

void sendScan(String uid) {
  HTTPClient http;
  http.begin("http://ALAMAT-SERVER/api/rfid/scan");
  http.addHeader("Content-Type", "application/json");
  http.addHeader("X-Device-Key", "ubah-dengan-kunci-rahasia");
  String body = "{\"uid\":\"" + uid + "\"}";
  int code = http.POST(body);
  String response = http.getString();
  // tampilkan `response` (JSON) ke LCD / logika buzzer di sini
  http.end();
}
```
Daftarkan dulu UID kartu baru lewat menu **Kartu RFID** di dashboard admin sebelum kartu bisa dipakai check-in.

## 4. Struktur fitur utama

| Area | Path |
|---|---|
| Dashboard admin (statistik & chart) | `/admin/dashboard` |
| Kelola member (CRUD, perpanjang) | `/admin/members` |
| Kelola paket membership | `/admin/packages` |
| Kelola kartu RFID | `/admin/rfid` |
| Riwayat absensi/check-in | `/admin/attendance` |
| Log notifikasi WhatsApp | `/admin/whatsapp-logs` |
| Dashboard member | `/member/dashboard` |
| API check-in RFID | `POST /api/rfid/scan` |

## 5. Yang bisa dikembangkan lebih lanjut
- Upload foto profil member (butuh `php artisan storage:link` + form file).
- Halaman pembayaran/invoice lebih detail (tabel `payments` sudah tersedia).
- Push notification tambahan (email) memakai Notification class Laravel.
- Role tambahan seperti "resepsionis" dengan akses terbatas (tinggal tambah value di kolom `role` & middleware).
