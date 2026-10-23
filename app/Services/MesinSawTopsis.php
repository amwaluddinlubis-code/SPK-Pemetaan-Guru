<?php

// Mesin peringkat hibrida SAW + TOPSIS untuk rekomendasi SPK.
//
// Lapisan greedy tetap menangani kelayakan dan alokasi formasi (ambang
// jarak, penghematan positif, sisa formasi, dan urutan prioritas guru).
// Mesin ini hanya memeringkat alternatif sekolah yang SUDAH layak bagi
// satu guru, sehingga normalisasi SAW/TOPSIS selalu dihitung di dalam
// kelompok alternatif guru yang sama.
//
// Skor akhir = rata-rata skor SAW dan koefisien kedekatan TOPSIS.
// Keduanya berada pada rentang 0..1 dan memakai bobot kriteria yang sama
// agar hasilnya dapat dibandingkan serta diaudit.

namespace App\Services;

class MesinSawTopsis
{
    /**
     * Definisi kriteria peringkat alternatif sekolah tujuan.
     *
     * - hemat_km (benefit): makin besar penghematan jarak, makin baik.
     * - jarak_tujuan_km (cost): makin kecil jarak penempatan baru, makin baik.
     * - sisa_formasi (benefit): sekolah dengan sisa kebutuhan lebih besar
     *   diprioritaskan untuk diisi.
     * - prioritas_daerah (benefit): nilai ordinal kategori daerah tujuan;
     *   sekolah terpencil mendapat prioritas pemerataan lebih tinggi.
     */
    public const KRITERIA = [
        'hemat_km' => [
            'label' => 'Penghematan jarak',
            'tipe' => 'benefit',
            'bobot' => 0.40,
        ],
        'jarak_tujuan_km' => [
            'label' => 'Jarak ke sekolah tujuan',
            'tipe' => 'cost',
            'bobot' => 0.25,
        ],
        'sisa_formasi' => [
            'label' => 'Sisa formasi tujuan',
            'tipe' => 'benefit',
            'bobot' => 0.25,
        ],
        'prioritas_daerah' => [
            'label' => 'Prioritas daerah tujuan',
            'tipe' => 'benefit',
            'bobot' => 0.10,
        ],
    ];

    /** Nilai ordinal kategori daerah sekolah tujuan. */
    public const NILAI_DAERAH = [
        'biasa' => 1,
        'terpencil' => 2,
        'sangat_terpencil' => 3,
    ];

    /**
     * Faktor penyesuai tingkat kelayakan linieritas (Kepmen 222/O/2025,
     * fase 2) yang dikalikan ke skor hibrida mentah: penempatan mapel
     * persis (tingkat 1) bernilai penuh; penempatan linier kelompok
     * (tingkat 2) tetap layak tetapi didahulukan setelah yang persis
     * pada skor yang sebanding. Bobot kriteria SAW/TOPSIS tidak berubah.
     */
    public const FAKTOR_TINGKAT = [
        1 => 1.0,
        2 => 0.85,
    ];

    public static function nilaiDaerah(?string $kategoriDaerah): int
    {
        return self::NILAI_DAERAH[strtolower(trim((string) $kategoriDaerah))] ?? 1;
    }

    /**
     * Hitung SAW, TOPSIS, dan skor hibrida, lalu urutkan alternatif.
     *
     * Setiap alternatif adalah array yang minimal memuat empat kunci
     * kriteria pada KRITERIA. Kunci lain dipertahankan apa adanya.
     * Hasil menambahkan: skor_saw, skor_topsis, skor_hybrid,
     * peringkat_ke, dan detail_skor (jejak perhitungan yang dapat diaudit).
     *
     * @param  array<int, array<string, mixed>>  $alternatif
     * @return array<int, array<string, mixed>>
     */
    public function peringkat(array $alternatif): array
    {
        if ($alternatif === []) {
            return [];
        }

        $alternatif = array_values($alternatif);
        $nilai = [];
        foreach ($alternatif as $i => $item) {
            foreach (self::KRITERIA as $kunci => $definisi) {
                $nilai[$i][$kunci] = (float) ($item[$kunci] ?? 0);
            }
        }

        $minimum = [];
        $maksimum = [];
        $penyebutTopsis = [];
        foreach (self::KRITERIA as $kunci => $definisi) {
            $kolom = array_column($nilai, $kunci);
            $minimum[$kunci] = min($kolom);
            $maksimum[$kunci] = max($kolom);
            $penyebutTopsis[$kunci] = sqrt(array_sum(array_map(
                fn (float $v) => $v * $v,
                $kolom
            )));
        }

        // 1) SAW: normalisasi benefit x/max dan cost min/x, lalu jumlah
        //    terbobot. 2) TOPSIS: normalisasi vektor, bobot, jarak ke
        //    solusi ideal positif/negatif, lalu koefisien kedekatan.
        $normalisasiSaw = [];
        $matriksTopsis = [];
        foreach ($nilai as $i => $baris) {
            foreach (self::KRITERIA as $kunci => $definisi) {
                $normalisasiSaw[$i][$kunci] = $this->normalisasiSaw(
                    $baris[$kunci],
                    $definisi['tipe'],
                    $minimum[$kunci],
                    $maksimum[$kunci]
                );

                $ternormalisasi = $penyebutTopsis[$kunci] > 0
                    ? $baris[$kunci] / $penyebutTopsis[$kunci]
                    : 0.0;
                $matriksTopsis[$i][$kunci] = $ternormalisasi * $definisi['bobot'];
            }
        }

        $idealPositif = [];
        $idealNegatif = [];
        foreach (self::KRITERIA as $kunci => $definisi) {
            $kolom = array_column($matriksTopsis, $kunci);
            if ($definisi['tipe'] === 'benefit') {
                $idealPositif[$kunci] = max($kolom);
                $idealNegatif[$kunci] = min($kolom);
            } else {
                $idealPositif[$kunci] = min($kolom);
                $idealNegatif[$kunci] = max($kolom);
            }
        }

        foreach ($alternatif as $i => &$item) {
            $skorSaw = 0.0;
            foreach (self::KRITERIA as $kunci => $definisi) {
                $skorSaw += $definisi['bobot'] * $normalisasiSaw[$i][$kunci];
            }

            $jarakPositif = 0.0;
            $jarakNegatif = 0.0;
            foreach (self::KRITERIA as $kunci => $definisi) {
                $jarakPositif += ($matriksTopsis[$i][$kunci] - $idealPositif[$kunci]) ** 2;
                $jarakNegatif += ($matriksTopsis[$i][$kunci] - $idealNegatif[$kunci]) ** 2;
            }
            $jarakPositif = sqrt($jarakPositif);
            $jarakNegatif = sqrt($jarakNegatif);

            // Bila alternatif tepat berada pada kedua solusi ideal
            // (semua kriteria identik), tidak ada jarak yang memisahkan;
            // koefisiennya didefinisikan 1 agar tidak terjadi 0/0.
            $skorTopsis = ($jarakPositif + $jarakNegatif) > 0
                ? $jarakNegatif / ($jarakPositif + $jarakNegatif)
                : 1.0;

            $skorSaw = $this->batasiSkor($skorSaw);
            $skorTopsis = $this->batasiSkor($skorTopsis);
            $skorHybridMentah = $this->batasiSkor(($skorSaw + $skorTopsis) / 2);

            // Penyesuai tingkat kelayakan linieritas (fase 2): alternatif
            // tanpa keterangan tingkat dianggap tingkat 1 (faktor penuh).
            $tingkat = (int) ($item['tingkat_linieritas'] ?? 1);
            $faktorTingkat = self::FAKTOR_TINGKAT[$tingkat] ?? 1.0;
            $skorHybrid = $this->batasiSkor($skorHybridMentah * $faktorTingkat);

            $item['skor_saw'] = $skorSaw;
            $item['skor_topsis'] = $skorTopsis;
            $item['skor_hybrid'] = $skorHybrid;
            $item['detail_skor'] = [
                'versi' => 'greedy-saw-topsis-v1',
                'metode' => 'Skor akhir = (SAW + TOPSIS) / 2 × faktor tingkat kelayakan linieritas',
                'kriteria' => $nilai[$i],
                'bobot' => array_map(
                    fn (array $definisi) => $definisi['bobot'],
                    self::KRITERIA
                ),
                'saw' => [
                    'normalisasi' => $normalisasiSaw[$i],
                    'skor' => $skorSaw,
                ],
                'topsis' => [
                    'matriks_terbobot' => $matriksTopsis[$i],
                    'jarak_ideal_positif' => $jarakPositif,
                    'jarak_ideal_negatif' => $jarakNegatif,
                    'skor' => $skorTopsis,
                ],
                'tingkat_linieritas' => $tingkat,
                'faktor_tingkat' => $faktorTingkat,
                'skor_hybrid_mentah' => $skorHybridMentah,
                'skor_hybrid' => $skorHybrid,
            ];
        }
        unset($item);

        usort($alternatif, function (array $a, array $b) {
            $cmp = $b['skor_hybrid'] <=> $a['skor_hybrid'];
            if ($cmp !== 0) {
                return $cmp;
            }
            $cmp = $b['skor_saw'] <=> $a['skor_saw'];
            if ($cmp !== 0) {
                return $cmp;
            }
            $cmp = $b['skor_topsis'] <=> $a['skor_topsis'];
            if ($cmp !== 0) {
                return $cmp;
            }
            $cmp = ((float) ($b['hemat_km'] ?? 0)) <=> ((float) ($a['hemat_km'] ?? 0));
            if ($cmp !== 0) {
                return $cmp;
            }

            return ((float) ($a['jarak_tujuan_km'] ?? INF)) <=> ((float) ($b['jarak_tujuan_km'] ?? INF));
        });

        foreach ($alternatif as $i => &$item) {
            $item['peringkat_ke'] = $i + 1;
            $item['detail_skor']['peringkat_ke'] = $i + 1;
        }
        unset($item);

        return $alternatif;
    }

    private function normalisasiSaw(float $nilai, string $tipe, float $minimum, float $maksimum): float
    {
        if ($tipe === 'benefit') {
            return $maksimum > 0 ? $nilai / $maksimum : 0.0;
        }

        // Kriteria cost: nilai nol adalah yang terbaik.
        if ($nilai <= 0) {
            return $minimum <= 0 ? 1.0 : 0.0;
        }

        return $minimum > 0 ? $minimum / $nilai : 0.0;
    }

    private function batasiSkor(float $skor): float
    {
        return max(0.0, min(1.0, $skor));
    }
}
