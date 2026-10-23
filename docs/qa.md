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

## Konversi tabel ke Livewire (12 Okt 2026)

Tujuh tabel interaktif (guru, sekolah, pengguna, usulan, SPK, swap, aktivitas)
dipindah ke komponen `App\Livewire\Tabel*` (cari/filter/sortir/pagination
tanpa reload; filter sinkron URL via `#[Url]`). Aturannya di
`AGENTS.md`/`CLAUDE.md` ("Aturan Livewire Proyek").

`TabelLivewireTest` (5 test: render awal + filter + scope peran per tabel)
lolos lokal 12 Okt 2026: total suite **118 test, 402 assertions, 0 gagal**
(sqlite `:memory:`; angka CI MySQL menyusul via workflow).

## Pembaruan fitur & QA (23 Okt 2026)

Sejak konversi di atas, tabel interaktif menjadi **11** (ditambah 4 tabel
referensi wilayah Kemendagri: provinsi, kabupaten, kecamatan, desa) dan
**12** sejak Distribusi dikonversi ke Livewire pada perbaikan audit UX
(23 Okt 2026, `TabelDistribusiTest` 4 test).
Fitur yang ditambahkan beserta tesnya:

- **Pengaturan Pemda (konteks wilayah ketat)** — `PengaturanPemdaTest`,
  `KonteksUiTest` (lencana konteks, spanduk, chip cakupan).
- **Pemilih terpencarian** (`x-searchable-select`, `x-desa-select`) —
  `SearchableSelectTest`.
- **Pagination daisyUI + pemilih baris per halaman + pencarian SPK/swap** —
  `PaginationDaisyUiTest` (4 test).
- **Pengaturan Akses** — `PengaturanAksesTest` (5 test: bawaan = aturan
  lama, tutup/buka berlaku ke route + menu, aksi tetap dijaga peran rinci,
  anti-terkunci administrator).
- **Impersonate** — `ImpersonateTest` (3 test: alur penuh, batasan,
  halaman admin tertutup selama menyamar).

Verifikasi lokal 23 Okt 2026 pada commit `1341d68`: **167 passed,
722 assertions** (PHPUnit, MySQL `peta_asn_test`) — termasuk `WilayahTest`
yang sebelumnya tercatat gagal pada commit modul wilayah. Sesuai aturan
dokumen ini, status rilis tetap ditentukan run CI yang SHA-nya sama
dengan commit yang akan dirilis.

## Audit UX semua rute & perbaikan gelombang 2 (23 Okt 2026)

Audit browser menyeluruh (38 rute desktop + 9 mobile + 15 cek peran,
salinan data asli) menemukan 2 temuan tinggi, 4 sedang, 5 rendah; nol
error JS. Laporan lengkap tersimpan di arsip QA pengguna
(`Audit_UX_SPK_2026-10-23/Laporan_Audit_UX_SPK.md`). Ringkas pelaksanaan:

- **T1** Kualitas Data 8,5 dtk → ±0,4 dtk (eager loading pulih +
  `periksa()` sekali) — terpisah, sudah di-push pada `42d7646` bersama
  **T2** (pagination ringkas di HP).
- **S1** kebocoran lebar mobile tuntas (navbar + form Simulasi) — semua
  rute mobile terverifikasi pas 375px.
- **S2** Distribusi menjadi tabel Livewire ke-12 (`TabelDistribusi`).
- **S3** bawaan menu Eksekutif (`/pemda`) kini termasuk `kabid_ptk`
  (akses lihat).
- **S4** label form terhubung `for`/`id` di semua form + aria-label
  bilah filter — 0 input tanpa label pada verifikasi akhir.
- **R1–R3** judul/H1 Profil konsisten, brand HP "Peta ASN", target
  sentuh ≥24px untuk kontrol utama. **R4** ditutup tanpa perubahan kode:
  terbukti bukan bloat aset (Leaflet hanya di halaman peta; login hangat
  median 371 ms di sandbox). **R5** menyatu dengan S2.

Verifikasi lokal gelombang 2 (belum di-push saat dicatat): **171 passed,
740 assertions**; pint bersih; audit ulang Playwright bersih.

## Metode peringkat Greedy + SAW–TOPSIS (23 Okt 2026)

Generate SPK kini memeringkat alternatif layak dengan skor hibrida
SAW + TOPSIS (`App\Services\MesinSawTopsis`; rumus, kriteria, dan bobot
di `docs/metode-saw-topsis.md`). Skor + snapshot kriteria tersimpan di
`hasil_spks` dan tampil di tabel SPK serta telaahan staf; swap tidak
berubah. Tes: `MesinSawTopsisTest` (4 unit, termasuk contoh hitung
manual), `SpkSawTopsisTest` (2 feature: peringkat + jejak skor,
alokasi greedy & prioritas keluarga pada slot tunggal).

Verifikasi lokal (belum di-push saat dicatat): **177 passed,
784 assertions**; pint bersih; build Vite sukses; migrasi
`add_skor_saw_topsis_to_hasil_spks` berjalan di salinan data sandbox.

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

## Full Livewire (23 Okt 2026)

Semua halaman indeks/dashboard dikonversi ke komponen Livewire halaman penuh (route → komponen): Dashboard, DashboardPemda, Proyeksi, TabelGuru, TabelSekolah, TabelPengguna, TabelUsulan, TabelSpk, TabelSwap, TabelDistribusi, TabelAktivitas, TabelProvinsi/Kabupaten/Kecamatan/Desa, PengaturanAkses, PengaturanPemdaLivewire (form konteks kini Livewire penuh: cascade provinsi→kabupaten reaktif, simpan/lepas via aksi komponen), KualitasDataLivewire & AksesibilitasLivewire (filter #[Url] reaktif), SimulasiLivewire (hitung seketika; route POST /simulasi lama dipertahankan). Draf komponen dari commit 1e49a8f yang duplikatif/rusak (SpkLivewire, SwapLivewire, UsulanLivewire, DistribusiLivewire, TabelGuruLivewire, TabelPenggunaLivewire, TabelSekolahLivewire + view *-index-nya) dihapus dan digantikan implementasi ini. Tes: HalamanLivewireTest (4) memastikan 16 route dirender komponennya + perilaku reaktif simulasi/kualitas/aksesibilitas; PengaturanPemdaTest dialihkan ke Livewire::test. Suite penuh 181 passed.
