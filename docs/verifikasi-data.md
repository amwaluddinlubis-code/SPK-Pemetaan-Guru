# Prosedur Verifikasi Data Lapangan (DATA-001)

## Latar belakang

Koordinat 552 sekolah di database saat ini adalah **estimasi** dan belum
layak dipakai untuk keputusan resmi penempatan guru.

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
Target: seluruh 552 sekolah terverifikasi sebelum aplikasi dipakai
untuk keputusan mutasi resmi.
