# Model Data (Ringkas) — PetaPenempatan ASN

| Tabel | Isi |
|---|---|
| users | Akun login + peran |
| guru | Data guru + domisili + koordinat + sekolah saat ini + kepegawaian |
| sekolah | Data sekolah + alamat + koordinat + kategori daerah |
| kebutuhan_guru | Kebutuhan guru per sekolah per mapel |
| usulan_penempatan | Usulan pindah + status alur |
| hasil_spks | Rekomendasi SPK + status |
| rekomendasi_swaps | Rekomendasi tukar + status |
| activity_logs | Jejak audit operasional (append-only) |

Relasi utama: guru milik satu sekolah (`sekolah_id`); sekolah punya banyak
kebutuhan guru; usulan/SPK/swap merujuk ke guru (dan sekolah tujuan).
