<?php

// Service Analisis Jarak (E): agregat jarak domisili → sekolah per
// kecamatan, kategori daerah, dan jenjang. Menggunakan Haversine
// (garis lurus) yang konsisten dengan SPK — BUKAN waktu tempuh.

namespace App\Services;

use App\Models\Guru;
use Illuminate\Support\Facades\DB;

class AnalisisJarak
{
    /**
     * Agregat per kecamatan: rata-rata, median kasar, dan distribusi.
     * Hanya guru dengan koordinat valid.
     */
    public function perKecamatan(?string $kategoriDaerah = null): array
    {
        $query = Guru::join('sekolah', 'guru.sekolah_id', '=', 'sekolah.id')
            ->select(
                'guru.kecamatan',
                'sekolah.kategori_daerah',
                DB::raw('COUNT(*) as jumlah'),
                DB::raw('AVG('.$this->haversineSql().') as rata_km'),
                DB::raw('MAX('.$this->haversineSql().') as maks_km'),
                DB::raw('SUM(CASE WHEN '.$this->haversineSql().' > 15 THEN 1 ELSE 0 END) as di_atas_15km')
            )
            ->whereNotNull('guru.domisili_lat')
            ->whereNotNull('sekolah.lat')
            ->groupBy('guru.kecamatan', 'sekolah.kategori_daerah')
            ->orderBy('guru.kecamatan');

        if ($kategoriDaerah) {
            $query->where('sekolah.kategori_daerah', $kategoriDaerah);
        }

        return $query->get()->map(fn ($r) => [
            'kecamatan' => $r->kecamatan,
            'kategori_daerah' => $r->kategori_daerah,
            'jumlah' => (int) $r->jumlah,
            'rata_km' => round((float) $r->rata_km, 2),
            'maks_km' => round((float) $r->maks_km, 2),
            'di_atas_15km' => (int) $r->di_atas_15km,
        ])->toArray();
    }

    /** Ekspresi Haversine SQL (konsisten dengan SpkController). */
    private function haversineSql(): string
    {
        return '(6371 * ACOS(LEAST(1, GREATEST(-1, '.
            'COS(RADIANS(guru.domisili_lat)) * COS(RADIANS(sekolah.lat)) * '.
            'COS(RADIANS(sekolah.lng) - RADIANS(guru.domisili_lng)) + '.
            'SIN(RADIANS(guru.domisili_lat)) * SIN(RADIANS(sekolah.lat))'.
            '))))';
    }
}
