<?php

// Service Simulasi Kebijakan "what-if" (C).
//
// Menjalankan logika pencocokan SPK secara IN-MEMORY pada snapshot data —
// TIDAK MENULIS ke database operasional (tanpa INSERT/UPDATE/DELETE).
// Hasil adalah skenario hipotetis untuk dibandingkan dengan baseline,
// bukan rekomendasi resmi maupun keputusan mutasi.

namespace App\Services;

use App\Models\Guru;
use App\Models\HasilSpk;
use App\Models\KebutuhanGuru;
use App\Models\Sekolah;

class SimulasiKebijakan
{
    /**
     * Jalankan simulasi.
     *
     * @param  array  $param  ['ambang_km' => float, 'mapel' => ?string,
     *                        'kecamatan' => ?string, 'sertakan_3t' => bool]
     * @return array hasil simulasi + perbandingan baseline
     */
    public function jalankan(array $param): array
    {
        $ambang = (float) ($param['ambang_km'] ?? 15);
        $mapel = $param['mapel'] ?? null;
        $kecamatan = $param['kecamatan'] ?? null;
        $sertakan3t = (bool) ($param['sertakan_3t'] ?? false);

        // Snapshot read-only
        $gurus = Guru::with('sekolah')->get();
        $sekolahs = Sekolah::with('kebutuhan')->get()->keyBy('id');

        if ($mapel) {
            $gurus = $gurus->where('mapel', $mapel);
        }
        if ($kecamatan) {
            $gurus = $gurus->where('kecamatan', $kecamatan);
        }

        // Sisa kebutuhan per (sekolah, mapel) dari snapshot
        $sisa = [];
        foreach (KebutuhanGuru::all() as $butuh) {
            $k = $butuh->sekolah_id.'|'.$butuh->mapel;
            $sisa[$k] = ($sisa[$k] ?? 0) + $butuh->jumlah;
        }

        $kandidat = [];
        $totalHemat = 0;

        foreach ($gurus as $guru) {
            if ($guru->domisili_lat === null || $guru->domisili_lng === null
                || ! $this->koordinatValid($guru->domisili_lat, $guru->domisili_lng)) {
                continue;
            }
            $asal = $guru->sekolah;
            if (! $asal) {
                continue;
            }
            // Batasan 3T: guru di sekolah 3T tidak ditarik (kecuali diminta)
            if (! $sertakan3t && in_array($asal->kategori_daerah, ['terpencil', 'sangat_terpencil'], true)) {
                continue;
            }

            $jarakSekarang = $guru->jarakKeSekolahSaatIni();
            if ($jarakSekarang === null || $jarakSekarang <= $ambang) {
                continue;
            }

            $pilihan = null;
            foreach ($sekolahs as $tujuan) {
                if ($tujuan->id === $asal->id) {
                    continue;
                }
                if ($tujuan->lat === null || $tujuan->lng === null
                    || ! $this->koordinatValid($tujuan->lat, $tujuan->lng)) {
                    continue;
                }
                $k = $tujuan->id.'|'.$guru->mapel;
                if (($sisa[$k] ?? 0) <= 0) {
                    continue;
                }

                $jarakTujuan = $guru->jarakKe((float) $tujuan->lat, (float) $tujuan->lng);
                $hemat = $jarakSekarang - $jarakTujuan;
                if ($hemat <= 0) {
                    continue;
                }

                // Pilih penghematan terbesar; jangan bergantung pada urutan database.
                if ($pilihan === null || $hemat > $pilihan['hemat_raw']) {
                    $pilihan = [
                        'sekolah_tujuan' => $tujuan->nama,
                        'hemat_raw' => $hemat,
                        'kunci' => $k,
                    ];
                }
            }

            if ($pilihan !== null) {
                $sisa[$pilihan['kunci']]--;
                $totalHemat += $pilihan['hemat_raw'];
                $kandidat[] = [
                    'sekolah_asal' => $asal->nama,
                    'sekolah_tujuan' => $pilihan['sekolah_tujuan'],
                    'hemat_km' => round($pilihan['hemat_raw'], 2),
                ];
            }
        }

        // Baseline: rekomendasi SPK aktif saat ini (agregat saja)
        $baseline = [
            'rekomendasi_aktif' => HasilSpk::where('status', 'rekomendasi')->count(),
        ];

        $sekolahTerdampak = collect($kandidat)->pluck('sekolah_tujuan')->unique()->values()->toArray();

        return [
            'parameter' => [
                'ambang_km' => $ambang,
                'mapel' => $mapel ?: 'semua',
                'kecamatan' => $kecamatan ?: 'semua',
                'sertakan_3t' => $sertakan3t,
            ],
            'jumlah_kandidat' => count($kandidat),
            'total_hemat_km' => round($totalHemat, 2),
            'rata_hemat_km' => count($kandidat) > 0 ? round($totalHemat / count($kandidat), 2) : 0,
            'sekolah_terdampak' => $sekolahTerdampak,
            'kandidat' => array_slice($kandidat, 0, 50), // batasi tampilan
            'baseline' => $baseline,
            'asumsi' => [
                'Jarak Haversine (garis lurus), bukan waktu tempuh.',
                'Satu skenario per guru (tujuan terbaik pertama yang menghemat).',
                'Guru di sekolah 3T dikecualikan kecuali parameter sertakan_3t aktif.',
                'Hasil simulasi TIDAK mengubah data operasional.',
            ],
            'versi_algoritme' => 'simulasi-whatif-v1',
            'waktu' => now()->toDateTimeString(),
        ];
    }

    private function koordinatValid($lat, $lng): bool
    {
        return is_numeric($lat) && is_numeric($lng)
            && (float) $lat >= -90 && (float) $lat <= 90
            && (float) $lng >= -180 && (float) $lng <= 180
            && ! ((float) $lat === 0.0 && (float) $lng === 0.0);
    }
}
