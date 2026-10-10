# Audit Menyeluruh — SPK Pemetaan Guru ASN

- **Repositori:** `amwaluddinlubis-code/SPK-Pemetaan-Guru`
- **Baseline commit yang diperiksa:** `1158318d92109abc2f51ed4bf5781f852c7686ac` (branch `main`)
- **Jenis audit:** pemeriksaan statis terhadap kode dan dokumentasi yang telah ditinjau.
- **Status:** rekomendasi dan temuan awal; belum seluruh repositori diuji secara runtime.
- **Pembaruan 11 Okt 2026:** status tiap temuan diperbarui ke progres terkini (commit `cb4aa43c`); 6 checklist tes penerimaan terverifikasi via uji HTTP.
- **Pembaruan 11 Okt 2026 (batch 2):** temuan SEC-001, SEC-002, FLOW-001, FLOW-002, DATA-001 terverifikasi; DOC-001 diperbaiki; TEST-001 dikerjakan (commit `e63b2b7e`); 4 checklist tambahan dicentang via PHPUnit.
- **Pembaruan 11 Okt 2026 (batch 3):** temuan SPK-003, SWAP-001, MAP-001 terverifikasi; MAP-002 diperbaiki; BUILD-001 dikerjakan (commit `6b224068`).
- **Bahasa:** Indonesia.

> Dokumen ini adalah acuan utama audit. Perbarui ID temuan yang sama ketika memperbaiki masalah; jangan membuat temuan duplikat. Jangan tandai temuan sebagai selesai sebelum ada perubahan kode dan bukti verifikasi.

## 1. Ringkasan eksekutif

Area dengan risiko paling tinggi adalah:
1. ketepatan kandidat SPK terhadap kebijakan pengurangan jarak dan kebutuhan formasi;
2. konsistensi kuota saat rekomendasi disetujui;
3. transaksi dan perlindungan terhadap persetujuan bersamaan;
4. pembatasan akses berdasarkan peran dan cakupan data;
5. koordinat sekolah yang disimpan pada lebih dari satu tempat;
6. kesiapan instalasi, kredensial demo, dan tes regresi.

Audit ini menggabungkan temuan dari file yang telah ditinjau dengan risiko yang perlu diverifikasi. Ini bukan pernyataan bahwa setiap file telah diperiksa baris demi baris atau aplikasi telah dijalankan.

## 2. Prioritas

- **P0 — sebelum hasil SPK digunakan untuk keputusan nyata:** validasi kandidat, kebutuhan/kuota, dan kondisi persetujuan yang dapat menyebabkan penempatan tidak sah.
- **P1 — integritas dan keamanan:** transaksi atomik, otorisasi per aksi, konsistensi koordinat, perlindungan konflik bersamaan, serta kredensial.
- **P2 — kualitas dan operasional:** tes otomatis, dokumentasi instalasi, penjelasan hasil rekomendasi, audit trail, dan peningkatan akurasi rute.

## 3. Register temuan

Status awal semua temuan di bawah adalah **Perlu verifikasi/perbaikan** kecuali dinyatakan lain. Prioritas mengacu pada dampak potensial, bukan klaim bahwa insiden telah terjadi.

### SPK-001 — Kandidat belum dipastikan mengurangi jarak

- **Prioritas:** P0
- **Area:** `app/Http/Controllers/SpkController.php`, `app/Livewire/PetaRekomendasi.php`
- **Temuan awal:** pemilihan kandidat mempertimbangkan jarak tujuan dari domisili; belum tampak filter seragam yang memastikan jarak tujuan lebih pendek daripada jarak sekolah asal.
- **Risiko:** rekomendasi dapat bertentangan dengan tujuan kebijakan jika tujuannya mengurangi jarak.
- **Saran:** tetapkan aturan kebijakan. Jika pengurangan jarak wajib, hitung `penghematan = jarak_asal - jarak_tujuan` dan singkirkan kandidat dengan penghematan `<= 0`. Jangan menerapkan aturan ini tanpa konfirmasi bila kebijakan resmi memiliki kriteria lain.
- **Verifikasi:** uji kandidat lebih dekat, sama jauh, dan lebih jauh; pastikan ambang 15 km ditangani tepat sesuai definisi.

- **Status saat ini (11 Okt 2026):** Terverifikasi
- **Commit:** `cb4aa43c`
- **Bukti:** `SpkController@generate`: filter penghematan positif (`$hemat <= 0` gugur), urut by penghematan terbesar, simpan `hemat_km` + `sekolah_asal_id` (migrasi `2026_10_11_000003`).
- **Tes:** HTTP test 11 Okt: POST /spk/generate menghasilkan 21 rekomendasi; query DB: 21/21 `hemat_km > 0`, 0 null `sekolah_asal_id`; 3 teratas terurut 55.94 > 55.60 > 55.48 km.
- **Catatan:** Aturan 'penghematan wajib positif' mengikuti dokumen rekomendasi; perlu konfirmasi Dinas bila kebijakan resmi berbeda.

### SPK-002 — Makna dan pemakaian kuota belum konsisten sampai persetujuan

- **Prioritas:** P0
- **Area:** `SpkController`, `UsulanController`, model/migrasi kebutuhan guru dan alur swap
- **Temuan awal:** kandidat dicari melalui kebutuhan mata pelajaran dengan `jumlah > 0`; belum terlihat mekanisme lengkap yang mengunci serta memperbarui kuota secara atomik pada semua jalur persetujuan.
- **Risiko:** dua guru dapat menempati satu posisi atau kuota menjadi tidak akurat.
- **Saran:** definisikan apakah `kebutuhan_guru.jumlah` berarti kebutuhan awal, posisi tersedia, atau sisa kebutuhan. Lalu terapkan satu aturan konsisten untuk reservasi, persetujuan, penolakan, dan pembatalan.
- **Verifikasi:** dua guru bersamaan untuk satu posisi, pembatalan, penolakan, dan persetujuan berulang.

- **Status saat ini (11 Okt 2026):** Terverifikasi
- **Commit:** `cb4aa43c`
- **Bukti:** `KebutuhanGuru::sisaDenganAlokasi()` (kebutuhan − rekomendasi aktif) dipakai di generate; `KebutuhanGuru::kurangi()` dipanggil di `SpkController@setujui`; validasi `sisa >= 1` dengan lock di dalam transaction.
- **Tes:** HTTP test 11 Okt: kebutuhan (sekolah 76, Bahasa Inggris) 1 → 0 setelah persetujuan; tidak ada rekomendasi ganda untuk formasi yang sama pada 21 hasil generate.
- **Catatan:** Definisi `jumlah` = sisa kebutuhan berjalan; berkurang tiap penempatan disetujui, tidak pulih saat rekomendasi ditolak (ditolak tidak memakai kuota).

### SPK-003 — Pemeringkatan belum menjelaskan seluruh alasan

- **Prioritas:** P1
- **Area:** SPK dan peta rekomendasi
- **Temuan awal:** daftar dibatasi pada sejumlah kandidat teratas dan diurutkan menurut jarak tujuan; alasan administrasi dan penghematan tidak seluruhnya tampak sebagai hasil yang dijelaskan kepada pengguna.
- **Saran:** tampilkan jarak asal, jarak tujuan, penghematan, kecocokan mata pelajaran, kuota, serta alasan kandidat lolos/gagal.
- **Verifikasi:** data penjelasan sesuai hasil hitung dan aturan yang diterapkan.

- **Status saat ini (11 Okt 2026, batch 3):** Terverifikasi
- **Commit:** `6b224068`
- **Bukti:** View `spk/index` kini punya kolom 'Kelayakan' per rekomendasi: mapel cocok, sisa formasi live (via `KebutuhanGuru::sisaDenganAlokasi()`), dan alasan (penghematan + ambang). Controller mengirim `$sisaKuota` tanpa N+1.
- **Tes:** PHPUnit `test_spk_index_menampilkan_penjelasan_kelayakan`: GET /spk memuat 'Kelayakan', 'Sisa formasi', '(garis lurus)'.
- **Catatan:** Alasan kandidat yang GUGUR (tidak tampil) belum dicatat — hanya kandidat lolos yang dijelaskan.

### SEC-001 — Scope data peta perlu diaudit untuk setiap peran

- **Prioritas:** P1
- **Area:** `app/Livewire/PetaRekomendasi.php`
- **Temuan awal:** pembatasan untuk peran guru terlihat pada sebagian jalur; scope pilihan/pin guru untuk peran lain perlu dipastikan konsisten dengan kewenangan.
- **Risiko:** pengguna dapat melihat lokasi atau data guru di luar lingkup tugasnya.
- **Saran:** lakukan scope query di server untuk setiap aksi dan hasil data; jangan hanya menyembunyikan pilihan pada antarmuka. Terapkan kebijakan least privilege pada koordinat domisili.
- **Verifikasi:** uji semua peran, akses ID guru milik pihak lain, serta endpoint/action secara langsung.

- **Status saat ini (11 Okt 2026, batch 2):** Terverifikasi
- **Commit:** `e63b2b7e`
- **Bukti:** `PetaRekomendasi::scopeGuru()` menerapkan scope peran (guru→diri sendiri, kepala_sekolah→sekolahnya, ketua_pgri→kecamatan) pada marker peta, dropdown, dan `cari()` (abort 403 bila guru di luar scope).
- **Tes:** PHPUnit `test_kepala_sekolah_tidak_bisa_intip_guru_sekolah_lain_di_peta`: cari() guru sekolah lain → hasil & infoGuru kosong (tidak bocor); kontrol positif guru dalam scope → data tampil.
- **Catatan:** Endpoint Livewire lain (ajukanPenempatan) sudah punya cek scope sendiri sebelumnya.

### SEC-002 — Perubahan data guru harus dibatasi per kolom

- **Prioritas:** P1
- **Area:** `GuruController` dan form Livewire terkait
- **Temuan awal:** kepemilikan data perlu dibedakan dari hak untuk mengubah setiap atribut.
- **Saran:** gunakan validasi dan daftar kolom yang boleh diubah untuk tiap peran. Kolom penempatan, sekolah, status, atau atribut administratif tidak boleh dapat diubah hanya karena record dimiliki pengguna.
- **Verifikasi:** permintaan manipulasi kolom melalui request langsung ditolak.

- **Status saat ini (11 Okt 2026, batch 2):** Terverifikasi
- **Commit:** `e63b2b7e`
- **Bukti:** `GuruController@update`: filter kolom per peran via `array_intersect_key` — role guru hanya data pribadi (nama, domisili, kecamatan, koordinat), kepala_sekolah semua kecuali NIP, kabid semua.
- **Tes:** PHPUnit `test_guru_role_tidak_bisa_ubah_nip_lewat_request_langsung`: PUT dengan nip diubah → NIP di DB tetap.
- **Catatan:** Form Blade untuk role guru sebaiknya juga menyembunyikan field administratif (defense in depth) — belum dikerjakan.

### FLOW-001 — Persetujuan usulan tidak seluruhnya atomik

- **Prioritas:** P0/P1
- **Area:** `app/Http/Controllers/UsulanController.php`, `SpkController.php`, `SwapController.php`
- **Temuan awal:** pada alur usulan, perubahan status dan mutasi guru terlihat tidak seluruhnya dibungkus dalam satu transaksi. Pada alur lain, pemeriksaan status di luar transaksi menimbulkan risiko race condition.
- **Saran:** gunakan `DB::transaction()`, kunci record terkait dengan `lockForUpdate()`, dan validasi ulang status, sekolah asal, kuota, kewenangan, serta kelayakan setelah lock diperoleh. Perbarui status, penempatan, kuota, dan riwayat dalam transaksi yang sama.
- **Verifikasi:** simulasi dua persetujuan bersamaan dan kegagalan di tengah proses.

- **Status saat ini (11 Okt 2026, batch 2):** Terverifikasi
- **Commit:** `e63b2b7e`
- **Bukti:** `UsulanController@verifikasi/setujui/tolak` kini: `lockForUpdate()` + validasi ulang status di dalam `DB::transaction()`; mutasi guru atomik dengan perubahan status; error → flash `gagal`. (SPK & swap sudah di batch 1.)
- **Tes:** PHPUnit: `test_usulan_setujui_harus_lewat_verifikasi_dulu` (setujui tanpa verifikasi ditolak; alur benar → guru termutasi); uji HTTP manual batch 1 untuk SPK/swap.
- **Catatan:** Uji konkurensi persis-bersamaan (dua request dalam milidetik yang sama) belum dilakukan.

### FLOW-002 — Usulan aktif ganda dapat terkena race condition

- **Prioritas:** P1
- **Area:** `UsulanController` dan alur pengajuan penempatan
- **Temuan awal:** pola periksa-lalu-buat tidak dengan sendirinya mencegah dua request bersamaan.
- **Saran:** tambahkan constraint database yang sesuai dengan aturan bisnis atau strategi locking/serialisasi; tangani pelanggaran constraint dengan pesan yang jelas.
- **Verifikasi:** dua pengajuan bersamaan untuk kombinasi guru/tujuan yang sama.

- **Status saat ini (11 Okt 2026, batch 2):** Terverifikasi
- **Commit:** `e63b2b7e`
- **Bukti:** `UsulanController@store`: `Guru::lockForUpdate()` di dalam transaction sebelum cek usulan aktif; pelanggaran → `ValidationException` pada field `guru_id`.
- **Tes:** PHPUnit `test_usulan_aktif_ganda_dicegah`: usulan kedua untuk guru yang sama → error validasi `guru_id`, jumlah usulan tetap 1.
- **Catatan:** Perlindungan di level aplikasi (lock), bukan constraint DB — cukup untuk skala ini.

### MAP-001 — Sumber koordinat sekolah ganda

- **Prioritas:** P1
- **Area:** model `Guru`, model `Sekolah`, peta, dan seluruh alur mutasi
- **Temuan awal:** guru menyimpan `sekolah_id` sekaligus kolom denormalisasi seperti `sekolah_saat_ini_lat/lng`. Data ganda dapat tidak sinkron.
- **Saran:** jadikan koordinat sekolah pada model `Sekolah` sebagai sumber utama bila sesuai dengan desain bisnis. Migrasikan data dengan aman dan hentikan ketergantungan pada salinan setelah semua penggunaan diverifikasi.
- **Verifikasi:** perubahan koordinat sekolah dan mutasi guru menghasilkan peta serta jarak yang konsisten.

- **Status saat ini (11 Okt 2026, batch 3):** Terverifikasi
- **Commit:** `6b224068`
- **Bukti:** `Guru::koordinatSekolah()` / `jarakKeSekolahSaatIni()`: relasi `sekolah` sebagai sumber kebenaran tunggal, fallback ke cache denormalisasi. Dipakai di SPK generate, swap generate, dashboard, dan PetaRekomendasi. Aturan bisnis 6 di `proses-bisnis.md` diperbarui.
- **Tes:** PHPUnit `test_jarak_ke_sekolah_memakai_relasi_bukan_cache`: cache diset menyimpang → jarak tetap dihitung dari relasi.
- **Catatan:** Kolom denormalisasi tetap ditulis saat mutasi (cache tampilan) — belum dihapus dari skema.

### MAP-002 — Jarak Haversine bukan jarak jalan

- **Prioritas:** P1/P2
- **Area:** query MySQL dan `Guru::jarakKe()`
- **Temuan awal:** README menyebut Haversine; ini menghitung jarak garis lurus.
- **Saran:** jelaskan jenis jarak di antarmuka. Gunakan rute jalan/waktu tempuh hanya bila diperlukan dan tersedia sumber data yang sesuai. Jangan mencampur nilai garis lurus dan jarak jalan dalam pemeringkatan yang sama tanpa definisi.
- **Verifikasi:** uji koordinat kosong/tidak valid dan periksa unit serta pembulatan.

- **Status saat ini (11 Okt 2026, batch 3):** Diperbaiki
- **Commit:** `6b224068`
- **Bukti:** Label '(garis lurus)' ditambahkan di view SPK, swap, dan dokumentasi proses bisnis sudah menyatakan Haversine = garis lurus.
- **Tes:** PHPUnit (assertSee '(garis lurus)' di halaman SPK).
- **Catatan:** Integrasi jarak jalan (OSRM/dll) belum dilakukan — sesuai saran audit, hanya bila diperlukan.

### SWAP-001 — Kelayakan dan konflik pertukaran harus diverifikasi menyeluruh

- **Prioritas:** P1
- **Area:** `SwapController`, `Guru::jarakKe()`, rekomendasi swap
- **Temuan awal:** pertukaran memerlukan validasi untuk dua guru dan dua sekolah, bukan hanya keuntungan jarak.
- **Saran:** validasi status, mapel/kualifikasi, syarat administratif, kedua kuota, konflik usulan aktif, dan konsistensi kedua perubahan dalam satu transaksi. Pertimbangkan maximum-weight matching hanya jika sistem harus mengoptimalkan banyak pasangan sekaligus.
- **Verifikasi:** salah satu pihak tidak memenuhi syarat, guru terlibat di dua swap, kuota berubah, dan persetujuan bersamaan.

- **Status saat ini (11 Okt 2026, batch 3):** Terverifikasi
- **Commit:** `6b224068`
- **Bukti:** `SwapController@generate` ditulis ulang dua tahap: (1) kumpulkan semua pasangan win-win valid + bobot (total hemat), (2) greedy maximum-weight matching (urut bobot desc, ambil yang gurunya bebas). Validasi administratif baru: jenjang harus sama + tidak ada pihak berusulan aktif.
- **Tes:** PHPUnit: `test_swap_memilih_pasangan_dengan_total_hemat_terbesar` (3 guru, pasangan terbaik terpilih) dan `test_swap_menolak_beda_jenjang_dan_usulan_aktif`.
- **Catatan:** Greedy bukan Blossom optimal — didokumentasikan di komentar kode; cukup untuk skala ini.

### DATA-001 — Validasi data geografis dan integritas relasi

- **Prioritas:** P1
- **Area:** model, form, import, migrasi
- **Saran:** validasi latitude `-90..90`, longitude `-180..180`, data kosong, foreign key, indeks, relasi yatim, dan konsistensi `sekolah_id` dengan sekolah aktif. Periksa jalur import Excel/CSV juga.
- **Verifikasi:** data invalid tidak menghasilkan rekomendasi yang tampak valid.

- **Status saat ini (11 Okt 2026, batch 2):** Terverifikasi
- **Commit:** `e63b2b7e`
- **Bukti:** Validasi import Excel sudah paritas dengan form: NIP unik (DB + dalam berkas), jenjang SD/SMP/SMA, kecamatan valid, NPSN terdaftar, lat/lng dalam rentang; FK terpasang di semua relasi (terverifikasi via information_schema).
- **Tes:** Review kode `validasiBarisImport()` + cek FK di DB.
- **Catatan:** Belum: audit relasi yatim pada data historis.

### DOC-001 — Kredensial demo tercantum di README

- **Prioritas:** P0 sebelum deployment publik
- **Area:** `README.md`, seeder, dan data contoh
- **Temuan awal:** README mencantumkan email dan password contoh termasuk akun dengan hak administratif.
- **Saran:** jangan gunakan kredensial contoh pada produksi. Hilangkan password siap pakai dari dokumentasi publik atau jelaskan proses pembuatan kredensial lokal yang aman; pastikan seeder produksi tidak membuat akun dengan password yang diketahui publik. Jika pernah dipakai pada deployment, rotasi kredensial.
- **Verifikasi:** cari semua kredensial hard-coded pada README, seeder, SQL, dan konfigurasi.

- **Status saat ini (11 Okt 2026, batch 2):** Diperbaiki
- **Commit:** `e63b2b7e`
- **Bukti:** README: kolom password dihapus dari tabel akun contoh; diganti rujukan ke `AdminSeeder.php` + peringatan keras jangan dipakai di production.
- **Tes:** Review README.
- **Catatan:** Seeder masih membuat akun dengan password berpola — wajib diganti/dihapus sebelum deployment publik (sudah dicatat di `docs/progress.md` Prioritas 2).

### BUILD-001 — Reproduksibilitas instalasi perlu dikonfirmasi

- **Prioritas:** P1/P2
- **Area:** README, struktur source, manifest dependency, migrasi, SQL
- **Temuan awal:** README menjelaskan menyalin paket source ke proyek Laravel baru dan memasang dependensi; repositori ini perlu dinilai sebagai paket source atau proyek Laravel mandiri secara eksplisit.
- **Saran:** dokumentasikan struktur yang benar, versi runtime, dependency yang diperlukan, cara setup database, dan cara menjalankan tes. Jangan menyatakan `composer.json` hilang sebelum struktur terbaru diperiksa lagi.
- **Verifikasi:** ikuti instruksi dari checkout baru pada lingkungan bersih.

- **Status saat ini (11 Okt 2026, batch 3):** Dikerjakan
- **Commit:** `6b224068`
- **Bukti:** README: blok 'Struktur paket' menjelaskan eksplisit repo = source overlay (tanpa vendor/composer.json), versi terverifikasi (PHP 8.3.6, MySQL 8.0.46, Laravel 13.35, Livewire 4.4), dan Langkah 9: cara menjalankan tes (`peta_asn_test`).
- **Tes:** Review README; `php artisan test` hijau di lingkungan ini.
- **Catatan:** Instalasi bersih penuh (create-project dari nol) belum diuji ulang di mesin bersih.

### TEST-001 — Tes penerimaan dan regresi belum terverifikasi

- **Prioritas:** P1/P2
- **Area:** tes Laravel untuk SPK, usulan, swap, otorisasi, dan geografi
- **Temuan awal:** status pelaksanaan tes belum dikonfirmasi dalam audit ini.
- **Saran:** buat tes otomatis dari daftar pada bagian 5 dan jalankan seluruh suite sebelum menutup temuan.
- **Verifikasi:** simpan perintah tes, hasil, dan commit yang diuji.

- **Status saat ini (11 Okt 2026, batch 2):** Dikerjakan
- **Commit:** `e63b2b7e`
- **Bukti:** `tests/Feature/KeamananPersetujuanTest.php`: 7 tes (SPK hemat positif, setujui ganda, stale, usulan ganda, alur verifikasi, SEC-001, SEC-002) + perbaikan ExampleTest. `tests/TestCase.php` memakai DB MySQL `peta_asn_test` (fungsi Haversine butuh MySQL).
- **Tes:** `php artisan test`: 9 passed, 32 assertions.
- **Catatan:** Belum: cakupan modul lain (sekolah, swap generate, dashboard), uji konkurensi sesungguhnya, CI otomatis.

## 4. Aturan bisnis SPK yang perlu disepakati

Sebelum implementasi, pastikan pemilik kebijakan menyetujui:
1. Apakah 15 km adalah jarak lurus, jarak jalan, atau ambang administratif.
2. Apakah kandidat wajib mengurangi jarak; apakah penghematan nol diperbolehkan.
3. Apakah prioritas berdasarkan penghematan terbesar, jarak tujuan terdekat, kebutuhan sekolah, atau gabungan berbobot.
4. Definisi kuota awal, tersedia, dipesan, terisi, dan kapan kuota dipulihkan.
5. Syarat administratif yang melampaui kecocokan mata pelajaran.
6. Aturan ketika data koordinat atau kebutuhan tidak lengkap.
7. Siapa yang dapat melihat koordinat domisili presisi dan riwayat penempatan.

Contoh perhitungan: jika jarak asal 24 km, kandidat 8 km menghemat 16 km; kandidat 18 km menghemat 6 km; kandidat 30 km menambah jarak 6 km dan harus ditolak jika pengurangan jarak wajib. Ini contoh ilustratif, bukan data aktual sistem.

## 5. Tes penerimaan wajib

- [x] Kandidat tujuan lebih jauh dari asal ditolak jika aturan mensyaratkan pengurangan jarak. (11 Okt: 21/21 rekomendasi `hemat_km > 0`)
- [ ] Kandidat dengan jarak sama diperlakukan sesuai kebijakan.
- [ ] Ambang 15 km diuji di bawah, tepat pada, dan di atas batas.
- [ ] Sekolah tanpa kuota tidak dapat disetujui.
- [x] Dua guru untuk satu posisi tidak menimbulkan kelebihan kuota. (11 Okt, PHPUnit: kebutuhan 1→0 setelah disetujui; generate menghormati sisaDenganAlokasi)
- [x] Dua persetujuan bersamaan tidak memindahkan guru/kuota secara ganda. (11 Okt: persetujuan ganda berurutan ditolak; uji konkurensi persis-bersamaan belum dilakukan)
- [x] Usulan lama tidak dapat disetujui setelah kondisi guru berubah. (11 Okt: guru dipindah manual → persetujuan ditolak, status tetap)
- [ ] Koordinat kosong dan koordinat di luar rentang ditangani dengan aman.
- [ ] Pengguna tanpa kewenangan tidak dapat menyetujui melalui request langsung.
- [x] Guru tidak dapat mengubah kolom administratif yang bukan kewenangannya. (11 Okt, PHPUnit: NIP tidak berubah via PUT langsung)
- [x] Guru yang sama tidak masuk ke dua penempatan/swap yang bertentangan. (11 Okt: rekomendasi lain untuk guru yang sama otomatis ditolak saat satu disetujui; generate swap menolak guru berrekomendasi aktif)
- [x] Kegagalan di tengah transaksi membatalkan semua perubahan terkait. (11 Okt: penolakan setujui ganda/stale → status & data guru tidak berubah)
- [x] Penghematan jarak dihitung sebagai jarak asal dikurangi jarak tujuan. (11 Okt: kolom `hemat_km` tersimpan per rekomendasi)
- [x] Mutasi memperbarui relasi sekolah dan data turunan secara konsisten. (11 Okt: `sekolah_id` + kolom denormalisasi terupdate dalam satu transaction)
- [ ] Import data tidak melewati validasi yang berlaku pada form.
- [x] Dashboard, peta, pencarian, dan ekspor menghormati scope setiap peran. (11 Okt, PHPUnit: peta Livewire menolak guru di luar scope kepala sekolah)

## 6. Urutan pelaksanaan

### Tahap 1 — Baseline
- Inventarisasi semua route, controller, model, Livewire, view, migration, seeder, import/export, dan tes.
- Catat commit baseline serta referensi file/metode untuk setiap ID.
- Konfirmasi struktur proyek dan instruksi setup.

### Tahap 2 — Kebijakan dan algoritma
- Putuskan aturan jarak, ambang, kelayakan, pemeringkatan, dan kuota.
- Terapkan filter kandidat dan penjelasan hasil.
- Tambahkan tes unit/feature untuk rumus dan batas.

### Tahap 3 — Integritas transaksi
- Satukan perubahan status, penempatan, kuota, dan riwayat dalam transaksi.
- Terapkan locking dan validasi ulang setelah lock.
- Lindungi pengajuan duplikat dan konflik swap.

### Tahap 4 — Akses dan data
- Audit semua route/action dan scope query.
- Batasi kolom yang boleh diedit per peran.
- Satukan sumber koordinat dan validasi import.

### Tahap 5 — Verifikasi dan kesiapan
- Jalankan tes regresi, migrasi dari database kosong, dan setup dari checkout baru.
- Hilangkan kredensial default yang tidak aman.
- Catat hasil tes dan tandai ID hanya setelah bukti tersedia.

## 7. Format pembaruan status

Untuk setiap perubahan, perbarui tabel/entri dengan:
- **Status:** Terbuka / Dikerjakan / Diperbaiki / Terverifikasi / Ditutup.
- **Commit:** SHA atau tautan commit.
- **Bukti:** file/fungsi yang berubah.
- **Tes:** perintah yang dijalankan dan hasil.
- **Catatan:** risiko yang masih tersisa.

Temuan tidak dianggap selesai hanya karena kode diubah. Status **Terverifikasi** memerlukan bukti tes atau pemeriksaan yang relevan.

## 8. Batasan audit ini

- Ini merupakan baseline statis dari kode dan dokumentasi yang telah ditinjau, bukan audit keamanan formal.
- Tidak ada klaim bahwa aplikasi, migrasi, atau suite tes telah dijalankan.
- Beberapa temuan ditandai sebagai risiko yang perlu diverifikasi; jangan memperlakukannya sebagai bug terkonfirmasi sebelum reproduksi.
- Perubahan algoritma harus mengikuti kebijakan resmi Dinas Pendidikan, bukan asumsi teknis semata.
