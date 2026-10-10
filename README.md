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
- 🔄 Mutasi silang (swap): rekomendasi pertukaran dua guru yang saling menguntungkan (win-win, keduanya lebih dekat ke domisili) + alur persetujuan Kadis
- 🎯 SPK penempatan (pengisian formasi): rekomendasi guru yang jarak tugasnya > 15 km ke sekolah terdekat yang kekurangan formasi mapelnya (Haversine di MySQL) + alur persetujuan Kadis
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
│   ├── Http/Controllers/   ← Auth, Dashboard, Guru, Sekolah, Spk, Swap, Usulan, User
│   ├── Livewire/           ← PetaRekomendasi.php
│   └── Models/             ← Guru, Sekolah, KebutuhanGuru, HasilSpk, RekomendasiSwap
├── database/
│   ├── migrations/         ← guru, sekolah, kebutuhan_guru, hasil_spks, rekomendasi_swaps, ...
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

## 3. Cara kerja SPK & rekomendasi

Sistem memiliki dua skenario SPK formal, plus rekomendasi interaktif di dashboard.

### Skenario 1 — Pengisian formasi (SPK)

1. Kabid PTK menekan **"Generate SPK"** di halaman SPK Penempatan.
2. Sistem menyeleksi guru yang jarak domisili → sekolah saat ini **melebihi ambang
   batas 15 km** (konstanta `SpkController::AMBANG_KM`).
3. Untuk tiap guru, sistem mencari sekolah yang kekurangan formasi (`jumlah > 0`)
   untuk mapel yang sama — jarak dihitung **langsung di query MySQL** dengan rumus
   Haversine, diurutkan dari terdekat, diambil 3 teratas (`MAKS_REKOMENDASI`).
4. Kadis menyetujui → guru otomatis dimutasi dalam satu database transaction;
   rekomendasi lain untuk guru yang sama otomatis ditolak agar tidak terjadi
   penempatan ganda.

### Skenario 2 — Mutasi silang (swap)

1. Kabid PTK menekan **"Generate Rekomendasi"** di halaman Mutasi Silang.
2. Sistem mencocokkan semua pasangan guru (O(N²)): syaratnya mapel sama, sekolah asal
   berbeda, dan **keduanya** lebih dekat ke domisili setelah bertukar (dihitung dengan
   rumus Haversine via `Guru::jarakKe()`).
3. Pasangan diurutkan berdasarkan **total penghematan jarak** terbesar.
4. Kadis menyetujui → kedua guru otomatis dimutasi dalam satu database transaction;
   rekomendasi lain yang melibatkan guru tersebut otomatis dibatalkan
   (`batal_karena_konflik`) agar tidak terjadi penempatan ganda.

### Rekomendasi interaktif (dashboard)

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
8. `app/Http/Controllers/SpkController.php` — SPK pengisian formasi (Haversine di MySQL)
9. `app/Http/Controllers/SwapController.php` — mutasi silang + resolusi konflik

---

## 5. Troubleshooting

| Masalah | Solusi |
|---|---|
| `php artisan migrate` → Access denied | Cek DB_USERNAME/DB_PASSWORD di `.env` |
| Halaman putih / `Vite manifest not found` | Jalankan `npm run build` (Langkah 7) |
| Class `Livewire\...` not found | Jalankan `composer require livewire/livewire` lalu `composer dump-autoload` |
| Peta tidak muncul | Butuh internet (tile OpenStreetMap + CDN Leaflet) |
| Port 8000 dipakai | `php artisan serve --port=8001` |
| Generate SPK/swap tidak menemukan apa-apa | Pastikan guru punya koordinat domisili + `sekolah_id`, dan ada kebutuhan formasi (`jumlah > 0`) untuk mapelnya |

---

Dibuat dengan ❤️ untuk para guru Indonesia.
