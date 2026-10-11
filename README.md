# 🗺️ PetaPenempatan ASN

Aplikasi pemetaan penempatan Guru ASN berbasis domisili.
Dibangun dengan **TALL stack**: **T**ailwind CSS + **A**lpine.js + **L**aravel + **L**ivewire,
dengan komponen UI dari **daisyUI**.

**Fitur:**
- 🗺️ Dashboard peta interaktif (Leaflet + OpenStreetMap, gratis)
- 🎯 Rekomendasi sekolah terdekat yang membutuhkan mapel guru (otomatis, tanpa reload — Livewire)
- 🔍 Pencarian + filter kecamatan di tabel guru & sekolah, filter peran di tabel pengguna
- 📍 Pemilih koordinat klik-peta di form guru & sekolah (klik/geser pin → lat/lng terisi otomatis)
- 👩‍🏫 CRUD Data Guru (NIP, mapel, domisili + koordinat; kecamatan berupa dropdown)
- 📥 Import massal data guru dari Excel/CSV + unduh template (khusus administrator)
- 📝 Alur usulan penempatan: Diajukan → Diverifikasi Kabid → Disetujui Kadis (otomatis mutasi) / Ditolak; bisa diajukan langsung dari hasil rekomendasi peta
- 🏫 CRUD Data Sekolah + kebutuhan guru per mapel (baris dinamis — Alpine.js)
- 🔐 Login dengan **5 tingkatan pengguna** (hak akses & scope berbeda)
- 👥 Kelola akun pengguna (khusus administrator)

## Sumber Data

- **Data sekolah (552)**: ASLI dari [referensi.data.kemendikdasmen.go.id](https://referensi.data.kemendikdasmen.go.id/pendidikan/dikdas/071500/2) — seluruh SD/SMP se-Kab. Mandailing Natal, Sumatera Utara (diakses 2026-10-10). NPSN, nama, alamat, kecamatan, status = data resmi.
- **Koordinat sekolah**: PERKIRAAN (geocoding kecamatan via Nominatim/Wikipedia + sebaran acak). Jangan dipakai untuk keperluan resmi — ganti dengan koordinat hasil survei/Dapodik.
- **Data guru (8)**: CONTOH fiktif untuk demo (domisili di sekitar Panyabungan).
- **Kebutuhan guru**: CONTOH untuk demo — ganti dengan data riil.

## Tingkatan Pengguna

| Peran | Scope Data | Hak Akses |
|---|---|---|
| **Guru** | Dirinya sendiri | Lihat data & rekomendasi sendiri; boleh ubah datanya sendiri |
| **Kepala Sekolah** | Sekolahnya | CRUD guru di sekolahnya; ubah data & kebutuhan sekolahnya |
| **Ketua PGRI Kecamatan** | Kecamatan | Lihat saja (read-only) data se-kecamatan |
| **Kabid PTK** | Kabupaten (semua) | Administrator: CRUD penuh guru, sekolah, dan kelola pengguna |
| **Kadis Pendidikan** | Semua | Reviewer: lihat semua (read-only) |

Akun contoh (password = nama peran + `123`, segera ganti!):

| Email | Password | Peran |
|---|---|---|
| `admin@peta-asn.local` | `admin123` | Kadis (reviewer) |
| `guru@peta-asn.local` | `guru123` | Guru (Siti Aminah) |
| `kepsek@peta-asn.local` | `kepsek123` | Kepala SD Negeri 076 Panyabungan |
| `pgri@peta-asn.local` | `pgri123` | Ketua PGRI Kec. Panyabungan |
| `kabid@peta-asn.local` | `kabid123` | Kabid PTK (administrator) |
| `kadis@peta-asn.local` | `kadis123` | Kadis (reviewer) |

> ⚠️ Paket ini berisi **kode sumber aplikasi** (tanpa folder `vendor/` dan `node_modules/`).
> Ikuti langkah instalasi di bawah — total ±15 menit.

---

## 1. Prasyarat

Instal dulu di komputermu:

| Kebutuhan | Versi minimal | Cara instal (Windows) |
|---|---|---|
| PHP | 8.2+ | [Laragon](https://laragon.org) (disarankan — sudah termasuk MySQL) atau XAMPP |
| Composer | terbaru | [getcomposer.org](https://getcomposer.org/download/) |
| MySQL / MariaDB | 8 / 10.4+ | ikut Laragon/XAMPP, atau [mysql.com](https://www.mysql.com/downloads/) |
| Node.js | 18+ | [nodejs.org](https://nodejs.org) (untuk Tailwind/daisyUI) |

Cek lewat terminal:
```
php -v
composer -V
node -v
```

---

## 2. Instalasi

### Langkah 1 — Buat proyek Laravel baru
```bash
composer create-project laravel/laravel peta-asn
cd peta-asn
```

### Langkah 2 — Salin file dari paket ini
Salin **seluruh isi** folder `peta-asn-source/` ke dalam folder `peta-asn/`,
timpa file yang sama namanya (`routes/web.php`, `vite.config.js`, dll).

> **Penting:** daftarkan middleware `role` di `bootstrap/app.php`:
> ```php
> ->withMiddleware(function (Middleware $middleware): void {
>     $middleware->alias([
>         'role' => \App\Http\Middleware\RoleMiddleware::class,
>     ]);
> })
> ```

Struktur akhirnya:
```
peta-asn/
├── app/
│   ├── Http/Controllers/   ← Auth, Dashboard, Guru, Sekolah
│   ├── Livewire/           ← PetaRekomendasi.php
│   └── Models/             ← Guru, Sekolah, KebutuhanGuru
├── database/
│   ├── migrations/         ← tabel guru, sekolah, kebutuhan_guru
│   └── seeders/            ← admin + data contoh
├── resources/views/        ← Blade + daisyUI
├── routes/web.php
├── vite.config.js
└── ...
```

### Langkah 3 — Instal dependency PHP (termasuk Livewire)
```bash
composer require livewire/livewire phpoffice/phpspreadsheet
```

### Langkah 4 — Instal dependency JS (Tailwind + daisyUI)
```bash
npm install
npm install -D tailwindcss @tailwindcss/vite daisyui
```

### Langkah 5 — Konfigurasi database
Salin `.env.example` menjadi `.env`, lalu sesuaikan:
```bash
cp .env.example .env
php artisan key:generate
```
Ubah di `.env`:
```ini
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=peta_asn
DB_USERNAME=root
DB_PASSWORD=
```
Buat database kosong bernama `peta_asn` di MySQL (via phpMyAdmin atau `CREATE DATABASE peta_asn;`).

> **Alternatif cepat:** impor langsung `database.sql` dari paket ini ke MySQL
> (sudah berisi tabel + data contoh + akun admin). Jika pakai cara ini,
> lewati Langkah 6.

### Langkah 6 — Migrasi + isi data contoh
```bash
php artisan migrate --seed
```

### Langkah 7 — Build aset (CSS/JS)
```bash
npm run build
```

### Langkah 8 — Jalankan! 🚀
```bash
php artisan serve
```
Buka **http://127.0.0.1:8000** di browser.

**Login default:** lihat tabel akun di atas (mis. `kabid@peta-asn.local` / `kabid123`)
⚠️ Segera ganti password setelah masuk!

---

## 3. Cara kerja rekomendasi

1. Setiap guru punya **koordinat domisili**; setiap sekolah punya **koordinat** + **kebutuhan mapel**.
2. Saat tombol *"Cari Sekolah Terdekat"* diklik, komponen Livewire `PetaRekomendasi`:
   - mengambil semua sekolah yang `kebutuhan.mapel == guru.mapel`,
   - menghitung jarak domisili → sekolah dengan **rumus Haversine** (`Guru::jarakKe()`),
   - mengurutkan dari terdekat dan menampilkan 5 teratas,
   - menggambar garis putus-putus ke 3 teratas di peta (via event `rute-diperbarui`).
3. Semua terjadi **tanpa reload halaman** (Livewire) — peta tidak di-render ulang
   berkat `wire:ignore`.

---

## 4. Peta belajar dari kode ini 🌱

Urutan baca yang disarankan untuk memahami aplikasi:

1. `routes/web.php` — alur halaman aplikasi
2. `app/Models/` — struktur data & relasi (hasMany / belongsTo)
3. `database/migrations/` — cara tabel dibuat
4. `app/Http/Controllers/GuruController.php` — CRUD standar
5. `app/Livewire/PetaRekomendasi.php` — komponen interaktif
6. `resources/views/livewire/peta-rekomendasi.blade.php` — peta + Alpine.js + event
7. `resources/views/layouts/app.blade.php` — layout daisyUI + navbar

---

## 5. Fitur dasar

- **Profil** (`/profil`): lihat & ubah nama/email, ganti password sendiri
- **Lupa password** (`/lupa-password`): reset via email — butuh konfigurasi MAIL:
  ```env
  MAIL_MAILER=smtp
  MAIL_HOST=smtp.gmail.com
  MAIL_PORT=587
  MAIL_USERNAME=email.anda@gmail.com
  MAIL_PASSWORD="kata sandi aplikasi"
  MAIL_ENCRYPTION=tls
  MAIL_FROM_ADDRESS=email.anda@gmail.com
  ```
  (Untuk Gmail, buat "App Password" di pengaturan akun Google.)
- **Export CSV**: tombol 📤 di halaman Data Guru, Data Sekolah, Usulan — mengikuti hak akses peran
- **Import Sekolah** (`/sekolah/import`): seperti import guru, template bisa diunduh
- **Sorting tabel**: klik header kolom (Nama, Mapel, Kecamatan, Status, Tanggal)
- **Info pagination**: "Menampilkan 1–15 dari 552 data" di bawah tabel
- **Halaman error ramah**: 403 & 404 dengan tombol kembali

---

## 6. Troubleshooting

| Masalah | Solusi |
|---|---|
| `php artisan migrate` → Access denied | Cek DB_USERNAME/DB_PASSWORD di `.env` |
| Halaman putih / `Vite manifest not found` | Jalankan `npm run build` (Langkah 7) |
| Class `Livewire\...` not found | Jalankan `composer require livewire/livewire` lalu `composer dump-autoload` |
| Peta tidak muncul | Butuh internet (tile OpenStreetMap + CDN Leaflet) |
| Port 8000 dipakai | `php artisan serve --port=8001` |

---

Dibuat dengan ❤️ untuk para guru Indonesia.
