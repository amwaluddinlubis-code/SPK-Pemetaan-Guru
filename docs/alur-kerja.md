# Alur Kerja Utama — PetaPenempatan ASN

## Pendataan Guru dan Sekolah

Data guru: NIP (unik), nama, mapel, jenjang (SD/SMP/SMA), alamat domisili,
desa/kelurahan, kecamatan, koordinat domisili, status pernikahan, status
kepegawaian (PNS/PPPK/Honorer), sertifikasi + mapel sertifikat. Data
sekolah: NPSN (unik), nama, alamat, desa/kelurahan, kecamatan, koordinat,
kategori daerah (Biasa/Terpencil/Sangat Terpencil 3T), plus kebutuhan guru
per mapel. Pemasukan lewat form atau import Excel (Kabid PTK).

**Rombel & kebutuhan otomatis:** rombel tiap sekolah dapat diimpor massal
dari CSV ekspor Dapodik per kecamatan (menu Import Rombel di Data Sekolah,
khusus Kabid PTK; pencocokan via NPSN). Kebutuhan guru lalu dihitung
otomatis: rombel × jam pelajaran per minggu (Permendikdasmen No. 13/2025)
÷ 24, dan dapat diterapkan ke semua sekolah sekaligus lewat tombol
**Terapkan Analisis Massal** (idempoten, tercatat di Jejak Audit).

**Wilayah & konteks:** provinsi/kabupaten/kecamatan/desa mengikuti
referensi Kemendagri (menu Referensi Wilayah). Bila **Pengaturan Pemda**
diatur, seluruh modul hanya menampilkan data wilayah kerja tersebut
(konteks ketat via global scope).

## Usulan Penempatan (bottom-up)

    diajukan → diverifikasi (Kabid PTK) → disetujui (Kadis) → guru otomatis dimutasi
                                          ↘ ditolak

Seorang guru tidak boleh punya 2 usulan aktif bersamaan. Persetujuan memakai
`lockForUpdate` + validasi ulang dalam transaction (cegah ganda & parsial).
Aksi penolakan dan perubahan status lainnya juga harus memakai transisi
status yang konsisten, dengan validasi ulang di dalam transaksi bila ada
perubahan data terkait.

## Mutasi Silang / Swap

Sistem mencari pasangan dua guru yang jika bertukar sekolah, keduanya lebih
dekat ke domisilinya (win-win). Syarat: mapel sama, jenjang sama, tidak ada
pihak yang berusulan aktif, satu guru hanya di satu rekomendasi aktif.
Pasangan dipilih greedy by total penghematan terbesar (bobot keluarga 1,5x).
Persetujuan Kadis mengunci rekomendasi + kedua guru dan validasi ulang posisi.

    menunggu → disetujui → kedua guru otomatis bertukar ↘ ditolak

**Catatan integritas:** proses generate yang berjalan bersamaan harus
mencegah guru yang sama terpilih di lebih dari satu rekomendasi aktif.
Aksi setuju/tolak harus memiliki penguncian dan validasi status yang
konsisten agar tidak terjadi perubahan status yang saling menimpa.

## SPK Pengisian Formasi (top-down)

Untuk guru berjarak > 15 km dari domisili ke sekolah saat ini, sistem mencari
3 sekolah terbaik yang kekurangan formasi mapelnya (Haversine di MySQL).
Aturan kelayakan (greedy): sekolah tujuan harus lebih dekat (penghematan
positif) dan sisa formasi tersedia (tidak over-booking). Alternatif yang
layak kemudian **diperingkat dengan skor hibrida SAW + TOPSIS** —
`hemat_km` + `sekolah_asal_id` + `skor_saw`/`skor_topsis`/`skor` +
`detail_skor` disimpan sebagai bukti (lihat `docs/metode-saw-topsis.md`).

    rekomendasi → disetujui (Kadis) → guru otomatis dimutasi ↘ ditolak

Setiap baris SPK punya tombol **Telaahan** — dokumen siap cetak/PDF untuk
PPK (dasar hukum, data guru, analisis, saran, tanda tangan Kabid PTK → Kadis).

**Catatan integritas:** sisa formasi tidak cukup dihitung sekali sebelum
proses transaksi. Mekanisme generate harus melindungi alokasi dari proses
bersamaan, mengunci atau mengalokasikan slot secara atomik, dan memvalidasi
ulang kapasitas saat rekomendasi disimpan serta saat disetujui.

## Dashboard Eksekutif Pemda

Ringkasan read-only: kartu statistik, distribusi per kecamatan (23
kecamatan), distribusi per jenjang + defisit, rekap status usulan/SPK/swap.
