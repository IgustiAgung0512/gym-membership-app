# Cara Membuat Database Melalui `php artisan migrate`

Panduan ini untuk kamu yang ingin membangun struktur database **dari nol lewat migration Laravel**, bukan lewat import file `gym_membership.sql`. Kalau kamu sudah pernah import file `.sql` sebelumnya, **jangan** ikuti panduan ini di database yang sama — lihat bagian [Kalau Sudah Pernah Import SQL](#kalau-sudah-pernah-import-sql-manual) di bawah.

---

## Kapan Pakai Cara ini?

Pakai `migrate` (bukan import `.sql`) kalau kamu:
- Baru pertama kali setup project ini, database masih kosong
- Ingin struktur tabel dikelola lewat kode (gampang di-tracking, gampang diubah lewat file migration baru)
- Berencana mengembangkan fitur baru ke depannya (tambah kolom, tabel baru, dll) — migration jauh lebih rapi untuk ini dibanding edit SQL manual

---

## Langkah-Langkah

### 1. Pastikan MySQL sudah jalan dan buat database kosong

Pilih salah satu cara sesuai tools kamu:

**Laragon** — menu Database → klik kanan → Create Database → beri nama `gym_membership`.

**XAMPP / phpMyAdmin** — buka `http://localhost/phpmyadmin` → tab Databases → ketik `gym_membership` → Create.

**MySQL CLI**:
```bash
mysql -u root -p -e "CREATE DATABASE gym_membership CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

> Penting: database yang dibuat di langkah ini harus **kosong** (belum ada tabel apa pun di dalamnya). Kalau sebelumnya kamu pernah import `.sql` ke database dengan nama sama, migration akan gagal karena tabelnya sudah ada duluan.

### 2. Atur koneksi database di `.env`

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=gym_membership
DB_USERNAME=root
DB_PASSWORD=
```

Sesuaikan `DB_USERNAME` dan `DB_PASSWORD` dengan akun MySQL lokal kamu.

### 3. Pastikan extension PHP untuk MySQL aktif

Buka `php.ini`, pastikan baris berikut **tidak** diawali tanda `;`:
```ini
extension=pdo_mysql
extension=mysqli
```
Restart web server/PHP setelah mengubahnya.

### 4. Jalankan migration

Untuk membuat semua tabel **tanpa** data contoh:
```bash
php artisan migrate
```

Untuk membuat semua tabel **sekaligus** data contoh (admin, member demo, paket, dll):
```bash
php artisan migrate --seed
```

### 5. Cek hasilnya

Kalau berhasil, terminal akan menampilkan daftar migration yang dijalankan, mirip ini:
```
INFO  Running migrations.

0001_01_01_000000_create_users_table ................. DONE
2024_01_01_000001_add_role_and_phone_to_users_table ... DONE
2024_01_01_000002_create_membership_packages_table .... DONE
2024_01_01_000003_create_members_table ................ DONE
2024_01_01_000004_create_rfid_cards_table ............. DONE
2024_01_01_000005_create_attendances_table ............ DONE
2024_01_01_000006_create_whatsapp_logs_table .......... DONE
2024_01_01_000007_create_payments_table ............... DONE
```

Kalau memakai `--seed`, di baris bawahnya juga akan muncul info login demo:
```
Admin login  : admin@gym.test / password
Member login : budi@gym.test / password
```

### 6. Jalankan server

```bash
php artisan serve
```

Buka `http://localhost:8000` dan login dengan akun demo di atas (kalau pakai `--seed`), atau buat akun admin sendiri lewat:
```bash
php artisan make:admin
```
(lihat `README-ADMIN.md` untuk detailnya)

---

## Tabel yang Dibuat

Migration di project ini akan membuat tabel-tabel berikut secara berurutan:

| Migration | Tabel dibuat/diubah |
|---|---|
| `add_role_and_phone_to_users_table` | Menambah kolom `role`, `phone`, `must_change_password` ke tabel `users` bawaan Laravel |
| `create_membership_packages_table` | `membership_packages` |
| `create_members_table` | `members` |
| `create_rfid_cards_table` | `rfid_cards` |
| `create_attendances_table` | `attendances` |
| `create_whatsapp_logs_table` | `whatsapp_logs` |
| `create_payments_table` | `payments` |

Laravel juga otomatis menyertakan migration bawaan untuk `users`, `password_reset_tokens`, `sessions`, `cache`, dan `jobs` — semua ini adalah bagian dari skeleton Laravel default, sudah ada begitu kamu `composer create-project laravel/laravel`.

---

## Perintah Migration Lain yang Berguna

| Perintah | Fungsi |
|---|---|
| `php artisan migrate` | Menjalankan migration baru yang belum pernah dijalankan |
| `php artisan migrate --seed` | Sama seperti di atas, plus mengisi data contoh dari `DatabaseSeeder.php` |
| `php artisan migrate:status` | Melihat migration mana saja yang sudah/belum dijalankan |
| `php artisan migrate:rollback` | Membatalkan migration terakhir (mundur 1 langkah) |
| `php artisan migrate:fresh --seed` | ⚠️ **Menghapus semua tabel**, lalu membuat ulang dari awal + isi data contoh. Gunakan hanya kalau memang ingin mulai bersih total dan tidak keberatan kehilangan semua data yang ada. |
| `php artisan db:seed` | Menjalankan ulang seeder saja (tanpa migrate), berguna kalau tabel sudah ada tapi datanya mau di-generate ulang |

---

## Kalau Sudah Pernah Import SQL Manual

Kalau database kamu **sudah berisi tabel** dari hasil import `gym_membership.sql` sebelumnya, lalu kamu coba jalankan `php artisan migrate`, kamu akan mendapat error seperti ini:

```
SQLSTATE[42S01]: Base table or view already exists: 1050 Table 'users' already exists
```

**Penyebabnya:** Laravel mencatat migration mana saja yang sudah dijalankan lewat tabel khusus bernama `migrations`. Kalau kamu buat tabel manual lewat SQL (bukan lewat `artisan migrate`), tabel `migrations` itu tidak tahu bahwa `users`, `members`, dll sudah ada — jadi Laravel tetap mencoba membuatnya dari awal dan gagal karena sudah ada.

**Solusinya, pilih salah satu:**

1. **Tetap pakai data yang sudah ada dari import SQL** — jangan jalankan `migrate` lagi, langsung `php artisan serve` saja. Ini cara paling aman kalau sudah ada data penting.

2. **Mulai bersih dengan migration** (⚠️ akan menghapus semua data yang ada sekarang):
   ```bash
   php artisan migrate:fresh --seed
   ```

Jangan mencampur kedua cara (`import .sql` dan `artisan migrate`) di database yang sama secara bersamaan — pilih salah satu sebagai sumber kebenaran struktur database kamu.

---

## Troubleshooting

| Error | Penyebab & Solusi |
|---|---|
| `SQLSTATE[HY000] [1045] Access denied` | Username/password di `.env` salah — cocokkan dengan akun MySQL kamu |
| `SQLSTATE[HY000] [1049] Unknown database` | Database belum dibuat — ulangi Langkah 1 |
| `could not find driver` | Extension `pdo_mysql` belum aktif di `php.ini` — ulangi Langkah 3 |
| `Connection refused` | Service MySQL belum berjalan — start dulu dari Laragon/XAMPP atau `sudo service mysql start` |
| `Base table or view already exists` | Tabel sudah ada duluan (biasanya dari import SQL manual) — lihat bagian [Kalau Sudah Pernah Import SQL](#kalau-sudah-pernah-import-sql-manual) |
| `Nothing to migrate` | Semua migration sudah pernah dijalankan sebelumnya — cek `php artisan migrate:status` untuk konfirmasi |
