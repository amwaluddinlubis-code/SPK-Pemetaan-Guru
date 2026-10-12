# Log Audit — PetaPenempatan ASN

Catatan audit statis dan temuan keamanan per tanggal. Setiap entri bersifat
append-only: temuan baru ditambahkan di bawah, entri lama tidak diubah.

---

## Audit fitur Mesin Pemerataan Guru — 11 Oktober 2026

Audit statis terhadap implementasi fitur tambahan menemukan dua isu yang diperbaiki:

- **AUDIT-PII-001 — NIP masuk ke log audit:** aksi tambah guru sebelumnya memasukkan NIP ke kolom perubahan yang dapat dilihat melalui halaman aktivitas. Pemanggil kini mencatat nama kolom saja, dan model audit meredaksi kunci sensitif secara rekursif. Ditambahkan tes regresi.
- **SIM-PRIV-001 — Identitas guru pada hasil simulasi:** keluaran simulasi sebelumnya mengandung nama dan ID guru yang tidak diperlukan untuk perbandingan skenario. Hasil sekarang menggunakan label kandidat anonim. Validasi simulasi juga melewati koordinat yang hilang/tidak valid.
- **SIM-LOGIC-001 — Pemilihan sekolah simulasi bergantung urutan database:** algoritme kini mengevaluasi semua tujuan yang layak dan memilih penghematan jarak terbesar, lalu mengurangi kapasitas slot yang dipilih saja. Tes regresi membuat tujuan yang lebih jauh muncul lebih awal untuk memastikan hasil tidak bergantung urutan.
- **DIST-001 — Guru proses mutasi terkelompok berdasarkan kecamatan domisili:** agregat kini mengatribusikan guru yang sedang diproses ke kecamatan sekolah penugasan asal, sesuai definisi kebutuhan formasi. Tes regresi mencakup domisili dan sekolah pada kecamatan berbeda.

Perbaikan tersebut sudah dikomit pada branch `main`. **CI untuk commit perbaikan wajib diverifikasi sebelum menyatakan tes lulus.** Audit ini bersifat statis sampai ada hasil CI yang terikat ke SHA terkait.

## Audit commit fitur dasar — 11 Oktober 2026

Audit statis pada commit fitur dasar terbaru menemukan dua isu yang terbukti:

- **SEC-004 — Enumerasi akun via lupa password:** endpoint sebelumnya membedakan email terdaftar dan tidak terdaftar. Respons kini generik untuk mengurangi enumerasi; pengiriman tautan tetap bergantung pada status broker email.
- **SEC-005 — Detail exception import bocor:** import sekolah sebelumnya menampilkan pesan exception mentah. Detail kini dilaporkan ke log server, sedangkan pengguna menerima pesan generik.

Audit cakupan peran menemukan temuan tambahan yang terbukti: route daftar `/guru`, `/sekolah`, dan `/usulan` sebelumnya hanya mensyaratkan login, padahal kebijakan dokumentasi membatasi Pemda ke dashboard ringkasan. Route daftar tersebut dan detail `/usulan/{id}` kini memakai middleware peran operasional, tidak termasuk `pemda`. Regression test Pemda diperluas ke daftar SPK, swap, guru, sekolah, dan usulan.

Regression test juga ditambah/diperbarui untuk respons reset password generik dan berkas import rusak. Workflow PHPUnit dikonfigurasi untuk berjalan pada push ke `main`, selain pull request dan pemicu manual. **Hasil tes untuk perubahan ini belum boleh dinyatakan lulus sebelum run CI terkait selesai dan diperiksa.** Angka tes/assertion terdahulu bukan bukti untuk commit ini.

## Audit mendalam putaran 2 — 11 Oktober 2026

Audit ulang atas `main` (HEAD `9a8f071`) menemukan dan memperbaiki:

- **AUTH-001 — Throttle pada reset password final:** `POST /reset-password`
  belum punya throttle (hanya `POST /lupa-password` yang punya). Ditambah
  `throttle:10,1`. Ditambah test: token tidak bisa dipakai ulang.
- **AUTH-002 — IDOR lintas peran:** 9 test negatif baru
  (`OtorisasiIdorTest`): guru tak bisa ubah guru lain, kepsek tak bisa
  ubah guru/sekolah lain, ketua PGRI tak bisa buat guru, guru tak bisa
  export, pemda tak bisa edit guru/pengguna langsung, guru tak bisa lihat
  usulan orang lain / verifikasi usulan. Semua ditolak 403 oleh
  `otorisasiAkses`/`otorisasiUbah`/`otorisasiLihat` + middleware `role`.
  Kadis dipastikan tetap bisa membuka daftar guru/sekolah.
- **IMP-001 — Jalur gagal import:** test baru untuk berkas >5MB
  (ditolak validasi) dan NPSN duplikat dalam satu file (baris duplikat
  dilaporkan, baris valid tetap tersimpan utuh — bukan data parsial
  setengah jadi; exception tak terduga memicu rollback penuh + `report()`).
- **Hasil: 71 passed, 231 assertions.** CI perlu diverifikasi ulang pada
  commit push putaran ini.

## Ringkasan Audit Statis — 11 Oktober 2026

Pemeriksaan dokumentasi dan kode di branch `main` menemukan isu berikut.
Ini adalah temuan statis; tingkat risiko dan status perbaikannya harus
diperbarui setelah perubahan kode serta pengujian runtime.

| ID | Temuan | Prioritas | Status 11 Okt 2026 (audit lanjutan) |
|---|---|---|---|
| SEC-003 | Query daftar SPK dan swap perlu filter cakupan sesuai peran | P0 | **Selesai & diperketat** — `terapkanScopePeran()` + 4 tes isolasi; pemda dibatasi ke dashboard ringkasan (middleware + menu) |
| FLOW-001 | Aksi penolakan/transisi status perlu transaksi, penguncian, validasi ulang | P0 | **Selesai** — tolak SPK/swap pakai `lockForUpdate` dalam transaction; error teknis tidak membocorkan SQL |
| FLOW-002 | Generate SPK/swap perlu aman terhadap proses bersamaan | P0 | **Diperkuat** — baca+kunci+hitung ulang kapasitas di dalam SATU transaksi, urutan kunci konsisten, retry deadlock 3x; unique constraint dievaluasi & ditolak (melanggar aturan bisnis) |
| DATA-001 | Koordinat estimasi; data contoh belum layak untuk keputusan operasional | P0 sebelum penggunaan riil | **Operasional** — verifikasi lapangan + ganti data demo saat deployment |
| GOV-001 | Bobot keluarga 1,5x perlu dasar kebijakan tertulis | P1 | Belum — perlu keputusan tertulis Dinas |
| QA-001 | Bukti tes untuk commit rilis | P0 sebelum rilis | **Diverifikasi** — 71 passed, 231 assertions; [GitHub Actions run #26](https://github.com/amwaluddinlubis-code/SPK-Pemetaan-Guru/actions/runs/38085288923) sukses pada commit `935bc2a`. Setiap commit berikutnya tetap perlu diverifikasi. |

### Batas pemeriksaan

Audit ini membandingkan dokumentasi dengan file kode yang tersedia di
GitHub. Aplikasi tidak dijalankan dalam pemeriksaan ini; tidak dilakukan
pengujian penetrasi, pengujian runtime, atau uji konkurensi langsung.
Tidak ada klaim bahwa semua temuan sudah diperbaiki hanya karena tercantum
dalam dokumentasi. Tandai temuan selesai hanya setelah perubahan kode,
tes yang relevan, dan hasil CI pada commit terkait telah diverifikasi.
