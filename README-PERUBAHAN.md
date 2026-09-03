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

## Tahap 4 — Perbaikan: Foto Tidak Tampil Saat Check-in RFID di Dashboard Admin

**Masalah:** widget "Check-in Terbaru" di Dashboard admin selalu menampilkan avatar
inisial huruf, padahal member yang tap kartu RFID sudah punya foto (dan foto itu sudah
tampil normal di halaman **Member**).

**Penyebab:** bug murni di kode — pada `Admin\DashboardController@latestRfidCheckin`,
variabel `$photo` sudah dihitung dengan benar, tapi **tidak pernah dimasukkan** ke array
JSON yang dikembalikan ke browser. Jadi JavaScript di dashboard tidak pernah menerima
data foto, walau backend-nya sudah punya nilainya.

### File yang diubah
- `app/Http/Controllers/Admin/DashboardController.php`
  - Tambah `'photo' => $photo,` ke dalam `response()->json([...])` pada method
    `latestRfidCheckin()`.

> Method serupa di `Admin\AttendanceController` (dipakai untuk halaman Absensi/Check-in
> manual) sudah benar sejak awal — sudah mengirim field `photo` di response-nya.

---

## Tahap 5 — Perbaikan: Widget "Check-in Terbaru" Tidak Auto-Refresh Saat Check-out

**Masalah:** saat member check-in, widget di Dashboard admin langsung ter-update otomatis.
Tapi saat member check-out, widget **tidak berubah** sampai halaman di-refresh manual.

**Penyebab:** widget ini mem-polling endpoint setiap 1 detik lewat JavaScript, lalu
membandingkan `id` attendance yang diterima dengan `id` terakhir yang sudah ditampilkan
(`lastAttendanceId`) untuk tahu apakah perlu render ulang. Saat **check-in**, sebuah baris
attendance **baru** dibuat → `id` berubah → widget ter-update. Saat **check-out**, baris
yang **sama** hanya di-update kolom `check_out_at`-nya → `id` tidak berubah → JS
menyimpulkan "tidak ada yang baru" dan skip render, padahal datanya sudah berubah.

### File yang diubah
- `resources/views/admin/dashboard.blade.php`
  - Key pembanding diubah dari `data.id` saja menjadi gabungan `data.id + ':' + data.checkout_time`.
    Dengan begitu, perubahan `checkout_time` (dari `null` jadi terisi) ikut dianggap
    sebagai perubahan state, sehingga widget otomatis re-render tanpa perlu refresh manual.

---

## Tahap 6 — Perbaikan: Member Tidak Bisa Check-in Lagi Setelah Check-out di Hari yang Sama

**Masalah:** kalau member check-in pagi lalu check-out siang, dia **tidak bisa** check-in
lagi di hari yang sama (misalnya sore) — tap kartu RFID seperti tidak bereaksi.

**Penyebab (root cause sebenarnya):** endpoint yang benar-benar dipanggil oleh **script
Arduino/ESP32** untuk RFID scan adalah `POST /api/members/add` →
`App\Http\Controllers\Api\MemberController@store` — **bukan** kode di
`Admin\AttendanceController` atau `Admin\DashboardController` seperti dugaan awal (dua
controller itu ternyata sebagian besar cuma dipakai untuk *menampilkan* data ke Dashboard
admin, bukan yang men-*generate* data check-in/check-out).

Logika lama di `Api\MemberController@store` mencari attendance **hari ini saja**
(`whereDate('check_in_at', today())`). Begitu member sudah check-in **dan** check-out
di hari yang sama, baris attendance "hari ini" itu tetap ditemukan lagi saat tap
berikutnya, tapi karena `check_out_at`-nya sudah terisi, kode masuk ke percabangan yang
**tidak melakukan apa pun** (silent no-op) — bukan membuat sesi check-in baru.

### File yang diubah
- `app/Http/Controllers/Api/MemberController.php`
  - Logika pencarian attendance diganti dari **berbasis tanggal** (`whereDate('check_in_at', today())`)
    menjadi **berbasis sesi terbuka** (`whereNull('check_out_at')`):
    - Kalau member punya sesi yang **masih terbuka** (belum check-out) → tap berikutnya
      dianggap **check-out**.
    - Kalau **tidak ada** sesi terbuka → tap berikutnya **selalu** dianggap **check-in baru**,
      tidak peduli sudah berapa kali dia check-in/check-out di hari itu.
  - Dengan perubahan ini, member bebas check-in & check-out berkali-kali dalam sehari
    (pagi, siang, sore, dst), selama setiap sesi ditutup (check-out) dulu sebelum
    sesi berikutnya dibuka.

> Catatan: query yang dipakai widget "Check-in Terbaru" di Dashboard admin
> (`Admin\DashboardController::latestRfidCheckin()`) tidak perlu diubah — query itu
> sudah otomatis mengambil sesi **terbaru** hari ini (`latest('id')`), jadi akan tetap
> menampilkan sesi yang paling baru walau member sudah check-in/check-out beberapa kali.

---

## Tahap 7 — Perbaikan: Halaman Absensi / Check-in Admin Error (Fatal Error)

**Masalah:** membuka menu **Absensi / Check-in** di sidebar admin menampilkan Internal
Server Error:

```
Symfony\Component\ErrorHandler\Error\FatalError
Cannot declare class App\Http\Controllers\Admin\DashboardController,
because the name is already in use
```

**Penyebab:** ini bug bawaan dari zip awal (bukan dari perubahan-perubahan sebelumnya).
File `app/Http/Controllers/Admin/AttendanceController.php` isinya salah — di dalamnya
class-nya bernama `DashboardController`, persis sama dengan class yang ada di
`app/Http/Controllers/Admin/DashboardController.php`. Begitu Laravel butuh me-load
`Admin\AttendanceController` (untuk route `/admin/attendance`), file yang di-load memang
benar filenya, tapi isinya mendeklarasikan class `DashboardController` yang **sudah pernah
dideklarasikan** oleh file lain di request yang sama → PHP fatal error "Cannot declare
class ..., because the name is already in use".

### File yang diubah
- `app/Http/Controllers/Admin/AttendanceController.php` — **ditulis ulang total**:
  - Class diubah jadi `AttendanceController` (sesuai nama file, sesuai standar PSR-4
    Laravel).
  - `index(Request $request)` — menampilkan daftar check-in per tanggal (sesuai kebutuhan
    `resources/views/admin/attendance/index.blade.php` yang sudah ada: filter tanggal,
    pagination, relasi `member.user` dan `rfidCard`).
  - `storeManual(Request $request)` — method baru untuk mencatat check-in/check-out manual
    oleh admin (dipakai route `POST /admin/attendance/manual` yang sebelumnya tidak
    punya implementasi sama sekali). Memakai pola **sesi terbuka** yang sama seperti
    Tahap 6, supaya konsisten: kalau member sedang punya sesi terbuka → dicatat sebagai
    check-out, kalau tidak → dicatat sebagai check-in baru (`method` disimpan sebagai
    `'manual'`).

> Sudah dicek juga: tidak ada file controller lain di project yang punya masalah nama
> class serupa (nama file vs nama class di dalamnya sudah cocok semua).

> Catatan: ada 1 file lagi yang ditemukan saat penelusuran ini,
> `resources/views/admin/attendance/live.blade.php` — sebuah halaman "mode live/kiosk"
> (layar besar yang menampilkan wajah member begitu tap kartu) yang sepertinya belum
> selesai dibangun: dia memanggil route `admin.attendance.poll` yang **belum ada** di
> `routes/web.php`, dan tidak ada menu/link di sidebar yang mengarah ke halaman ini. Karena
> tidak dipakai/tidak di-link dari mana pun, halaman ini **tidak error** dan aman diabaikan
> untuk sekarang — tapi kalau kamu memang berencana memakai fitur "layar live check-in" ini,
> kabari saya supaya saya bantu selesaikan (perlu dibuatkan route `admin.attendance.poll`
> yang mengembalikan JSON check-in terbaru).

---

## Tahap 8 — Tambah Tampilan "Kartu Belum Terdaftar" & "Member Expired" saat Check-in/Check-out

**Permintaan:** kalau kartu RFID yang di-tap belum terdaftar, atau member-nya sudah
expired, widget "Check-in Terbaru" harus menampilkan pesan yang jelas — bukan diam saja
seperti sebelumnya.

**Kondisi sebelumnya:** backend memang sudah mendeteksi kartu tidak terdaftar / member
tidak aktif, tapi selalu membalas `{ "exists": false }` tanpa keterangan apa pun, dan
frontend-nya hanya `if (!data.exists) { return; }` — alias diam total, tidak ada
indikasi apa pun ke admin. Ditemukan juga bug terpisah: endpoint yang dipanggil Arduino
(`Api\MemberController@store`) akan **crash** kalau kartu belum terdaftar, karena
langsung memanggil `$card->member` padahal `$card` bisa `null`.

### File yang diubah
- `app/Http/Controllers/Admin/DashboardController.php`
  - `latestRfidCheckin()` sekarang membedakan alasan gagal lewat field `reason`:
    `unregistered` (kartu tidak dikenali/tidak terhubung member), `expired`,
    `inactive`, atau `blocked` — masing-masing disertai info UID/nama/kode member kalau
    tersedia, plus `scan_at` untuk keperluan dedup di frontend.
- `app/Http/Controllers/Api/MemberController.php`
  - Ditambah validasi di awal: kalau kartu tidak ditemukan / belum terhubung member →
    balas `404` dengan `reason: 'unregistered'` (dulu ini bikin **crash**, sekarang aman).
  - Kalau member ditemukan tapi status bukan `active` → balas `403` dengan
    `reason: 'expired'` atau `'inactive'`, **tanpa mencatat attendance apa pun**.
  - Sekalian diperbaiki bug lama: kalau `scan_uids` masih kosong, variabel `$checkUid`
    tidak pernah di-assign ulang setelah `ScanUid::create()`, jadi baris berikutnya
    (`$checkUid->uid`) crash. Sekarang hasil `create()` disimpan ke `$checkUid`.
- `resources/views/admin/dashboard.blade.php`
  - Tambah state ketiga di widget "Check-in Terbaru": **kartu belum terdaftar**,
    dengan warna aksen merah/coral (beda dari hijau/volt untuk check-in sukses),
    menampilkan judul + detail sesuai `reason` yang diterima.
  - Indikator "Menunggu kartu" di pojok kanan atas widget juga ikut berubah warna
    jadi merah dan teksnya jadi "Perlu perhatian" saat state ini aktif.
  - Dedup polling untuk state ini memakai kombinasi `reason + uid + scan_at`, supaya
    tap kartu yang sama berulang kali tidak memicu animasi berulang, tapi tap kartu
    lain (atau kartu yang sama di-tap ulang di waktu berbeda) tetap terdeteksi sebagai
    kejadian baru.

> Catatan: member dengan status `inactive` juga sudah ditangani (pesan "Member tidak
> aktif") sekalian, walau tidak diminta eksplisit — supaya konsisten dengan status
> `expired` dan tidak ada celah member nonaktif yang tetap "berhasil" checkin diam-diam.

---
|---|---|---|
| `app/Http/Controllers/Member/PhotoController.php` | **Baru** | Upload foto member (`update`) + serve foto member (`show`) |
| `app/Http/Controllers/Admin/MemberController.php` | Diubah | Tambah method `photo()` + import `Storage` |
| `app/Http/Controllers/Admin/AttendanceController.php` | Diubah | URL foto pakai route, bukan `asset('storage/...')` |
| `app/Http/Controllers/Admin/DashboardController.php` | Diubah | URL foto pakai route + fix `photo` tidak ikut terkirim di response `latestRfidCheckin()` |
| `routes/web.php` | Diubah | Tambah 3 route: `member.photo.update`, `member.photo.show`, `admin.members.photo` |
| `resources/views/member/dashboard.blade.php` | Diubah | UI upload/ganti foto + alert flash |
| `resources/views/layouts/member.blade.php` | Diubah | Tambah CSS `[x-cloak]` |
| `resources/views/admin/members/index.blade.php` | Diubah | Tampilkan foto member (mobile & desktop) |
| `resources/views/admin/dashboard.blade.php` | Diubah | Fix widget "Check-in Terbaru" agar auto-refresh saat check-out |
| `app/Http/Controllers/Api/MemberController.php` | Diubah | Fix logika check-in/check-out dari berbasis tanggal jadi berbasis sesi terbuka (mendukung multi-sesi per hari) |
| `app/Http/Controllers/Admin/AttendanceController.php` | **Ditulis ulang** | Fix fatal error class duplikat + implementasi `index()` & `storeManual()` |
| `app/Http/Controllers/Api/MemberController.php` | Diubah (lagi) | Fix crash kartu belum terdaftar + tolak checkin member expired/inactive |

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
