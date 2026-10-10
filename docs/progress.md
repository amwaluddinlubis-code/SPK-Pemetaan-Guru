# Progress — PetaPenempatan ASN

Posisi proyek per **11 Oktober 2026**: prototipe fungsional yang lengkap
(semua alur utama jalan dan teruji), sedang bergerak menuju aplikasi
siap-production.

Teknologi: Laravel 13 + MySQL 8 + Livewire 4 + Tailwind CSS/daisyUI.

## 1. Yang Sudah Selesai

### Fondasi (9–10 Okt 2026)

- Proyek Laravel terinstal; repo GitHub `SPK-Pemetaan-Guru` (branch `main`).
- Database: **552 sekolah asli** Kab. Mandailing Natal (dari
  referensi.data.kemendikdasmen.go.id), 8 guru contoh, 7 akun demo.
- **6 peran pengguna** dengan middleware hak akses: guru, kepala_sekolah,
  ketua_pgri, kabid_ptk, kadis, pemda.
- Dokumen **standar GUI** (`docs/standar-gui.md`): pola tombol, tabel,
  form, badge status, bahasa Indonesia, dan checklist halaman baru.
- Navigasi utama berbentuk **sidebar** (desktop) / laci geser (mobile)
  — selesai dan terverifikasi render 11 Okt 2026.

### Modul Data Guru (10 Okt 2026)

- CRUD data guru + validasi (NIP unik, koordinat, dsb).
- Import Excel + template contoh.
- Kolom alamat **desa/kelurahan** (11 Okt 2026).

### Modul Data Sekolah (10 Okt 2026)

- CRUD data sekolah + kelola kebutuhan guru per mapel.
- Kolom alamat **desa/kelurahan** (11 Okt 2026).

### Modul Usulan Penempatan (11 Okt 2026)

- Alur: diajukan → diverifikasi Kabid → disetujui/ditolak Kadis.
- Persetujuan otomatis memutasi guru (transaction).

### Modul SPK Pengisian Formasi (10 Okt 2026, patch akuntabilitas 11 Okt 2026)

- Generate: guru berjarak > 15 km → 3 sekolah terdekat yang kekurangan
  formasi mapelnya (Haversine di MySQL); setujui/tolak oleh Kadis.
- Teruji end-to-end: rekomendasi dibuat dan guru termutasi dengan benar
  setelah disetujui.
- **Patch akuntabilitas**: kandidat wajib lebih dekat dari sekolah saat
  ini (penghematan positif), diurutkan by penghematan terbesar; sisa
  formasi dijaga agar tidak over-booking; `sekolah_asal_id` + `hemat_km`
  disimpan sebagai bukti; persetujuan memakai `lockForUpdate` +
  validasi ulang (cegah persetujuan ganda & mutasi parsial); kebutuhan
  formasi dikurangi otomatis saat disetujui.

### Modul Mutasi Silang / Swap (10 Okt 2026, patch keamanan 11 Okt 2026)

- Generate rekomendasi pasangan win-win; setujui/tolak oleh Kadis.
- Teruji: generate, setujui, dan tolak berjalan benar.
- **Patch keamanan**: satu guru hanya di satu rekomendasi aktif;
  persetujuan mengunci rekomendasi + kedua guru (urutan id konsisten)
  dan memvalidasi ulang posisi keduanya — gagal tanpa mutasi parsial
  jika salah satu sudah pindah.

### Modul Eksekutif Pemda (11 Okt 2026)

- Dashboard ringkasan read-only: kartu statistik, distribusi per
  kecamatan, distribusi per jenjang + defisit, rekap status
  usulan/SPK/swap.

### Quality Assurance (10 Okt 2026)

- Uji browser otomatis (login, dashboard, data guru, SPK, swap):
  semua lolos. Database di-reset bersih setelah pengujian.

## 2. To-Do List

### Prioritas 1 — Paket "Fitur Dasar" (disetujui 10 Okt 2026, belum dikerjakan)

- [ ] Ganti password sendiri
- [ ] Lupa / reset password via email
- [ ] Halaman profil pengguna
- [ ] Export Excel/PDF untuk tabel guru, sekolah, usulan
- [ ] Import data sekolah via Excel
- [ ] Halaman error 403/404 yang ramah
- [ ] Sorting kolom pada tabel
- [ ] Info pagination ("menampilkan 1–10 dari 552")

### Prioritas 2 — Menuju Production

- [ ] Import data guru asli (menggantikan 8 data contoh)
- [ ] Verifikasi koordinat 552 sekolah (saat ini hasil estimasi)
- [ ] Ganti/hapus password akun demo (`admin123`, `kabid123`, dll.)
- [ ] Branding Dinas (logo, nama instansi)
- [ ] Deploy ke server production + domain
- [ ] Jadwal backup database berkala

### Ditunda (keputusan: fitur dasar dulu)

- Cetak SK Mutasi dalam bentuk PDF
- Notifikasi WhatsApp untuk status usulan

## 3. Risiko dan Catatan

1. **Koordinat sekolah adalah estimasi** — perlu verifikasi lapangan
   sebelum dipakai untuk keputusan resmi.
2. **Akun demo masih aktif** — wajib diganti sebelum production.
3. File `database.sql` di repo **tidak mencakup** tabel
   `usulan_penempatan` dan kolom-kolom terbaru. Cara instal yang
   disarankan: `php artisan migrate --seed`.
4. Peta Leaflet membutuhkan koneksi internet untuk memuat tile peta.


## 4. Audit keamanan lanjutan (branch `security/login-throttle-demo-seeder`)

- Ditambahkan pembatasan login `throttle:5,1` untuk mengurangi brute-force berbasis IP.
- Seeder guru contoh dan akun demo dibatasi ke `local`/`testing`; `AdminSeeder` juga menolak lingkungan lain.
- Tes regresi ditambahkan untuk rate limit dan guard seeder production.
- Workflow PHPUnit diperbaiki agar direktori tes repo benar-benar disalin ke runtime Laravel.
- Verifikasi GitHub Actions run `38077124705` pada SHA `42b3f02a743abfd671715edc385279901c24819a`: **18 tes lulus, 59 assertions**.
- Dokumentasi temuan: `docs/AUDIT-KEAMANAN-2026-10.md`.
- Catatan: verifikasi otomatis bukan pengganti uji penetrasi atau pemeriksaan deployment production.

### Audit keamanan lanjutan — SEC-003

- Ditemukan bahwa halaman daftar SPK dan mutasi silang di branch `main` belum menerapkan pembatasan cakupan peran yang sudah dipakai pada daftar guru/usulan.
- Query daftar SPK dan swap diperbaiki di branch PR #2 untuk membatasi hasil bagi guru, kepala sekolah, dan Ketua PGRI.
- Tes regresi ditambahkan untuk kedua halaman dan ketiga peran tersebut. Status kelulusan menunggu hasil GitHub Actions setelah push.
