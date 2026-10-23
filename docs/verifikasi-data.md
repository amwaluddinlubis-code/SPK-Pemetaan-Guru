# Prosedur Verifikasi Data Lapangan (DATA-001)

## Latar belakang

Koordinat sekolah-sekolah di database saat ini adalah **estimasi** dan belum
layak dipakai untuk keputusan resmi penempatan guru. (Data awal yang
ter-seed: 478 sekolah negeri se-Kab. Mandailing Natal.)

## Prosedur verifikasi

1. Ekspor daftar sekolah per kecamatan dari aplikasi
   (`/sekolah` → Export Excel).
2. Untuk setiap sekolah, verifikasi di lapangan:
   - [ ] Nama sekolah sesuai papan nama
   - [ ] Alamat/desa/kelurahan benar
   - [ ] Koordinat diambil via GPS di gerbang utama sekolah
         (akurasi < 10 meter)
   - [ ] Kategori daerah (Biasa/Terpencil/Sangat Terpencil) sesuai kondisi
3. Catat hasil pada kolom: `terverifikasi` (ya/tidak), `tanggal_verifikasi`,
   `petugas`, `catatan`.
4. Sekolah yang belum terverifikasi: tandai dan JANGAN jadikan dasar
   rekomendasi SPK/swap sampai terverifikasi.
5. Update koordinat via form edit sekolah atau import Excel koreksi
   (Kabid PTK).

## Prioritas

Verifikasi dulu sekolah di kecamatan dengan defisit guru tertinggi
(lihat `/distribusi`), karena rekomendasi SPK paling berdampak di sana.

## Tanggung jawab

Operator: Dinas Pendidikan Kab. Mandailing Natal (Bidang PTK).
Target: seluruh sekolah di database terverifikasi sebelum aplikasi dipakai
untuk keputusan mutasi resmi.

## Status verifikasi di aplikasi (per 23 Okt 2026)

Prosedur lapangan di atas kini ditegakkan oleh aplikasi:

- Setiap sekolah menyimpan status `koordinat_terverifikasi` beserta
  waktu, petugas, sumber (GPS lapangan / konfirmasi sekolah / peta
  Dapodik / citra satelit / konfirmasi massal), dan catatan.
  Semua koordinat awal (seed/impor) berstatus **belum terverifikasi**.
- Lencana status tampil di halaman Data Sekolah; formulir verifikasi
  ada di halaman Ubah Sekolah.
- Yang berwenang memverifikasi: **Kabid PTK** dan **Kadis** (semua
  sekolah dalam konteks pemda), **Kepala Sekolah** (hanya sekolahnya
  sendiri). Guru tidak dapat memvalidasi.
- Kabid PTK dapat memakai tombol **Verifikasi Koordinat Massal** di
  Data Sekolah setelah titik-titik estimasi dikonfirmasi — gunakan
  hanya bila hasil verifikasi lapangan memang sudah menyimpulkan
  titik yang ada dapat dipakai.
- Mesin SPK (generate) dan Peta Rekomendasi **hanya** menjadikan
  sekolah terverifikasi sebagai tujuan rekomendasi.
- Mengubah titik koordinat sekolah yang sudah terverifikasi akan
  membatalkan statusnya secara otomatis; verifikasi ulang diperlukan.
- Setiap verifikasi (satuan maupun massal) dan pembatalan otomatis
  tercatat di Jejak Audit.
