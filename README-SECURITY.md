# 🛡️ Panduan Keamanan Sistem (Security Guide) · GymPulse

Dokumen ini menjelaskan arsitektur keamanan, lapisan proteksi (*defense-in-depth*), proteksi **Payment Gateway & QRIS Dinamis (Anti-Retas / Anti-Tukar)**, konfigurasi aman, detail seluruh file yang ditambahkan/diubah, serta langkah-langkah pemeliharaan untuk memastikan aplikasi **GymPulse** dan perangkat **RFID Reader (Arduino/ESP32)** tetap aman dari berbagai ancaman siber (hacker, fake webhook, brute force, sniffing, clickjacking, DoS, broken access control / IDOR).

---

## 📑 Daftar Isi
1. [Arsitektur Keamanan Global](#-arsitektur-keamanan-global)
2. [Keamanan Khusus Payment Gateway & QRIS (Anti-Retas & Anti-Tukar)](#-keamanan-khusus-payment-gateway--qris-anti-retas--anti-tukar)
   - [Lapisan 1: Integritas QRIS EMVCo & CRC-16 Checksum](#1-integritas-qris-emvco--crc-16-checksum-anti-qr-swapping)
   - [Lapisan 2: Tanda Tangan Kriptografis (HMAC-SHA256 & Midtrans SHA-512)](#2-tanda-tangan-kriptografis-hmac-sha256--midtrans-sha-512)
   - [Lapisan 3: Validasi Kesesuaian Nominal (Anti-Amount Tampering)](#3-validasi-kesesuaian-nominal-anti-amount-tampering)
   - [Lapisan 4: Active Server-to-Server Double Verification (Inquiry API)](#4-active-server-to-server-double-verification-inquiry-api)
   - [Lapisan 5: State Machine & Idempotency Webhook](#5-state-machine--idempotency-webhook)
   - [Lapisan 6: Kunci Sandbox / Simulasi di Lingkungan Produksi](#6-kunci-sandbox--simulasi-di-lingkungan-produksi)
   - [Lapisan 7: Proteksi IDOR & Otorisasi Pemilik Pesanan](#7-proteksi-idor--otorisasi-pemilik-pesanan)
   - [Lapisan 8: Rate Limiting Khusus Webhook & Checkout](#8-rate-limiting-khusus-webhook--checkout)
3. [Matriks File Keamanan yang Ditambahkan & Diubah](#-matriks-file-keamanan-yang-ditambahkan--diubah)
4. [Lapisan Proteksi Umum Lainnya](#-lapisan-proteksi-umum-lainnya)
5. [Keamanan Integrasi Hardware RFID (Arduino/ESP32)](#-keamanan-integrasi-hardware-rfid-arduinoesp32)
6. [Panduan Konfigurasi Produksi (.env)](#-panduan-konfigurasi-produksi-env)
7. [Pengujian Keamanan Otomatis (Test Suite)](#-pengujian-keamanan-otomatis-test-suite)

---

## 🏛️ Arsitektur Keamanan Global

GymPulse menerapkan prinsip **Defense-in-Depth** (perlindungan berlapis):

```
[ Browser / HP Member / Internet ] 
         │
         ▼  (1) HTTP Security Headers (Anti-Clickjacking, Anti-MIME Sniffing)
[ Global Middleware ]
         │
         ▼  (2) Rate Limiter & Throttling (Anti-Brute Force, Anti-DoS, Rate Webhook 60/m)
[ Routing Layer ]
         │
         ▼  (3) Role-Based Access Control (RBAC: Admin, Cashier, Member) & IDOR Protection
[ Controllers ]
         │
         ▼  (4) Cryptographic Verification (SHA-512 & HMAC-SHA256), DB Row Locking, Active Gateway Inquiry
[ Database & Payment Gateway ]
```

---

## 💳 Keamanan Khusus Payment Gateway & QRIS (Anti-Retas & Anti-Tukar)

Untuk mencegah peretasan, pemalsuan bukti bayar, penukaran gambar QR (*QR swapping*), pemotongan harga sepihak, atau fake webhook dari peretas, sistem GymPulse menerapkan **8 Lapis Proteksi**:

### 1. Integritas QRIS EMVCo & CRC-16 Checksum (Anti-QR Swapping)
* **Lokasi:** `app/Services/QrisService.php` (`calculateCrc16()` & `verifyPayloadChecksum()`)
* **Mekanisme:**
  * Setiap payload QRIS dinamis dibuat mengikuti standar resmi **Bank Indonesia (ASPI)** dan **EMVCo Tag-Length-Value (TLV)**.
  * Dilengkapi perhitungan matematis **CRC-16-CCITT (Polynomial `0x1021`, Initial `0xFFFF`)** pada tag `6304`.
  * Jika hacker mencoba mengubah 1 karakter pun pada data QR (misalnya mengganti rekening merchant tujuan atau menurunkan harga), nilai checksum otomatis menjadi tidak valid dan QR akan ditolak oleh aplikasi perbankan/e-wallet.

### 2. Tanda Tangan Kriptografis (HMAC-SHA256 & Midtrans SHA-512)
* **Lokasi:** `app/Services/QrisService.php` & `app/Http/Controllers/Api/PaymentWebhookController.php`
* **Mekanisme:**
  * Setiap notifikasi webhook dari Payment Gateway diwajibkan menyertakan `signature_key`.
  * Server GymPulse menghitung tanda tangan menggunakan rumus SHA-512 resmi:
    $$\text{Expected Signature} = \text{hash('sha512', } \text{order\_id} + \text{status\_code} + \text{gross\_amount} + \text{ServerKey} \text{)}$$
  * Tanda tangan dicocokkan menggunakan `hash_equals()` untuk **mencegah serangan Timing Attack**.
  * Jika ada pihak luar mengirim POST fake settlement tanpa `ServerKey` rahasia, request langsung **DITOLAK DENGAN HTTP 403 FORBIDDEN** dan dicatat dalam log keamanan (*Critical Security Alert*).

### 3. Validasi Kesesuaian Nominal (Anti-Amount Tampering)
* **Lokasi:** `app/Http/Controllers/Api/PaymentWebhookController.php`
* **Mekanisme:**
  * Perhitungan total belanja dihitung murni di sisi backend (*Server-Side Calculation*) berdasarkan harga produk di database (`Product::lockForUpdate()`).
  * Webhook memeriksa apakah nominal yang dibayarkan (`gross_amount`) sama persis hingga satuan Rupiah dengan `$order->total_amount`.
  * Jika nominal kurang (misal tagihan Rp 50.000 tapi dibayar Rp 1.000), sistem menolak memproses pesanan dan menandai status sebagai error nominal.

### 4. Active Server-to-Server Double Verification (Inquiry API)
* **Lokasi:** `app/Http/Controllers/Api/PaymentWebhookController.php`
* **Mekanisme:**
  * Saat menerima webhook notifikasi, server GymPulse tidak hanya percaya pada isi pesan webhook, tetapi melakukan **inquiry balik (Double-Check)** langsung dari Server GymPulse ke Server Midtrans API (`GET https://api.midtrans.com/v2/{order_id}/status`) menggunakan kredensial HTTP Basic Auth rahasia.
  * Status hanya diubah menjadi `paid` jika server resmi Midtrans mengonfirmasi bahwa transaksi tersebut benar-benar sukses (*settlement* / *capture* dengan `fraud_status: accept`).

### 5. State Machine & Idempotency Webhook
* **Lokasi:** `app/Http/Controllers/Api/PaymentWebhookController.php`
* **Mekanisme:**
  * **Idempotency:** Jika webhook dikirim berulang kali oleh gateway (misal karena retry network), pesanan yang sudah `paid` tidak akan memotong stok ganda dan membalas `200 OK`.
  * **Auto-Restore Inventory:** Jika status pembayaran dinyatakan `expire`, `cancel`, atau `deny`, pesanan diubah ke `cancelled` dan seluruh stok barang yang sempat dipesan otomatis dikembalikan (*increment stock*) ke etalase kasir.

### 6. Kunci Sandbox / Simulasi di Lingkungan Produksi
* **Lokasi:**
  * `app/Http/Controllers/Member/StoreController.php` (`simulateQris()`)
  * `app/Http/Controllers/Cashier/PosController.php` (`simulateQris()`)
  * `app/Http/Controllers/Admin/PosController.php` (`simulateQris()`)
* **Mekanisme:**
  * Endpoint simulasi pembayaran (`/simulate`) otomatis **DIKUNCI / DINONAKTIFKAN TOTAL (HTTP 403)** ketika aplikasi berjalan di lingkungan produksi (`APP_ENV=production` atau `MIDTRANS_IS_PRODUCTION=true`).
  * Tidak ada celah bagi pengguna di server live untuk mendapatkan barang gratis dengan menekan simulasi bayar.

### 7. Proteksi IDOR & Otorisasi Pemilik Pesanan
* **Lokasi:** `app/Http/Controllers/Member/StoreController.php`
* **Mekanisme:**
  * Member hanya dapat melihat struk, mengecek status, atau membatalkan pesanan miliknya sendiri (`$order->member_id === $member->id`).
  * Upaya mengakses atau memanipulasi pesanan member lain langsung diblokir dengan `403 Forbidden`.

### 8. Rate Limiting Khusus Webhook & Checkout
* **Lokasi:** `app/Providers/AppServiceProvider.php` & `routes/api.php`
* **Mekanisme:**
  * `throttle:webhook` membatasi request webhook maksimal **60 req/menit per IP** untuk mencegah flooding DoS pada gateway callback.
  * `throttle:checkout` membatasi pembuatan pesanan maksimal **15 checkout/menit per user** untuk mencegah spam pesanan palsu (*Cart Exhaustion Attack*).

---

## 📊 Matriks File Keamanan yang Ditambahkan & Diubah

| Status | File | Bagian / Modul | Perubahan Utama |
| :---: | :---| :---| :---|
| 🟢 **BARU** | `app/Http/Controllers/Api/PaymentWebhookController.php` | Payment Security | Handler webhook Midtrans dengan SHA-512 signature check, active status inquiry, amount check, dan auto-restore stock. |
| 🟢 **BARU** | `tests/Feature/PaymentSecurityTest.php` | Testing Suite | 7 pengujian otomatis keamanan pembayaran (Anti-Tampering, Fake Signature, IDOR, EMVCo CRC-16, Production Lock). |
| 🟡 **DIUBAH** | `app/Services/QrisService.php` | Payment Core | Implementasi standar EMVCo CRC-16-CCITT, HMAC-SHA256 signature generator, dan Midtrans verifier. |
| 🟡 **DIUBAH** | `config/services.php` | Konfigurasi | Menambahkan konfigurasi resmi Midtrans (`server_key`, `is_production`, dll). |
| 🟡 **DIUBAH** | `routes/api.php` | API Routing | Mendaftarkan route webhook `/api/payment/webhook` & `/api/midtrans/notification` dengan `throttle:webhook`. |
| 🟡 **DIUBAH** | `routes/web.php` | Web Routing | Mengunci checkout dengan `throttle:checkout`. |
| 🟡 **DIUBAH** | `app/Providers/AppServiceProvider.php` | Service Provider | Mendaftarkan rate limiter `webhook` dan `checkout`. |
| 🟡 **DIUBAH** | `app/Http/Controllers/Member/StoreController.php` | Member Store | Lockout simulasi di production, IDOR check, dan logging. |
| 🟡 **DIUBAH** | `app/Http/Controllers/Cashier/PosController.php` | Cashier POS | Lockout simulasi di production dan audit logging. |
| 🟡 **DIUBAH** | `app/Http/Controllers/Admin/PosController.php` | Admin POS | Lockout simulasi di production dan audit logging. |
| 🟡 **DIUBAH** | `README-SECURITY.md` | Dokumentasi | Buku panduan lengkap keamanan Payment Gateway & QRIS. |

---

## 🛡️ Lapisan Proteksi Umum Lainnya

1. **Anti Brute-Force Login:** Pembatasan 5 kali percobaan gagal per menit dengan hitung mundur otomatis.
2. **HTTP Security Headers:** `X-Frame-Options: SAMEORIGIN`, `X-Content-Type-Options: nosniff`, `Referrer-Policy`, dll.
3. **Session Hardening & Regeneration:** Session di-*regenerate* otomatis saat login & ganti password untuk mencegah *Session Fixation*.
4. **Halaman Error Anti Information Disclosure:** Error 403, 404, 419, 429, 500 tidak membocorkan query database atau path file server.

---

## ⚡ Keamanan Integrasi Hardware RFID (Arduino/ESP32)

1. **Format Payload Tetap Standar:** Endpoint `POST /api/members/add` dan `POST /api/rfid/scan` menerima format JSON `{ "uid": "..." }` tanpa merusak firmware lama (*Zero Breaking Changes*).
2. **Perlindungan Anti-Flooding / DoS:** Dilindungi oleh middleware `throttle:api` dengan batas **120 request per menit**.
3. **Proteksi Debounce Hardware:** Jeda tap kartu < 3 detik diabaikan untuk mencegah kartu yang tertempel lama langsung memicu check-out seketika.

---

## 🚀 Panduan Konfigurasi Produksi (.env)

Saat aplikasi siap dijalankan di server live / produksi, pastikan file `.env` memuat konfigurasi berikut:

```env
# 1. Nonaktifkan mode debug & set environment ke production
APP_ENV=production
APP_DEBUG=false

# 2. URL Domain Resmi
APP_URL=https://gym.namadomainanda.com

# 3. Kredensial Resmi Payment Gateway Midtrans (Production)
MIDTRANS_SERVER_KEY=Mid-server-xxxxxxxxxxxxxxxxxxxx
MIDTRANS_CLIENT_KEY=Mid-client-xxxxxxxxxxxxxxxxxxxx
MIDTRANS_MERCHANT_ID=Gxxxxxxxx
MIDTRANS_IS_PRODUCTION=true
MIDTRANS_IS_SANITIZED=true
MIDTRANS_IS_3DS=true

# 4. Keamanan Sesi HTTPS
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax
```

---

## 🧪 Pengujian Keamanan Otomatis (Test Suite)

Seluruh mekanisme keamanan telah diuji secara otomatis dan lulus 100%:

```powershell
# Jalankan seluruh pengujian keamanan
php artisan test
```

### Hasil Verifikasi Test Suite:
```
PASS  Tests\Feature\PaymentSecurityTest
✓ webhook rejects invalid cryptographic signature
✓ webhook rejects gross amount mismatch
✓ webhook accepts valid signature and marks order paid
✓ webhook cancels order and restores stock on expire
✓ member cannot access or cancel other members order idor
✓ qris service emvco crc16 integrity
✓ simulate qris blocked in production

PASS  Tests\Feature\SecurityHardeningTest
✓ security headers are present
✓ unauthenticated user cannot access rfid helper routes
✓ login brute force protection

Tests: 12 passed (36 assertions)
```

---

*Terakhir diperbarui: September 2026 · GymPulse Security & Fintech Engineering Team*
