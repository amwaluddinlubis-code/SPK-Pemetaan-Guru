# Quality Assurance — PetaPenempatan ASN

## Aturan proyek

- **1 fitur = 1 test** — setiap fitur baru atau perubahan perilaku wajib
  disertai minimal 1 test di `tests/Feature/`.
- Test memakai database MySQL `peta_asn_test` (bukan SQLite — query
  Haversine butuh fungsi MySQL). Jalankan dengan `php artisan test`.
- Alur kerja sebelum commit/push: test hijau, `vendor/bin/pint`, update
  dokumentasi bila diperlukan (lihat `.cursorrules`).

## Hasil verifikasi 11 Okt 2026 (Mesin Pemerataan Guru)

`KeamananPersetujuanTest` (14) + `CakupanFiturTest` (19) +
`RegressionAuditTest` (9) + `FiturDasarTest` (20) +
`OtorisasiIdorTest` (9) + `JejakAuditTest` (6) + `KualitasDataTest` (6) +
`DistribusiTest` (4) + `AksesibilitasTest` (3) + `SimulasiTest` (5) +
`ProyeksiTest` (3) =
**98 passed, 315 assertions** tercatat pada commit implementasi fitur `fcd858b`. Angka ini tidak mewakili tes setelah audit lanjutan di bawah; gunakan run CI yang SHA-nya cocok dengan commit terbaru untuk status saat ini.

## CI

Workflow `.github/workflows/phpunit.yml` SUDAH ADA di repo (dibuat sesi
lain): GitHub Actions (PHP 8.3 + MySQL 8 service) membangun runtime Laravel
via `composer create-project` + overlay source, lalu menjalankan
`php artisan test` pada push dan PR ke `main`.

## Jarak jalan hybrid (11 Okt 2026)

Komputasi massal (generate SPK/swap) tetap memakai Haversine (cepat,
offline, gratis). Jarak tempuh jalan hanya diambil untuk rekomendasi
final yang ditampilkan di halaman SPK, dengan alur:

1. **Cache** — tabel `jarak_jalan_cache`, kunci koordinat dibulatkan
   4 desimal (~11 m). Cek cache dulu, tanpa hit API bila ada.
2. **API routing** — bila cache miss: `App\Services\RoutingJalan`
   query OSRM (default, tanpa key) atau OpenRouteService bila
   `ROUTING_PROVIDER=ors` + `ROUTING_ORS_KEY` di `.env`. Hasil disimpan
   ke cache.
3. **Fallback** — bila API gagal/timeout, otomatis tampil garis lurus
   (Haversine) dengan label yang jelas; halaman tidak pernah rusak.

Tampilan: badge 🚗 jalan di samping jarak garis lurus, lengkap dengan
estimasi waktu tempuh ± menit (hover untuk detail sumber/cache).

Test: `tests/Feature/RoutingJalanTest.php` (4 test) — cache dipakai tanpa
hit API; fallback Haversine saat API gagal; respons OSRM diparse dan
disimpan; kunci cache dibulatkan 4 desimal. Catatan: kolom penghematan
tetap memakai garis lurus (Haversine).

## Verifikasi concurrency nyata (11 Okt 2026)

Dua instance `php artisan serve` (port 8001 & 8002, satu database MySQL)
menjalankan generate SPK dan generate swap secara bersamaan (curl paralel).
Skenario: 1 slot formasi diperebutkan 6 guru (SPK) dan 1 guru yang
win-win dengan 2 pasangan (swap). Hasil: 3 ronde paralel SPK →
tepat 1 rekomendasi aktif untuk slot tersebut; 1 ronde paralel swap →
tepat 1 swap aktif melibatkan guru yang diperebutkan. Invariant
kapasitas/anti-tumpang-tindih bertahan di bawah eksekusi paralel nyata.
Batasan: ini uji ad-hoc via HTTP (bukan bagian dari suite PHPUnit);
beban produksi yang lebih tinggi tetap perlu uji beban di staging.

## Strategi transaksi & locking (SPK & swap)

- **Generate SPK** (`SpkController@generate`): SATU transaksi —
  (1) baca kandidat + `lockForUpdate` (urutan `id` konsisten),
  (2) kunci baris `kebutuhan_guru` untuk mapel kandidat (urutan `id`
  konsisten), (3) hitung ulang `sisa = jumlah - alokasi aktif
  (status=rekomendasi)` dari data terkini, (4) cek duplikat + alokasi
  slot + insert rekomendasi. Retry deadlock 3x
  (`DB::transaction($cb, 3)` — Laravel hanya mengulang untuk deadlock,
  bukan untuk `RuntimeException` aturan bisnis).
- **Generate swap** (`SwapController@generate`): SATU transaksi —
  baca kandidat + lock, hitung ulang `$terpakai` (swap `menunggu`) dan
  `$usulanAktif` dari data terkini, greedy matching, insert. Retry 3x.
- **Setujui/tolak** (SPK, swap, usulan): kunci baris target
  (`lockForUpdate`), validasi ulang status + prasyarat di dalam transaksi,
  lalu mutasi. `RuntimeException` → pesan bisnis ke pengguna;
  `Throwable` teknis → dicatat ke log, pengguna hanya melihat pesan umum
  (tidak ada detail SQL yang bocor).
- **Urutan penguncian konsisten** (guru → kebutuhan) di semua alur untuk
  menghindari deadlock siklik.
- **Unique constraint**: dievaluasi dan DITOLAK — rekomendasi boleh dibuat
  ulang setelah ditolak; unique index akan melanggar aturan bisnis
  (MySQL tidak mendukung partial unique index).

## QA browser putaran 2 (11 Okt 2026)

Playwright headless, skrip `~/workspace/qa-peta-asn/qa2.py`: login kabid &
pemda OK; dashboard, data guru (badge kepegawaian/Serdik/linearitas), form
guru (field kepegawaian), data sekolah (kolom kategori daerah), generate SPK
(21 rekomendasi + kolom Kelayakan + tombol Telaahan), telaahan staf
(render lengkap: kop, dasar, data, analisis, saran, tanda tangan),
swap, usulan, dan dashboard pemda — semua ter-render dengan benar.
Screenshot tersimpan di `~/workspace/qa-peta-asn/shots2/`.

Tes wajib mencakup: cakupan data per peran untuk seluruh daftar SPK/swap;
akses langsung ke detail/aksi; persetujuan dan penolakan bersamaan;
dua proses generate bersamaan; kapasitas formasi; dan larangan satu guru
berada di beberapa rekomendasi aktif.

## QA browser Issue #3 — SELESAI (11 Okt 2026, ~05:30 WIB)

Pelacakan: [Issue #3](https://github.com/amwaluddinlubis-code/SPK-Pemetaan-Guru/issues/3) (ditutup).

- **Commit yang diuji:** `053741f` (+ perbaikan responsif mobile)
- **Browser:** Chromium 153.0.8010.12 (Playwright, headless)
- **Skrip:** `~/workspace/qa-peta-asn/qa3.py`, screenshot di `shots3/` (47 file)
- **Hasil: 47/47 PASS**

| Area | Status | Bukti/catatan |
|---|---|---|
| Browser QA desktop | **PASS** | 6 peran (kabid, kadis, pemda, guru, kepsek, ketua PGRI) — semua halaman utama render 200, tanpa overflow, tanpa console error (kecuali 2 error CDN eksternal pada dashboard — sudah diperbaiki dengan bundle Leaflet via Vite, 11 Okt 2026). |
| GUI mobile (390×844) | **PASS setelah perbaikan** | Awalnya FAIL: navbar overflow + tabel guru overflow. Diperbaiki: judul navbar disingkat di mobile, info user jadi ikon, baris tombol `flex-wrap`, tabel dalam card diberi `overflow-hidden` + `min-w-0`. |
| Tablet (768×1024) | **PASS** | Dashboard & SPK render baik, tanpa overflow. |
| Otorisasi visual per peran | **PASS** | Kadis/pemda/guru/kepsek ditolak (403) pada route yang bukan haknya; pemda hanya bisa `/pemda` + `/distribusi`; guru bisa `/guru` & `/spk` tapi scope data milik sendiri. |
| Login throttle | **PASS (terkonfirmasi bekerja)** | Login ke-6 dalam 1 menit ditolak — sesuai `throttle:5,1`. |

### Matriks viewport

| Perangkat | Viewport | Status |
|---|---:|---|
| Desktop | 1440×900 | PASS |
| Desktop | 1280×800 | PASS |
| Tablet | 768×1024 | PASS |
| Mobile | 390×844 | PASS |
| Mobile kecil | 360×800 | PASS |

### Temuan & perbaikan selama QA

1. **Navbar mobile overflow** — judul "PetaPenempatan ASN" + nama user + tombol Keluar tidak muat di 390px. Perbaikan: judul disingkat "PPA" di layar kecil (`hidden sm:inline`), nama user disembunyikan jadi ikon (`hidden md:inline`).
2. **Tabel & baris tombol overflow di mobile** — halaman guru/sekolah/pengguna/usulan. Perbaikan: `flex-wrap` pada baris tombol, `overflow-hidden` + `min-w-0` pada card tabel.
3. **Ekspektasi QA yang keliru (bukan bug)** — guru memang boleh membuka `/guru` & `/spk` (scope data milik sendiri via `terapkanScopePeran()`); skrip QA awal salah mengharapkan 403.
4. **Ketergantungan CDN** — Leaflet dimuat dari unpkg.com; gagal saat offline. Diperbaiki 11 Okt 2026: Leaflet dibundle via Vite (`resources/js/peta.js`), tidak lagi tergantung CDN.

### Standar bukti dan pelaporan

- Catat SHA commit yang diuji, tanggal, browser/versi, viewport, role, route, langkah, status, dan temuan.
- Simpan screenshot yang aman dan diberi nama berdasarkan route/role/viewport; jangan merekam NIP, alamat, koordinat pribadi, kredensial, token, atau data sensitif.
- Catat error console dan request gagal; setiap bug FAIL harus memiliki langkah reproduksi dan issue tersendiri.
- Setelah eksekusi nyata, ganti status NOT RUN dengan hasil berbasis bukti dan tautkan artefak. Jangan menyimpulkan lulus dari render statis atau dari CI PHPUnit saja.
- Semua ide, tugas, temuan, dan perintah lanjutan terkait pekerjaan repositori dicatat sebagai GitHub Issue; dokumentasi merangkum status dan menautkan issue, bukan menjadi satu-satunya backlog.
