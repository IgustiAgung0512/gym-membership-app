# 🏋️‍♂️ GymPulse — Smart RFID Gym Management System

Dokumentasi lengkap sistem manajemen keanggotaan gym modern berbasis **Laravel 11**, **Tailwind CSS**, dan **Alpine.js** yang terintegrasi dengan alat pembaca kartu **RFID Hardware (ESP32/ESP8266 + RC522)** serta **WhatsApp Gateway (Fonnte)**.

---

## 📑 Daftar Isi
1. [Tabel Komparasi Perubahan (Sebelum vs Sesudah)](#-tabel-komparasi-perubahan-sebelum-vs-sesudah)
2. [Matriks File yang Diubah & Ditukar](#-matriks-file-yang-diubah--ditukar)
3. [Rincian Detail Pembaruan Setiap Fitur](#-rincian-detail-pembaruan-setiap-fitur)
4. [Tata Cara & Alur Penggunaan Sistem (SOP)](#-tata-cara--alur-penggunaan-sistem-sop)
5. [Panduan Instalasi & Menjalankan Aplikasi](#-panduan-instalasi--menjalankan-aplikasi)
6. [Kredensial Akun Demo](#-kredensial-akun-demo)
7. [Dokumentasi Integrasi Hardware RFID & WhatsApp](#-dokumentasi-integrasi-hardware-rfid--whatsapp)
8. [Struktur Rute Aplikasi](#-struktur-rute-aplikasi)

---

## 🔄 Tabel Komparasi Perubahan (Sebelum vs Sesudah)

| Modul / Halaman | Kondisi Sebelumnya (Lama) | Kondisi Terbaru (Sekarang) |
| :--- | :--- | :--- |
| **Tema Desain (Theme)** | Dark theme gelap dengan teks putih & aksen neon berlebih (*AI slop*). | **Clean Light Theme** profesional (latar putih `#FFFFFF`/`#F8FAFC`, teks slate gelap `#0F172A`, aksen hijau kebugaran alami `#65A30D`). |
| **Halaman Depan (`/`)** | Langsung mengarah ke login, belum ada landing page informasi gym. | **Landing Page Komprehensif**: Tentang Gym, Galeri Foto Fasilitas, Simulasi Scan RFID, Kalkulator BMI, Paket Harga, Jadwal & FAQ. |
| **Navbar Beranda** | Menu terlalu padat dan teks tombol terpotong di layar HP (`Daftar Membe...`). | **Navbar Responsif & Rapi**: Teks ber-`whitespace-nowrap`, tombol compact, dan menu drawer mobile yang proporsional. |
| **Halaman Login (`/login`)** | Desain polos tanpa tombol kembali. | **Login Light Theme**: Kartu putih modern dengan tombol *"← Kembali ke Beranda"*, toggle password, dan validasi visual. |
| **Dashboard Admin** | Teks putih di atas kartu putih (angka statistik & form tidak terlihat). | **Kontras Tinggi & Jelas**: Angka statistik, tabel, form input, dan sidebar gelap teratur dengan kontras teks sangat tegas. |
| **Tampilan HP Admin RFID & Absensi** | Tabel desktop kaku dan kolom aksi/UID terpotong di layar ponsel. | **Mobile Cards View**: Data otomatis berubah menjadi kartu vertikal yang rapi dan mudah dioperasikan di layar HP. |
| **Log WhatsApp (`/admin/whatsapp-logs`)** | Hanya menampilkan log tanpa tombol aksi penghapusan. | **Fitur Hapus Log**: Ditambahkan tombol hapus per baris, tombol *"Bersihkan Log Terkirim"*, dan opsi *"Hapus Semua"*. |
| **Dashboard Member (`/member/dashboard`)** | Hanya kartu sederhana dan riwayat absensi polos. | **Ekosistem Member Lengkap**: Kartu Digital RFID Pass, Bukti Keanggotaan (E-Receipt Cetak PDF), 1-Klik WA, Target Kebugaran, Live Crowd Meter, Jadwal Kelas & Tips Latihan Harian. |

---

## 📂 Matriks File yang Diubah & Ditukar

Berikut adalah daftar berkas yang dimodifikasi beserta bagian yang diubah/ditukar:

### 1. File Routing & Konfigurasi
- **[`routes/web.php`](file:///c:/Users/user/gym-membership-app/routes/web.php)**:
  - *Ditukar*: `Route::get('/')` diubah dari redirect login menjadi rendering view landing page `welcome.blade.php` dengan data paket aktif.
  - *Ditambahkan*: Rute penghapusan log WhatsApp (`admin.whatsapp.destroy`, `admin.whatsapp.clear-sent`, `admin.whatsapp.clear-all`).
- **[`vite.config.js`](file:///c:/Users/user/gym-membership-app/vite.config.js)**:
  - *Ditambahkan*: Mendaftarkan `landing.css`, `login.css`, `admin.css`, dan `dashboard.css` ke dalam input compiler Vite.

### 2. File Controller
- **[`app/Http/Controllers/Admin/WhatsappLogController.php`](file:///c:/Users/user/gym-membership-app/app/Http/Controllers/Admin/WhatsappLogController.php)**:
  - *Ditambahkan*: Method `destroy()` untuk hapus satu log, `clearSent()` untuk hapus log terkirim, dan `clearAll()` untuk reset log.

### 3. File Tampilan (Views)
- **[`resources/views/welcome.blade.php`](file:///c:/Users/user/gym-membership-app/resources/views/welcome.blade.php)** *(Baru/Ditukar Total)*:
  - Landing page GymPulse light theme lengkap dengan hero section, kalkulator BMI, simulasi tap RFID, paket harga, jadwal operasional, dan navbar mobile responsif.
- **[`resources/views/auth/login.blade.php`](file:///c:/Users/user/gym-membership-app/resources/views/auth/login.blade.php)** *(Diperbarui)*:
  - UI login light theme, tombol navigasi *"Kembali ke Beranda"*, toggle intip password, dan indikator enkripsi.
- **[`resources/views/layouts/admin.blade.php`](file:///c:/Users/user/gym-membership-app/resources/views/layouts/admin.blade.php)** *(Diperbarui)*:
  - Master layout admin tema terang dengan sidebar deep slate, header tanggal live, dan navigasi aktif warna lime.
- **[`resources/views/admin/dashboard.blade.php`](file:///c:/Users/user/gym-membership-app/resources/views/admin/dashboard.blade.php)** *(Diperbarui)*:
  - Live RFID monitor polling 1 detik dengan card putih, kartu statistik, dan Chart.js tema terang.
- **[`resources/views/admin/rfid/index.blade.php`](file:///c:/Users/user/gym-membership-app/resources/views/admin/rfid/index.blade.php)** *(Diperbarui)*:
  - Ditambahkan tampilan kartu mobile responsif (`lg:hidden`) sehingga aksi blokir/hapus tidak terpotong di HP.
- **[`resources/views/admin/attendance/index.blade.php`](file:///c:/Users/user/gym-membership-app/resources/views/admin/attendance/index.blade.php)** *(Diperbarui)*:
  - Ditambahkan tampilan kartu absensi mobile responsif (`lg:hidden`).
- **[`resources/views/admin/whatsapp/index.blade.php`](file:///c:/Users/user/gym-membership-app/resources/views/admin/whatsapp/index.blade.php)** *(Diperbarui)*:
  - Ditambahkan tombol *"Bersihkan Log Terkirim"*, *"Hapus Semua"*, tombol hapus per baris, dan kartu mobile.
- **[`resources/views/admin/reports/index.blade.php`](file:///c:/Users/user/gym-membership-app/resources/views/admin/reports/index.blade.php)** *(Diperbarui)*:
  - Diperbaiki kontras seluruh angka statistik, form filter, dan grafik 6 bulan.
- **[`resources/views/admin/members/index.blade.php`](file:///c:/Users/user/gym-membership-app/resources/views/admin/members/index.blade.php)**, **[`create.blade.php`](file:///c:/Users/user/gym-membership-app/resources/views/admin/members/create.blade.php)**, **[`edit.blade.php`](file:///c:/Users/user/gym-membership-app/resources/views/admin/members/edit.blade.php)**, **[`packages/index.blade.php`](file:///c:/Users/user/gym-membership-app/resources/views/admin/packages/index.blade.php)** *(Diperbarui)*:
  - Form input, select option, dan tabel diperbaiki agar memiliki kontras tinggi dan latar putih bersih.
- **[`resources/views/layouts/member.blade.php`](file:///c:/Users/user/gym-membership-app/resources/views/layouts/member.blade.php)** & **[`member/dashboard.blade.php`](file:///c:/Users/user/gym-membership-app/resources/views/member/dashboard.blade.php)** *(Diperbarui)*:
  - Master layout member light theme dan dashboard 2-kolom terpadu (kartu pass RFID, modal E-Receipt, WhatsApp, target bulanan, crowd meter, jadwal kelas, workout split harian).

### 4. File Gaya (CSS)
- **[`resources/css/landing.css`](file:///c:/Users/user/gym-membership-app/resources/css/landing.css)**: Styling khusus landing page light theme.
- **[`resources/css/login.css`](file:///c:/Users/user/gym-membership-app/resources/css/login.css)**: Styling khusus halaman login light theme.
- **[`resources/css/admin.css`](file:///c:/Users/user/gym-membership-app/resources/css/admin.css)**: Rule global panel admin untuk memastikan keterbacaan teks 100% tinggi.

---

## 📖 Tata Cara & Alur Penggunaan Sistem (SOP)

### 1. Alur Pendaftaran Member Baru & Taut Kartu RFID
1. Masuk sebagai Admin (`/login`).
2. Buka menu **Member** -> klik **+ Member Baru**.
3. Isi data diri calon member (Nama, WhatsApp, Email, Paket).
4. Tempelkan kartu fisik RFID member pada alat reader USB/gate. Kolom *"UID Kartu"* akan terisi otomatis via AJAX polling.
5. Klik **Simpan & Kirim Notifikasi WA**.
6. Sistem secara otomatis:
   - Menyimpan akun user dan data member.
   - Menautkan UID RFID ke member tersebut.
   - Mengirim pesan WhatsApp selamat datang berisi **email dan password sementara** ke nomor HP member.

### 2. Alur Check-in Otomatis Gate Masuk RFID
1. Member datang ke gym dan menempelkan kartu RFID pada scanner gate.
2. Alat scanner (ESP32) mengirim HTTP POST ke `/api/rfid/scan` dengan UID kartu.
3. Server memvalidasi:
   - Apakah kartu terdaftar dan aktif?
   - Apakah masa berlaku paket member masih aktif?
4. Jika valid:
   - Pintu gate terbuka.
   - Data absensi tersimpan otomatis.
   - Sistem mengirim pesan notifikasi WhatsApp konfirmasi check-in ke member.
   - Layar Dashboard Admin (`/admin/dashboard`) yang sedang aktif otomatis menampilkan foto, nama, UID, dan waktu check-in secara live (real-time tanpa reload).

### 3. Alur Member Melihat Dashboard & Cetak Bukti Keanggotaan
1. Member login menggunakan email & password di `/login`.
2. Pada Dashboard Member (`/member/dashboard`):
   - Member dapat memantau sisa hari aktif membership.
   - Melihat progress target latihan bulanan (berapa kali sudah latihan bulan ini).
   - Melihat status keramaian gym (*Live Crowd Meter*).
   - Melihat jadwal kelas kebugaran hari ini.
3. Untuk mencetak struk:
   - Klik tombol **"Bukti Keanggotaan"** -> Modal E-Receipt akan muncul -> Klik **"🖨️ Cetak / Simpan PDF"**.
4. Untuk perpanjang paket:
   - Klik tombol **"Perpanjang via WA"** -> otomatis membuka WhatsApp kasir dengan pesan perpanjangan siap kirim.

### 4. Alur Pembersihan / Hapus Log WhatsApp (Hemat Penyimpanan)
1. Buka menu **Log WhatsApp** di dashboard admin (`/admin/whatsapp-logs`).
2. Untuk menghapus 1 pesan tertentu: klik tombol **Hapus** pada baris log tersebut.
3. Untuk menghapus semua pesan yang sudah sukses terkirim: klik tombol **"Bersihkan Log Terkirim"** di bagian kanan atas.
4. Database akan langsung bersih dari log riwayat lama, menjaga performa query tetap cepat.

---

## 🚀 Panduan Instalasi & Menjalankan Aplikasi

```bash
# 1. Masuk ke direktori proyek
cd gym-membership-app

# 2. Install dependensi composer
composer install

# 3. Setup file .env dan generate key
cp .env.example .env
php artisan key:generate

# 4. Sesuaikan konfigurasi database MySQL di .env
DB_DATABASE=gym_membership
DB_USERNAME=root
DB_PASSWORD=

# 5. Jalankan migrasi dan seeder data awal
php artisan migrate:fresh --seed

# 6. Install dependensi Node.js & compile aset Vite
npm install
npm run build

# 7. Jalankan server Laravel
php artisan serve
```

Akses aplikasi di browser: **`http://localhost:8000`**

---

## 🔑 Kredensial Akun Demo

| Role | Email | Password | Hak Akses |
| :--- | :--- | :--- | :--- |
| **Administrator** | `admin@gym.test` | `password` | Kelola seluruh data member, paket, RFID, absensi, WhatsApp & laporan |
| **Member Gym** | `budi@gym.test` | `password` | Akses kartu digital, jadwal kelas, e-receipt, target kebugaran |

---

## 📡 Dokumentasi Integrasi Hardware RFID & WhatsApp

### 1. Endpoint Scan RFID Reader (ESP32 / ESP8266 + RC522)
Setiap kali kartu ditempelkan ke reader, firmware memanggil endpoint:

```http
POST /api/rfid/scan
Header: X-Device-Key: <RFID_DEVICE_KEY dari .env>
Content-Type: application/json

{
  "uid": "A1B2C3D4"
}
```

**Respon JSON Sukses:**
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

### 2. Konfigurasi WhatsApp Notifikasi (Fonnte Gateway)
Tambahkan token provider pada file `.env`:
```env
WHATSAPP_API_URL=https://api.fonnte.com/send
WHATSAPP_API_TOKEN=isi_token_fonnte_anda
```

---

## 🗺️ Struktur Rute Aplikasi

| Endpoint | Name Rute | Middleware | Keterangan |
| :--- | :--- | :--- | :--- |
| `GET /` | `home` | `web` | Landing page tentang gym & paket |
| `GET /login` | `login` | `guest` | Form login portal |
| `POST /logout` | `logout` | `auth` | Proses logout akun |
| `GET /admin/dashboard` | `admin.dashboard` | `auth, role:admin` | Live RFID Monitor & Statistik |
| `GET /admin/members` | `admin.members.index` | `auth, role:admin` | Daftar kelola member |
| `GET /admin/packages` | `admin.packages.index` | `auth, role:admin` | Daftar paket membership |
| `GET /admin/rfid` | `admin.rfid.index` | `auth, role:admin` | Kelola kartu RFID & blokir |
| `GET /admin/attendance` | `admin.attendance.index` | `auth, role:admin` | Riwayat absensi check-in |
| `GET /admin/whatsapp-logs` | `admin.whatsapp.index` | `auth, role:admin` | Riwayat log pesan WhatsApp |
| `DELETE /admin/whatsapp-logs/clear-sent` | `admin.whatsapp.clear-sent` | `auth, role:admin` | Hapus semua log terkirim |
| `GET /admin/reports` | `admin.reports.index` | `auth, role:admin` | Cetak PDF laporan bulanan |
| `GET /member/dashboard` | `member.dashboard` | `auth, role:member` | Portal dashboard member |
| `POST /api/rfid/scan` | `api.rfid.scan` | `api` | Endpoint hardware reader gate |

---

© 2026 **GymPulse** — Smart RFID Fitness Management System.
