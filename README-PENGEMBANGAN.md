# 🚀 Roadmap & Rencana Pengembangan GymPulse

Dokumen ini memuat panduan, ide fitur, arsitektur teknis, dan prioritas pengembangan masa depan untuk sistem manajemen gym **GymPulse (Smart RFID Fitness)**.

---

## 📌 Daftar Isi
1. [Ringkasan Status Saat Ini](#-ringkasan-status-saat-ini)
2. [Prioritas 1: Low Effort, High Impact](#-prioritas-1-low-effort-high-impact)
3. [Prioritas 2: Peningkatan Pengalaman Member](#-prioritas-2-peningkatan-pengalaman-member)
4. [Prioritas 3: Otomatisasi & Integrasi Bisnis](#-prioritas-3-otomatisasi--integrasi-bisnis)
5. [Prioritas 4: Skalabilitas & Fitur Lanjutan](#-prioritas-4-skalabilitas--fitur-lanjutan)
6. [Matriks Prioritas & Estimasi](#-matriks-prioritas--estimasi)

---

## 📊 Ringkasan Status Saat Ini

Sistem saat ini sudah memiliki fondasi yang kuat:
- ✅ **Multi-Role Authentication**: Admin, Kasir (Cashier), dan Member.
- ✅ **Smart RFID Check-in / Check-out**: Integrasi hardware IoT via API endpoint.
- ✅ **POS & Gym Store**: Manajemen produk, stok, kasir POS, dan riwayat transaksi.
- ✅ **Cloud Storage**: Terintegrasi dengan Supabase Storage (S3 Compatible) untuk foto member dan produk.
- ✅ **Database**: Kompatibel dengan TiDB / MySQL.
- ✅ **WhatsApp Gateway**: Integrasi Fonnte untuk notifikasi pendaftaran & reset password.
- ✅ **User Management**: Pengelolaan akun admin & kasir dari dashboard admin.

---

## ⚡ Prioritas 1: Low Effort, High Impact

### 1. Otomatisasi Reminder WhatsApp (Cron Job)
* **Tujuan**: Mengurangi *churn rate* dan meningkatkan retensi perpanjangan membership secara otomatis.
* **Fitur**:
  * Pengingat otomatis H-7, H-3, dan H-1 sebelum membership berakhir.
  * Ucapan selamat ulang tahun otomatis kepada member aktif.
  * Notifikasi stok barang menipis langsung ke WhatsApp Admin/Kasir.
* **Implementasi Teknis**:
  * Gunakan `routes/console.php` atau command `php artisan app:send-membership-reminders`.
  * Daftarkan di scheduler Laravel: `Schedule::command('...')->dailyAt('08:00');`.

### 2. QR Code Member Digital (Fallback RFID)
* **Tujuan**: Member tetap bisa check-in meskipun kartu RFID fisiknya tertinggal.
* **Fitur**:
  * Generate QR Code unik per member di dashboard member (menggunakan library `simplesoftwareio/simple-qrcode`).
  * Scanner QR di meja resepsionis / kasir untuk check-in instan.

---

## 🏋️ Prioritas 2: Peningkatan Pengalaman Member

### 1. Progressive Web App (PWA)
* **Tujuan**: Memungkinkan member menginstal aplikasi GymPulse langsung ke layar utama HP Android/iOS tanpa Google Play / App Store.
* **Fitur**:
  * Web App Manifest (`manifest.json`) + Service Worker untuk caching offline.
  * Tampilan *app-like* (full screen tanpa browser URL bar).
  * Push Notification browser.

### 2. Fitness & Body Progress Tracker
* **Tujuan**: Memberikan *value* lebih bagi member agar termotivasi berolahraga rutin.
* **Fitur**:
  * Form input berat badan, tinggi badan, lingkar perut, dan body fat percentage.
  * Grafik perkembangan BMI bulanan (menggunakan Chart.js yang sudah terpasang).
  * **Workout Streak & Leaderboard**: Peringkat member paling rajin latihan bulan ini.

### 3. Booking Kelas Gym & Personal Trainer (PT)
* **Tujuan**: Manajemen kelas terjadwal (Yoga, Zumba, HIIT, Spinning, Body Combat).
* **Fitur**:
  * Jadwal kelas mingguan beserta batas kuota peserta.
  * Tombol *Booking Slot* langsung dari dashboard member.
  * Manajemen profil trainer dan ketersediaan jam sesi latihan.

---

## 💳 Prioritas 3: Otomatisasi & Integrasi Bisnis

### 1. Payment Gateway Asli (Midtrans / Xendit / Tripay)
* **Tujuan**: Pembayaran online otomatis tanpa konfirmasi kasir manual.
* **Fitur**:
  * Pembayaran instan via QRIS (GoPay, OVO, Dana, ShopeePay, BCA, dll.) dan Virtual Account Bank.
  * **Webhook Handler**: Saat pembayaran berhasil, sistem otomatis memperpanjang masa aktif membership member dan mengirim struk via WhatsApp.

### 2. Manajemen Sewa Loker (Locker System)
* **Tujuan**: Mengatur inventaris loker gym dan sewa loker berbayar.
* **Fitur**:
  * Grid visual status loker (Kosong, Terpakai, Perbaikan).
  * Penugasan nomor loker ke member saat tap-in RFID.
  * Laporan masa sewa loker bulanan.

---

## 🏢 Prioritas 4: Skalabilitas & Fitur Lanjutan

### 1. Audit Trail & Activity Log
* **Tujuan**: Keamanan dan akuntabilitas staf.
* **Fitur**:
  * Mencatat log setiap perubahan data penting (perubahan harga paket, stok barang, hapus member, ganti password user).
  * Tampilan log aktivitas di dashboard admin.

### 2. Multi-Cabang (Multi-Gym Branch)
* **Tujuan**: Ekspansi bisnis jika gym memiliki lebih dari 1 lokasi cabang.
* **Fitur**:
  * Pemisahan data stok kasir POS dan alat reader RFID per cabang.
  * Laporan keuangan per cabang maupun konsolidasi pusat.

---

## 📋 Matriks Prioritas & Estimasi

| No | Fitur | Kategori | Tingkat Kesulitan | Dampak Bisnis |
|:---|:---|:---|:---:|:---:|
| 1 | **Cron Job WhatsApp Reminders** | Otomatisasi | 🟢 Rendah | ⭐⭐⭐⭐⭐ Sangat Tinggi |
| 2 | **QR Member Card Digital** | Member Experience | 🟢 Rendah | ⭐⭐⭐⭐ Tinggi |
| 3 | **PWA Mobile App** | Frontend / Mobile | 🟡 Sedang | ⭐⭐⭐⭐ Tinggi |
| 4 | **Payment Gateway (Midtrans/Xendit)** | Transaksi | 🟡 Sedang | ⭐⭐⭐⭐⭐ Sangat Tinggi |
| 5 | **Jadwal Kelas & Booking PT** | Manajemen Gym | 🟡 Sedang | ⭐⭐⭐⭐ Tinggi |
| 6 | **Fitness Progress Tracker** | Member Experience | 🟡 Sedang | ⭐⭐⭐ Sedang |
| 7 | **Manajemen Loker** | Fasilitas | 🟢 Rendah | ⭐⭐⭐ Sedang |
| 8 | **Audit Log Aktivitas Staf** | Keamanan | 🟢 Rendah | ⭐⭐⭐ Sedang |
| 9 | **Dukungan Multi-Cabang** | Skalabilitas | 🔴 Tinggi | ⭐⭐⭐⭐ Tinggi (Jangka Panjang) |

---

> 💡 **Saran Urutan Pengerjaan**:  
> Mulailah dari **No. 1 (Cron WhatsApp Reminder)** dan **No. 2 (QR Member Card)** karena memberikan dampak langsung pada operasional gym dengan waktu pengerjaan yang relatif cepat.
