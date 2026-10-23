# Checklist Deployment Production — PetaPenempatan ASN

## Sebelum deploy

- [ ] `APP_ENV=production`, `APP_DEBUG=false` di `.env`
- [ ] `APP_KEY` sudah di-generate (`php artisan key:generate`)
- [ ] Kredensial database production sudah dikonfigurasi
- [ ] Jalankan `php artisan migrate --force` (bukan `--seed` — seeder demo
      otomatis diblokir di production via `abort_unless`)
- [ ] Verifikasi TIDAK ADA akun demo di database production:
      `SELECT email FROM users WHERE email LIKE '%@peta-asn.local'`
      harus kosong. Jika ada, hapus manual.
- [ ] Buat akun admin production pertama via tinker/artisan dengan
      password kuat (min. 12 karakter, unik)
- [ ] `npm run build` sudah dijalankan; `public/build/` ter-deploy
- [ ] Konfigurasi mail (SMTP) untuk reset password sudah diisi dan dites
- [ ] HTTPS aktif; `SESSION_SECURE_COOKIE=true`
- [ ] (Opsional) Konfigurasi routing jarak jalan di `.env`:
      `ROUTING_PROVIDER=osrm` (default, tanpa key) atau `ors` +
      `ROUTING_ORS_KEY` untuk OpenRouteService

## Data

- [ ] DATA-001: koordinat sekolah sudah diverifikasi lapangan
      (data awal seed: 478 sekolah negeri; lihat `docs/verifikasi-data.md`)
- [ ] Setelah login admin pertama: atur **Pengaturan Pemda** (konteks
      wilayah kerja) dan tinjau **Pengaturan Akses** (matriks menu per peran)
- [ ] Data guru asli sudah diimpor menggantikan 8 data contoh
- [ ] Backup database awal sudah dibuat dan prosedur restore sudah dites

## Kebijakan

- [ ] GOV-001: dasar tertulis bobot keluarga 1,5x sudah ditandatangani
      Dinas (lihat `docs/kebijakan-bobot-keluarga.md`)

## Setelah deploy

- [ ] Login sebagai admin, verifikasi dashboard tampil normal
- [ ] Migrasi verifikasi koordinat terpasang; seluruh sekolah awal
      berstatus BELUM terverifikasi — Kabid PTK menjalankan verifikasi
      (massal setelah konfirmasi, atau per sekolah lewat Ubah Sekolah)
      sebelum generate SPK dipakai, karena hanya sekolah terverifikasi
      yang dapat menjadi tujuan rekomendasi
- [ ] Antrean Pendaftaran Guru hanya dapat dibuka Kabid PTK (peran
      lain menerima 403) dan Jejak Audit tidak memuat nilai NIP
- [ ] Test lupa/reset password via email
- [ ] Monitor log 24 jam pertama (`storage/logs/laravel.log`)
- [ ] Jadwalkan backup database berkala (harian, retensi 30 hari)
