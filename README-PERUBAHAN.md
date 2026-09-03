# README — Fitur Foto Profil Member (GymPulse)

Dokumen ini merangkum **semua perubahan** yang ditambahkan ke project `gym-membership-app`
dibanding zip aslinya, dibagi per tahap sesuai urutan permintaan. Setiap tahap berisi:
tujuan, file yang diubah/dibuat, dan apa isi perubahannya.

---

## Ringkasan Fitur

Sebelumnya kolom `photo` di tabel `members` sudah ada di database tapi **tidak pernah dipakai**
sama sekali oleh aplikasi. Perubahan ini menambahkan:

1. Form upload/ganti foto di **Dashboard Member**.
2. Perbaikan agar foto **benar-benar tampil** di browser (tanpa bergantung symlink Laravel).
3. Tampilan foto member di **Dashboard & Daftar Member Admin**.

---

## Tahap 1 — Menambahkan Input/Ganti Foto di Dashboard Member

**Tujuan:** member bisa upload atau mengganti foto profilnya sendiri lewat halaman
`/member/dashboard`.

### File baru
- `app/Http/Controllers/Member/PhotoController.php`
  - Method `update()`: validasi file (`jpg,jpeg,png,webp`, maks 2MB), simpan ke
    `storage/app/public/members/`, hapus foto lama otomatis, lalu update kolom
    `photo` pada baris `members` milik user yang login.

### File yang diubah
- `routes/web.php`
  - Tambah `use App\Http\Controllers\Member\PhotoController as MemberPhotoController;`
  - Tambah route `POST /member/photo` → `member.photo.update` (di dalam grup middleware
    `auth`, `role:member`, jadi otomatis hanya bisa diakses member yang login).
- `resources/views/member/dashboard.blade.php`
  - Tambah avatar bulat di kartu member (foto asli / inisial nama jika belum ada foto).
  - Klik avatar → buka file picker → preview instan pakai Alpine.js → tombol
    **Simpan Foto** / **Batal** muncul → submit ke server.
  - Tambah alert sukses/gagal (`session('success')`, `$errors`) di atas dashboard.
- `resources/views/layouts/member.blade.php`
  - Tambah CSS `[x-cloak]{display:none !important}` supaya tombol Simpan/Batal tidak
    "kedip" sebelum Alpine.js selesai inisialisasi.

---

## Tahap 2 — Perbaikan: Foto Tidak Tampil di Dashboard Member

**Masalah:** setelah upload, gambar tampil sebagai ikon broken image.

**Penyebab:** foto disimpan lewat `asset('storage/' . $member->photo)`, yang mengandalkan
symlink `public/storage → storage/app/public`. Symlink itu **belum dibuat**
(`php artisan storage:link` belum pernah dijalankan), dan di banyak setup Windows/Laragon/XAMPP
symlink ini sering gagal dibuat tanpa privilege admin.

**Solusi permanen yang dipilih:** foto di-*serve* langsung lewat route Laravel
(streaming dari disk), **tidak lagi bergantung pada symlink sama sekali**.

### File yang diubah
- `app/Http/Controllers/Member/PhotoController.php`
  - Tambah method `show()`: mengambil foto member yang sedang login langsung dari
    `Storage::disk('public')` dan mengirimkannya sebagai response gambar. Sekaligus
    lebih aman karena hanya bisa diakses oleh member yang bersangkutan (butuh login).
- `routes/web.php`
  - Tambah route `GET /member/photo` → `member.photo.show`.
- `resources/views/member/dashboard.blade.php`
  - URL foto diganti dari `asset('storage/'.$member->photo)` menjadi
    `route('member.photo.show') . '?v=' . $member->updated_at->timestamp`
    (parameter `?v=` untuk cache-busting supaya browser langsung menampilkan foto
    terbaru setiap kali diganti, tanpa perlu hard refresh).

> Catatan: kolom `photo` di database **tidak perlu diubah/ditambah apa pun** — struktur
> tabel `members` sudah cukup (`photo varchar(255) nullable`). Masalahnya murni di cara
> file di-serve ke browser, bukan di database.

---

## Tahap 3 — Perbaikan: Foto Tidak Tampil di Dashboard Admin

**Masalah:** di halaman **Member** (admin), semua member tampil dengan avatar inisial
huruf saja, walau member tersebut sudah punya foto.

**Penyebab:** halaman ini **memang belum pernah punya logic** untuk menampilkan foto —
dari awal source code cuma render `substr($nama, 0, 1)`. Ditambah 2 tempat lain di sisi
admin yang masih pakai `asset('storage/...')` (rawan broken image dengan alasan yang sama
seperti Tahap 2).

### File yang diubah
- `app/Http/Controllers/Admin/MemberController.php`
  - Tambah `use Illuminate\Support\Facades\Storage;`
  - Tambah method `photo(Member $member)`: serve foto member manapun (khusus admin)
    langsung dari storage, sama seperti pola di Tahap 2.
- `routes/web.php`
  - Tambah route `GET /admin/members/{member}/photo` → `admin.members.photo`
    (di dalam grup middleware `auth`, `role:admin`).
- `resources/views/admin/members/index.blade.php`
  - Versi **mobile card** dan **desktop table**: tampilkan `<img>` dari
    `route('admin.members.photo', $m)` jika `$m->photo` ada, fallback ke avatar
    inisial huruf jika member belum pernah upload foto.
- `app/Http/Controllers/Admin/AttendanceController.php`
  - Baris pengambilan foto untuk response check-in RFID diganti dari
    `asset('storage/' . $attendance->member->photo)` menjadi
    `route('admin.members.photo', $attendance->member)`.
- `app/Http/Controllers/Admin/DashboardController.php`
  - Perubahan yang sama seperti di atas, dipakai untuk menampilkan foto saat ada
    check-in RFID terbaru di Dashboard admin.

---

## Daftar Lengkap File yang Diubah/Ditambah

| File | Status | Keterangan |
|---|---|---|
| `app/Http/Controllers/Member/PhotoController.php` | **Baru** | Upload foto member (`update`) + serve foto member (`show`) |
| `app/Http/Controllers/Admin/MemberController.php` | Diubah | Tambah method `photo()` + import `Storage` |
| `app/Http/Controllers/Admin/AttendanceController.php` | Diubah | URL foto pakai route, bukan `asset('storage/...')` |
| `app/Http/Controllers/Admin/DashboardController.php` | Diubah | URL foto pakai route, bukan `asset('storage/...')` |
| `routes/web.php` | Diubah | Tambah 3 route: `member.photo.update`, `member.photo.show`, `admin.members.photo` |
| `resources/views/member/dashboard.blade.php` | Diubah | UI upload/ganti foto + alert flash |
| `resources/views/layouts/member.blade.php` | Diubah | Tambah CSS `[x-cloak]` |
| `resources/views/admin/members/index.blade.php` | Diubah | Tampilkan foto member (mobile & desktop) |

**Database:** tidak ada perubahan struktur tabel. Kolom `photo` di tabel `members`
sudah tersedia sejak awal dan langsung dipakai apa adanya.

---

## Cara Menerapkan ke Project Kamu

1. Extract zip ini, timpa (overwrite) file-file di atas ke project lokal kamu —
   atau langsung pakai seluruh isi zip sebagai pengganti folder project.
2. Jalankan `composer install` dan `npm install` (folder `vendor/` dan `node_modules/`
   tidak disertakan di zip supaya ukurannya kecil).
3. Pastikan `.env` sudah mengarah ke database yang benar, lalu jalankan migrasi seperti biasa.
4. **Tidak wajib**, tapi disarankan tetap jalankan `php artisan storage:link` sekali
   sebagai jaring pengaman tambahan (fitur foto di atas sudah tidak bergantung pada ini,
   tapi baik untuk kebutuhan lain di masa depan).
5. Jalankan `php artisan serve`, lalu coba:
   - Login sebagai member → buka Dashboard → klik avatar → upload foto → **Simpan Foto**.
   - Login sebagai admin → buka menu **Member** → foto member yang baru upload harus
     langsung terlihat di daftar.

---

## Potensi Pengembangan Selanjutnya

- Tambah tombol "Hapus Foto" di dashboard member (kembali ke avatar inisial).
- Tambah crop/resize gambar di sisi client sebelum upload (mengurangi ukuran file).
- Tampilkan foto juga di halaman **Edit Member** (admin) dan **Detail Laporan**.
