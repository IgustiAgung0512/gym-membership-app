# Cara Membuat / Memulihkan Akun Admin & Kasir — Tanpa Hapus Database

Panduan ini untuk situasi seperti: lupa password admin/kasir, akun terhapus, database baru di-reset, atau butuh staf kasir baru — **tanpa perlu** merusak relasi tabel, atau kehilangan data member, RFID, dan absensi yang sudah ada.

---

## 1. Akun Admin (`make:admin`)

Project ini sudah dilengkapi perintah artisan khusus: `make:admin`.

### Jalankan perintah di terminal:

**Mode interaktif** (akan ditanya satu-satu):
```bash
php artisan make:admin
```
Contoh isian:
```
Nama admin: Admin GYM
Email admin: admin@gym.test
Password admin (min. 6 karakter): ********
```

**Atau mode satu baris** (langsung isi semua sekaligus):
```bash
php artisan make:admin --name="Admin GYM" --email=admin@gym.test --password=passwordbaru123
```

---

## 2. Akun Kasir Front Desk (`make:cashier`)

Untuk membuat atau memulihkan akun staf kasir (Front Desk & Terminal POS):

### Jalankan perintah di terminal:

**Mode interaktif**:
```bash
php artisan make:cashier
```
Contoh isian:
```
Nama kasir: Staff Kasir Front Desk
Email kasir: kasir@gym.test
No. WhatsApp / HP kasir (opsional): 081234567899
Password kasir (min. 6 karakter): ********
```

**Atau mode satu baris**:
```bash
php artisan make:cashier --name="Staff Kasir" --email=kasir@gym.test --phone=081234567899 --password=password123
```

Setelah selesai, akun kasir langsung siap digunakan untuk login di `/login` dan otomatis diarahkan ke antarmuka **Kasir POS & Pendaftaran Member**.

---

## Kenapa Ini Aman Dijalankan Kapan Saja

Perintah `make:admin` dan `make:cashier` dirancang **idempotent** — artinya boleh dijalankan berkali-kali tanpa risiko:

| Situasi | Yang terjadi |
|---|---|
| Email **belum terdaftar** di database | Dibuatkan akun baru dengan role yang sesuai (`admin` / `cashier`) |
| Email **sudah ada** (misalnya lupa password) | Akun tersebut **diperbarui role-nya** dan passwordnya **direset** ke password baru |
| Data member, kartu RFID, riwayat absensi, paket membership | **Tidak disentuh sama sekali** — hanya baris user dengan email tersebut yang diubah |

Jadi tidak ada alasan lagi untuk `migrate:fresh` atau hapus database cuma gara-gara lupa akun admin.

---

## Contoh Skenario

### Skenario 1: Database baru dibuat, buat akun Admin & Kasir pertama
```bash
php artisan migrate
php artisan make:admin --name="Admin GYM" --email=admin@gym.test --password=admin123
php artisan make:cashier --name="Staff Kasir" --email=kasir@gym.test --phone=081234567899 --password=password123
```

### Skenario 2: Lupa password admin atau kasir yang sudah ada
```bash
php artisan make:admin --email=admin@gym.test --password=passwordbaruku
# atau untuk kasir:
php artisan make:cashier --email=kasir@gym.test --password=passwordbarukasir
```
*(Nama boleh diisi ulang atau dikosongkan jika via mode interaktif)*

### Skenario 3: Menambah staf kasir shift baru
```bash
php artisan make:cashier --name="Kasir Shift Pagi" --email=kasirpagi@gym.test --password=pagi123
php artisan make:cashier --name="Kasir Shift Malam" --email=kasirmalam@gym.test --password=malam123
```
Sekarang setiap staf kasir memiliki akun login masing-masing.

---

## Cara Alternatif: Manual via Laravel Tinker

Bisa juga membuat atau mereset password admin & kasir lewat Tinker:

```bash
php artisan tinker
```

Lalu jalankan:

```php
// Buat / Reset Akun Admin
\App\Models\User::updateOrCreate(
    ['email' => 'admin@gym.test'],
    [
        'name' => 'Admin GYM',
        'password' => \Illuminate\Support\Facades\Hash::make('passwordbaru123'),
        'role' => 'admin',
        'must_change_password' => false,
    ]
);

// Buat / Reset Akun Kasir
\App\Models\User::updateOrCreate(
    ['email' => 'kasir@gym.test'],
    [
        'name' => 'Staff Kasir Front Desk',
        'phone' => '081234567899',
        'password' => \Illuminate\Support\Facades\Hash::make('password123'),
        'role' => 'cashier',
        'must_change_password' => false,
    ]
);
```

Ketik `exit` untuk keluar.

---

## Cara Alternatif: Database Seeder (Saat Database Baru / Reset Total)

Jika database baru di-reset dari awal dan Anda ingin memasukkan data demo lengkap (Admin, Kasir, Paket Membership, dan Produk Toko):

```bash
php artisan migrate:fresh --seed
```

Akun bawaan hasil seeder:
- **Admin**: `admin@gym.test` / password: `password`
- **Kasir**: `kasir@gym.test` / password: `password`

---

## Yang PERLU DIHINDARI

Jangan lakukan hal berikut hanya karena lupa/butuh akun admin atau kasir:

- ❌ `php artisan migrate:fresh` pada database yang sedang aktif digunakan — akan menghapus **semua tabel** dan **semua data member, kartu RFID, transaksi kasir, serta riwayat absensi**.
- ❌ Hapus manual tabel `users` lewat phpMyAdmin — bisa merusak relasi ke tabel `members` dan `orders` (foreign key constraint).
- ❌ Drop seluruh database lalu import ulang file `.sql` awal — Anda akan kehilangan seluruh transaksi yang sudah berjalan.

Cukup gunakan perintah `php artisan make:admin` atau `php artisan make:cashier` — aman, cepat, dan tidak mengganggu data lainnya.
