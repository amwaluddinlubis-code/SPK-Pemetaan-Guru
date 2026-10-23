# Metode Peringkat SPK — Greedy + SAW–TOPSIS

Berlaku untuk **SPK Pengisian Formasi** (`POST /spk/generate`) sejak
23 Okt 2026. Mutasi silang (swap) tidak berubah: tetap *greedy
maximum-weight matching* atas total penghematan berbobot keluarga.

## Arsitektur dua lapis

1. **Greedy (kelayakan & alokasi)** — tidak berubah dari aturan lama:
   - Hanya guru berjarak tugas **> 15 km** (ambang SPK) yang diproses.
   - Guru yang bertugas di sekolah asal 3T/terpencil **dilindungi**
     (tidak ditarik keluar).
   - Sekolah tujuan harus satu mapel, memiliki sisa formasi, dan lebih
     dekat dari sekolah saat ini (penghematan > 0).
   - Urutan pemrosesan guru memprioritaskan **guru berkeluarga**
     (bobot keluarga 1,5×) agar tidak kehabisan formasi.
   - Kolam alternatif per guru dibatasi **20 sekolah layak terdekat**;
     maksimal **3 rekomendasi** per guru; setiap rekomendasi yang dibuat
     langsung mengurangi sisa formasi di dalam transaksi yang sama
     (anti over-booking, aman konkurensi).
2. **SAW + TOPSIS (peringkat alternatif)** — di dalam kolam layak milik
   satu guru, setiap sekolah tujuan dinilai dengan empat kriteria:

   | Kriteria | Tipe | Bobot | Nilai |
   |---|---|---|---|
   | Penghematan jarak | benefit | 40% | jarak sekarang − jarak tujuan (km) |
   | Jarak ke sekolah tujuan | cost | 25% | jarak domisili → tujuan (km) |
   | Sisa formasi tujuan | benefit | 25% | sisa slot mapel saat penilaian |
   | Prioritas daerah tujuan | benefit | 10% | biasa = 1, terpencil = 2, sangat terpencil = 3 |

   Status keluarga **bukan** kriteria antar-sekolah (nilainya sama untuk
   semua alternatif milik guru yang sama) — ia bekerja pada lapisan
   greedy sebagai urutan prioritas pemrosesan.

## Rumus

Untuk alternatif *i* dan kriteria *j* dengan bobot *wⱼ*:

- **SAW** — normalisasi benefit `r = x / max(x)`, cost `r = min(x) / x`;
  skor `V_i = Σ wⱼ · r_ij`.
- **TOPSIS** — normalisasi vektor `r = x / √(Σx²)`, matriks terbobot
  `v = wⱼ · r_ij`; solusi ideal positif A+ (maks benefit, min cost) dan
  negatif A− (kebalikannya); jarak Euclidean D+ dan D−;
  skor `C_i = D− / (D+ + D−)`.
- **Skor hibrida** — `H_i = (V_i + C_i) / 2`, rentang 0..1, ditampilkan
  sebagai 0..100. Urutan rekomendasi mengikuti H tertinggi; seri dipecah
  oleh skor SAW, lalu TOPSIS, lalu penghematan terbesar.

Kasus tepi: alternatif tunggal atau semua kriteria identik → skor 1
(tidak ada pembanding yang lebih baik di kolamnya).

## Jejak audit

Setiap rekomendasi baru menyimpan `skor_saw`, `skor_topsis`, `skor`
(hibrida), dan `detail_skor` (JSON: versi metode, nilai kriteria saat
penilaian, bobot, normalisasi SAW, matriks terbobot TOPSIS, jarak ke
solusi ideal, peringkat). Telaahan staf mencetak skor dan snapshot
kriteria ini. Rekomendasi lama (sebelum metode ini) bernilai NULL dan
ditampilkan sebagai "belum tersedia", bukan 0.

## Contoh hitung (dipakai juga sebagai tes unit)

Alternatif: A (hemat 30, jarak 5, sisa 1, daerah 1),
B (hemat 25, jarak 10, sisa 4, daerah 3),
C (hemat 20, jarak 15, sisa 2, daerah 2).

| Alternatif | SAW | TOPSIS | Hibrida | Peringkat |
|---|---|---|---|---|
| A | 0,746 | 0,484 | 0,615 | 2 |
| B | 0,808 | 0,702 | 0,755 | 1 |
| C | 0,542 | 0,236 | 0,389 | 3 |

B menang walau jaraknya kalah dari A, karena sisa formasi dan prioritas
daerah mengunggulinya — inilah beda peringkat multikriteria terhadap
urutan penghematan murni.

## Batasan & catatan kebijakan

- Normalisasi bersifat **relatif terhadap kolam alternatif guru itu
  sendiri**; skor dua guru berbeda tidak dapat dibandingkan secara
  absolut.
- Bobot kriteria dan nilai ordinal daerah adalah **parameter
  kebijakan** (konstanta di `App\Services\MesinSawTopsis`); perubahan
  bobot mengubah peringkat dan sebaiknya ditetapkan tertulis oleh Dinas,
  sebagaimana bobot keluarga (GOV-001).
- Skor adalah bahan pendukung keputusan — keputusan akhir tetap oleh
  pejabat berwenang (aturan bisnis #16).
