# Model Data (Ringkas) — PetaPenempatan ASN

| Tabel | Isi |
|---|---|
| users | Akun login + peran |
| guru | Data guru + domisili + koordinat + sekolah saat ini + kepegawaian |
| sekolah | Data sekolah + alamat + koordinat + kategori daerah + rombel & jenjang |
| kebutuhan_guru | Kebutuhan guru per sekolah per mapel (dapat dihitung dari rombel × JP ÷ 24) |
| wilayah_provinsi / wilayah_kabupaten / wilayah_kecamatan / wilayah_desa | Referensi wilayah Kemendagri (kode resmi, ter-seed nasional) |
| pengaturan | Pasangan kunci–nilai pengaturan aplikasi (konteks Pemda) |
| akses_menu | Penyimpangan akses menu per peran dari bawaan registri (Pengaturan Akses) |
| jarak_jalan_cache | Cache jarak jalan (routing) per pasangan koordinat |
| usulan_penempatan | Usulan pindah + status alur |
| hasil_spks | Rekomendasi SPK + status + skor hibrida SAW–TOPSIS (`skor_saw`, `skor_topsis`, `skor`, `detail_skor` JSON; NULL untuk rekomendasi lama) |
| rekomendasi_swaps | Rekomendasi tukar + status |
| activity_logs | Jejak audit operasional (append-only) |

Relasi utama: guru milik satu sekolah (`sekolah_id`); sekolah punya banyak
kebutuhan guru; usulan/SPK/swap merujuk ke guru (dan sekolah tujuan).
