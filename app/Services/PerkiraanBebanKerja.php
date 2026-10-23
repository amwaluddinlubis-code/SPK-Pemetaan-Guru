<?php

// Perkiraan beban mengajar (JP/minggu) seorang guru BILA sebuah
// rekomendasi SPK disetujui — linieritas fase 3.
//
// Tunjangan Profesi mensyaratkan beban kerja penuh (Permendikdasmen
// 10/2026 Pasal 4), jadi penempatan ke sekolah yang total JP mapelnya
// kecil dapat membuat guru kekurangan jam walau jaraknya membaik.
//
// Rumus perkiraan (kasar, satu angka untuk bahan pertimbangan):
//   total JP mapel di tujuan = jumlah rombel × JP/minggu mapel
//     (Permendikdasmen 13/2025, konstanta AnalisisKebutuhanGuru)
//   perkiraan JP guru = total JP ÷ (guru mapel itu di tujuan + 1)
// Khusus Guru Kelas SD: satu rombel diampu penuh satu guru kelas,
// sehingga perkiraannya adalah beban penuh (24 JP).
// Kembali null bila tidak dapat diperkirakan (rombel belum terisi
// atau JP mapel tidak dikenal).

namespace App\Services;

use App\Models\Guru;
use App\Models\HasilSpk;

class PerkiraanBebanKerja
{
    /**
     * @return array{jp: float, cukup: bool, total_jp: int, pembagi: int}|null
     */
    public static function untukRekomendasi(HasilSpk $spk): ?array
    {
        $tujuan = $spk->sekolahTujuan;
        if (! $tujuan) {
            return null;
        }

        $rombel = (int) ($tujuan->rombel ?? 0);
        if ($rombel <= 0) {
            return null;
        }

        $mapel = $spk->mapelTujuan();
        $jenjang = strtoupper(trim((string) ($tujuan->jenjang ?: $spk->guru?->jenjang ?: '')));

        // Guru kelas SD mengampu satu rombel penuh.
        if ($jenjang === 'SD' && $mapel === AnalisisKebutuhanGuru::LABEL_GURU_KELAS) {
            return [
                'jp' => (float) AnalisisKebutuhanGuru::BEBAN_MENGAJAR_JP,
                'cukup' => true,
                'total_jp' => $rombel * AnalisisKebutuhanGuru::BEBAN_MENGAJAR_JP,
                'pembagi' => $rombel,
            ];
        }

        $jpPerRombel = AnalisisKebutuhanGuru::JP_SMP[$mapel]
            ?? AnalisisKebutuhanGuru::JP_SD_KHUSUS[$mapel]
            ?? null;
        if ($jpPerRombel === null) {
            return null;
        }

        $totalJp = $rombel * $jpPerRombel;
        $guruDiTujuan = Guru::where('sekolah_id', $tujuan->id)
            ->where('mapel', $mapel)
            ->count();
        $pembagi = $guruDiTujuan + 1;
        $jp = $totalJp / $pembagi;

        return [
            'jp' => round($jp, 1),
            'cukup' => $jp >= AnalisisKebutuhanGuru::BEBAN_MENGAJAR_JP,
            'total_jp' => $totalJp,
            'pembagi' => $pembagi,
        ];
    }
}
