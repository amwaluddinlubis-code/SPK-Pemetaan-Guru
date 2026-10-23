# Mesin Pemerataan Guru — PetaPenempatan ASN

Fitur tambahan berada di menu/route berikut (semua memerlukan login):

| Route | Fungsi | Peran |
|---|---|---|
| `/kualitas-data` | Temuan data tidak lengkap/tidak konsisten; pemeriksaan read-only | Kabid PTK, Kadis |
| `/kualitas-data/export` | Ekspor temuan dengan minimisasi data pribadi | Kabid PTK, Kadis |
| `/distribusi` | Agregat kebutuhan, ketersediaan, defisit/surplus per kecamatan dan mapel | Kabid PTK, Kadis, Pemda |
| `/simulasi` | Simulasi hipotetis tanpa menulis perubahan operasional | Kabid PTK, Kadis |
| `/aksesibilitas` | Statistik jarak Haversine agregat, bukan waktu tempuh jalan | Kabid PTK, Kadis |
| `/proyeksi` | Syarat dan metodologi proyeksi kebutuhan | Kabid PTK, Kadis |
| `/aktivitas` | Jejak audit operasional append-only melalui aplikasi | Kabid PTK |

Kolom Peran adalah **bawaan** Pengaturan Akses (lihat `docs/peran.md`) —
administrator dapat mengubah akses membuka halamannya per peran; ekspor
dan aksi lain tetap dibatasi middleware peran rinci seperti tertulis di
`routes/web.php`.

## Definisi dan batasan

- Defisit = `max(kebutuhan - tersedia, 0)`; surplus = `max(tersedia - kebutuhan, 0)`. Periksa aturan hitung guru yang punya usulan/SPK/swap aktif sebelum memakai angka untuk keputusan.
- Jarak dihitung menggunakan Haversine (garis lurus), bukan jarak jalan atau waktu tempuh. Koordinat yang diperkirakan perlu verifikasi lapangan.
- Simulasi bersifat hipotetis, read-only, dan tidak menerbitkan SK atau memutasi guru. Hasil tidak menampilkan nama/NIP guru; kandidat ditampilkan dengan nomor anonim.
- Halaman proyeksi saat ini menyatakan **data belum cukup** karena belum tersedia deret waktu historis yang diperlukan. Tidak ada angka proyeksi yang dibuat-buat.
- Jejak audit mencatat aksi minimum yang dibutuhkan dan meredaksi kunci sensitif termasuk NIP, alamat, koordinat domisili, password dan token; alamat IP peminta tidak disimpan. Log aplikasi bukan pengganti penyimpanan audit tahan manipulasi atau kontrol akses database.
- Tetap lakukan pemeriksaan kebijakan, kualitas data, scope akses, dan persetujuan pejabat sebelum menggunakan rekomendasi secara operasional.

## Tes dan verifikasi

Workflow `.github/workflows/phpunit.yml` menjalankan `php artisan test` di runtime Laravel dengan MySQL 8. Untuk menyatakan suatu commit lulus, periksa run GitHub Actions yang SHA-nya sama persis dengan commit tersebut. Tidak adanya status check pada endpoint status commit **bukan** bukti lulus atau gagal. Catat jumlah tes dan assertions hanya dari hasil run aktual.
