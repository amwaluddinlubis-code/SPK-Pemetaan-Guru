# Roadmap — PetaPenempatan ASN

## Selesai: Mesin Pemerataan Guru (11 Okt 2026)

- [x] **G. Audit trail**: tabel `activity_logs` (append-only), model
      `ActivityLog` dengan redaksi rahasia, halaman `/aktivitas`
      (kabid_ptk, filter aksi/aktor/hasil/tanggal), hook pada
      CRUD guru/sekolah, import, generate & keputusan SPK/swap/usulan.
- [x] **D. Alur persetujuan**: alasan wajib saat menolak (usulan/SPK/swap),
      riwayat transisi status di halaman usulan, perbaiki kebocoran
      `$e->getMessage()` di UsulanController menjadi pesan generik + log.
- [x] **A. Kualitas data**: `/kualitas-data` (kabid/kadis) — deteksi NIP/NPSN
      duplikat, koordinat kosong/invalid, relasi invalid, kebutuhan tak
      masuk akal; klasifikasi dampak; ekspor minim PII (read-only).
- [x] **B. Distribusi defisit/surplus**: `/distribusi` (kabid/kadis/pemda) —
      agregat per kecamatan x mapel, rumus defisit/surplus terdokumentasi,
      guru dalam proses dihitung sekali.
- [x] **E. Aksesibilitas**: `/aksesibilitas` (kabid/kadis) — agregat jarak
      Haversine per kecamatan/kategori daerah, disclaimer bukan waktu tempuh.
- [x] **C. Simulasi what-if**: `/simulasi` (kabid/kadis) — in-memory, tanpa
      ubah data operasional; parameter ambang/mapel/kecamatan/3T; disclaimer
      hipotetis + versi algoritme.
- [x] **F. Proyeksi**: `/proyeksi` — jujur "data belum cukup" (tanpa deret
      waktu historis), syarat data & metodologi terdokumentasi.
- [x] **H. Keamanan**: semua route baru bermiddleware role + tes akses;
      N+1 dicegah (eager loading/agregat SQL); pint lolos.
- [x] **Hasil: 98 passed, 315 assertions.**

## Selesai: Prioritas P0 — Sebelum production

- [x] Perbaiki scope data pada daftar SPK dan swap untuk setiap peran
      (11 Okt 2026: `terapkanScopePeran()` di SpkController &
      SwapController).
- [x] **Keputusan kebijakan pemda** (11 Okt 2026): peran `pemda` dibatasi
      pada dashboard (`/`) dan dashboard eksekutif (`/pemda`) saja.
- [x] **Hardening race condition generate** (11 Okt 2026): SATU transaksi
      + `lockForUpdate` urutan konsisten + hitung ulang kapasitas dari
      data terkini + retry deadlock 3x.
- [x] Tes regresi akses lintas peran (5 tes baru).
- [x] Amankan aksi penolakan dan semua transisi status dengan transaksi,
      penguncian, serta validasi ulang yang konsisten.
- [x] Cegah race condition pada generate SPK/swap dan alokasi formasi.
- [x] QA browser Issue #3: 47/47 PASS (desktop/mobile/tablet).
- [x] Bundle Leaflet via Vite — hapus ketergantungan CDN unpkg (11 Okt 2026).

## Selesai: Prioritas 1 — Paket "Fitur Dasar" (10–11 Okt 2026)

- [x] Ganti password sendiri; lupa/reset password via email; halaman profil.
- [x] Export Excel/PDF tabel guru, sekolah, usulan.
- [x] Import data sekolah via Excel (template + validasi per baris).
- [x] Halaman error 403/404 yang ramah; sorting kolom tabel; info pagination.
- [x] 16 regression test paket fitur dasar (`FiturDasarTest`).

## Selesai: Peningkatan GUI (11 Okt 2026)

- [x] Navbar sticky/freeze di semua viewport (sebelumnya hanya mobile).
- [x] Tema light/dark/system (toggle di navbar, tersimpan di localStorage,
      mengikuti preferensi OS, cegah flicker).
- [x] Tampilan kartu mobile untuk tabel guru (pengganti scroll horizontal).
- [x] Ikon SVG (Heroicons) menggantikan emoji di sidebar & judul halaman.
- [x] Menu sidebar dikelompokkan: Data Master, Proses Penempatan, Analisis,
      Sistem.
- [x] Hierarki visual dashboard eksekutif: metrik "Menunggu Keputusan"
      ditonjolkan.
- [x] Form guru berseksi: Data Pribadi, Kepegawaian, Domisili.
- [x] Aksesibilitas dasar: skip link, indikator fokus keyboard.
- [x] 9 test baru (`NavbarTemaTest`); total 109 passed.

## Selesai: Konteks Pemda, Pengaturan Akses, Impersonate (23 Okt 2026)

- [x] **Referensi Wilayah Kemendagri terintegrasi**: 4 tabel Livewire
      (provinsi/kabupaten/kecamatan/desa) + API dropdown berjenjang +
      seeder nasional (37 provinsi, 514 kabupaten, 7.277 kecamatan,
      83.761 desa).
- [x] **Pengaturan Pemda**: konteks wilayah kerja (provinsi/kabupaten)
      dengan konteks ketat (global scope), identitas pemda di layout,
      lencana konteks + spanduk pengingat + chip cakupan.
- [x] **Pemilih terpencarian** (combobox) di filter & form, termasuk
      pemilih desa mengikuti kecamatan.
- [x] **Import Rombel Dapodik** per kecamatan (pencocokan NPSN) +
      **Terapkan Analisis Massal** kebutuhan guru (rombel × JP
      Permendikdasmen No. 13/2025 ÷ 24).
- [x] **Pagination daisyUI** terpusat + pemilih baris per halaman
      (10–100) di semua tabel; pencarian teks untuk tabel SPK & swap.
- [x] **Pengaturan Akses**: matriks menu/route per peran (gerbang
      middleware + sidebar dari registri), anti-terkunci administrator,
      tercatat di Jejak Audit.
- [x] **Impersonate**: admin masuk sebagai pengguna lain (bilah
      peringatan + kembali), tercatat di Jejak Audit.
- [x] Data awal sekolah dibatasi **status NEGERI (478)**; NPSN SDN 051
      Bange dibetulkan (10207979).
- [x] Container utama diperlebar (tanpa batas `max-w-6xl`).
- [x] **Hasil lokal: 167 passed, 722 assertions** pada commit `1341d68`
      (CI per-SHA tetap menjadi penentu status rilis).

## Prioritas 2 — Menuju Production (belum)

- [ ] Import data guru asli (mengganti 8 data contoh).
- [ ] Verifikasi koordinat sekolah pada data awal (478 sekolah negeri;
      saat ini estimasi) — data live dapat bertambah lewat import.
- [ ] Branding Dinas; deploy + domain; backup berkala dan uji pemulihan.
- [ ] Ganti/hapus akun demo dan password bawaan; verifikasi konfigurasi
      environment, backup, dan prosedur pemulihan. **(Operasional.)**
- [ ] Uji beban staging (konkurensi produksi).
- [x] Viewport 1280×800 dan 360×800 (QA browser lanjutan) — PASS 11 Okt 2026.
- [x] Hapus file docs usang di remote — selesai 11 Okt 2026.

## Ditunda (keputusan: fitur dasar dulu)

- Cetak SK Mutasi PDF; notifikasi WhatsApp untuk status usulan.

## Risiko dan Catatan

1. **Koordinat sekolah adalah estimasi** — perlu verifikasi lapangan
   sebelum dipakai untuk keputusan resmi.
2. **Akun demo berisi kredensial berpola dan tidak boleh digunakan di
   production.** Seeder membatasi pembuatan akun demo ke lingkungan
   `local`/`testing`; verifikasi konfigurasi dan akun yang sudah ada
   tetap wajib saat deployment.
3. File `database.sql` yang usang telah **dihapus** dari repo.
   Satu-satunya cara instal yang didukung: `php artisan migrate --seed`.
4. Tile peta Leaflet membutuhkan koneksi internet (dari server
   OpenStreetMap). Library Leaflet sendiri sudah dibundle via Vite
   (tidak tergantung CDN lagi sejak 11 Okt 2026).
5. Jarak yang ditampilkan adalah garis lurus, bukan jarak tempuh jalan.
6. Audit statis tidak menggantikan pengujian runtime, pengujian penetrasi,
   atau verifikasi kepatuhan administrasi kepegawaian.
7. **Uji beban**: `php artisan serve` adalah server development
   single-threaded — tidak cocok untuk uji beban konkurensi. Uji beban
   yang valid membutuhkan nginx + php-fpm. Login throttle (5/menit/IP)
   terkonfirmasi memblokir brute force (30 login bersamaan → 25 ditolak).
   Uji beban production wajib dilakukan di environment staging yang
   menyerupai production.
8. **Akses daftar guru/sekolah/usulan untuk peran pemda** sudah diputuskan:
   dibatasi ke peran operasional (route + menu), sejalan data minimization.
   Sejak 23 Okt 2026 pembatasan ini diatur **Pengaturan Akses** dan dapat
   diubah administrator tanpa mengubah kode.
9. **GOV-001**: bobot keluarga 1,5x perlu dasar kebijakan tertulis Dinas
   sebelum dipakai operasional.
