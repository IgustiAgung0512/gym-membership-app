# Cara Membuat / Memulihkan Akun Admin — Tanpa Hapus Database

Panduan ini untuk situasi seperti: lupa password admin, akun admin ke-hapus, atau butuh admin baru — **tanpa perlu** `migrate:fresh`, hapus tabel manual, atau kehilangan data member, RFID, dan absensi yang sudah ada.

---

## Cara Cepat (Direkomendasikan)

Project ini sudah dilengkapi perintah artisan khusus: `make:admin`.

### 1. Buka terminal di folder project

```bash
cd gym-membership-app
```

### 2. Jalankan perintah berikut

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

### 3. Selesai

Kalau berhasil, akan muncul:
```
Akun admin siap dipakai:
  Email    : admin@gym.test
  Password : (sesuai yang barusan kamu masukkan)
```

Langsung login ke `/login` dengan email & password tersebut.

---

## Kenapa Ini Aman Dijalankan Kapan Saja

Perintah `make:admin` dirancang **idempotent** — artinya boleh dijalankan berkali-kali tanpa risiko:

| Situasi | Yang terjadi |
|---|---|
| Email **belum terdaftar** di database | Dibuatkan akun baru dengan role `admin` |
| Email **sudah ada** (misalnya akun member biasa, atau admin lama yang lupa password) | Akun itu **di-upgrade jadi admin** dan passwordnya **direset** ke yang baru kamu masukkan |
| Data member, kartu RFID, riwayat absensi, paket membership | **Tidak disentuh sama sekali** — hanya baris user dengan email tersebut yang diubah |

Jadi tidak ada alasan lagi untuk `migrate:fresh` atau hapus database cuma gara-gara lupa akun admin.

---

## Contoh Skenario

### Skenario 1: Database baru, belum ada admin sama sekali
```bash
php artisan migrate
php artisan make:admin --name="Admin GYM" --email=admin@gym.test --password=admin123
```

### Skenario 2: Lupa password admin yang sudah ada
```bash
php artisan make:admin --email=admin@gym.test --password=passwordbaruku
```
(Nama boleh diisi ulang atau dikosongkan — kalau mode interaktif dan tidak mau ganti nama, isi saja nama yang sama seperti sebelumnya)

### Skenario 3: Ingin admin kedua sebagai cadangan
```bash
php artisan make:admin --name="Admin Cadangan" --email=admin2@gym.test --password=cadangan123
```
Sekarang ada dua akun admin yang bisa login terpisah.

### Skenario 4: Akun member biasa ingin dijadikan admin
```bash
php artisan make:admin --name="Budi Santoso" --email=budi@gym.test --password=newpassword
```
⚠️ Perhatikan: ini akan mengubah role akun itu jadi `admin` dan **memutus tautannya sebagai member** secara akses (baris di tabel `members` tetap ada, tapi user tersebut sekarang login sebagai admin, bukan lagi ke dashboard member). Gunakan email khusus admin, jangan email member aktif, kecuali memang disengaja.

---

## Cara Alternatif (Manual via Tinker) — kalau perintah `make:admin` belum terpasang

Kalau kamu belum sempat menambahkan file `app/Console/Commands/MakeAdminCommand.php` ke project, bisa juga lewat Tinker:

```bash
php artisan tinker
```

Lalu jalankan:

```php
$admin = \App\Models\User::updateOrCreate(
    ['email' => 'admin@gym.test'],
    [
        'name' => 'Admin GYM',
        'password' => \Illuminate\Support\Facades\Hash::make('passwordbaru123'),
        'role' => 'admin',
        'must_change_password' => false,
    ]
);

echo "Admin siap: {$admin->email}";
```

Ketik `exit` untuk keluar.

---

## Yang PERLU DIHINDARI

Jangan lakukan hal berikut hanya karena lupa/butuh akun admin:

- ❌ `php artisan migrate:fresh` — menghapus **semua tabel** dan **semua data**, termasuk member, kartu RFID, riwayat absensi
- ❌ Hapus manual tabel `users` lewat phpMyAdmin — bisa merusak relasi ke tabel `members` (foreign key)
- ❌ Drop seluruh database lalu import ulang file `.sql` awal — kamu akan kehilangan seluruh data yang sudah bertambah sejak awal setup

Cukup gunakan `php artisan make:admin` — aman, cepat, dan tidak mengganggu data lain.

---

## Instalasi Perintah `make:admin` (kalau belum ada di project kamu)

Kalau project kamu belum punya file ini, salin file berikut ke lokasi yang sesuai:

**File:** `app/Console/Commands/MakeAdminCommand.php`

Laravel otomatis mendeteksi command baru di folder `app/Console/Commands/` tanpa perlu registrasi tambahan. Setelah file disalin, cek apakah perintahnya sudah terbaca:

```bash
php artisan list make
```

Kalau `make:admin` muncul di daftar, berarti sudah siap dipakai.
