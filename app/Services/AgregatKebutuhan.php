<?php

// Service Agregat Kebutuhan (B): hitung defisit/surplus guru per
// kecamatan, mapel, dan jenjang.
//
// Rumus:
//   tersedia = jumlah guru aktif per (kecamatan, mapel, jenjang)
//   kebutuhan = jumlah formasi per (kecamatan, mapel, jenjang)
//   defisit  = max(kebutuhan - tersedia, 0)
//   surplus  = max(tersedia - kebutuhan, 0)
//
// Guru yang sedang dalam proses mutasi (punya rekomendasi SPK/swap/usulan
// aktif) dihitung SEKALI di sekolah asal — tidak ganda — dan ditandai
// agar tidak disalahartikan sebagai tersedia penuh.

namespace App\Services;

use App\Models\Guru;
use App\Models\HasilSpk;
use App\Models\KebutuhanGuru;
use App\Models\RekomendasiSwap;
use App\Models\UsulanPenempatan;
use Illuminate\Support\Facades\DB;

class AgregatKebutuhan
{
    /**
     * Hitung agregat per kecamatan x mapel.
     * Kembalikan array: ['kecamatan', 'mapel', 'tersedia', 'kebutuhan',
     *   'defisit', 'surplus', 'dalam_proses']
     */
    public function perKecamatanMapel(?string $kecamatan = null, ?string $mapel = null): array
    {
        // Guru dalam proses mutasi (hindari hitung ganda)
        $dalamProses = $this->guruDalamProses();

        $guruQuery = Guru::select('kecamatan', 'mapel', DB::raw('COUNT(*) as tersedia'))
            ->groupBy('kecamatan', 'mapel');
        if ($kecamatan) {
            $guruQuery->where('kecamatan', $kecamatan);
        }
        if ($mapel) {
            $guruQuery->where('mapel', $mapel);
        }
        $tersedia = $guruQuery->get()->keyBy(fn ($r) => $r->kecamatan.'|'.$r->mapel);

        $butuhQuery = KebutuhanGuru::join('sekolah', 'kebutuhan_guru.sekolah_id', '=', 'sekolah.id')
            ->select('sekolah.kecamatan', 'kebutuhan_guru.mapel', DB::raw('SUM(kebutuhan_guru.jumlah) as kebutuhan'))
            ->groupBy('sekolah.kecamatan', 'kebutuhan_guru.mapel');
        if ($kecamatan) {
            $butuhQuery->where('sekolah.kecamatan', $kecamatan);
        }
        if ($mapel) {
            $butuhQuery->where('kebutuhan_guru.mapel', $mapel);
        }
        $kebutuhan = $butuhQuery->get()->keyBy(fn ($r) => $r->kecamatan.'|'.$r->mapel);

        $kunci = $tersedia->keys()->merge($kebutuhan->keys())->merge(array_keys($dalamProses))->unique()->sort()->values();

        $hasil = [];
        foreach ($kunci as $k) {
            [$kec, $mpl] = explode('|', $k, 2);
            $t = (int) ($tersedia[$k]->tersedia ?? 0);
            $b = (int) ($kebutuhan[$k]->kebutuhan ?? 0);
            $hasil[] = [
                'kecamatan' => $kec,
                'mapel' => $mpl,
                'tersedia' => $t,
                'kebutuhan' => $b,
                'defisit' => max($b - $t, 0),
                'surplus' => max($t - $b, 0),
                'dalam_proses' => $dalamProses[$k] ?? 0,
            ];
        }

        return $hasil;
    }

    /**
     * ID guru yang punya rekomendasi/usulan aktif (dalam proses mutasi),
     * dikelompokkan per kecamatan|mapel sekolah asal.
     */
    private function guruDalamProses(): array
    {
        $ids = collect();

        $ids = $ids->merge(
            HasilSpk::where('status', 'rekomendasi')->pluck('guru_id')
        );
        $ids = $ids->merge(
            RekomendasiSwap::where('status', 'menunggu')->pluck('guru_a_id')
        );
        $ids = $ids->merge(
            RekomendasiSwap::where('status', 'menunggu')->pluck('guru_b_id')
        );
        $ids = $ids->merge(
            UsulanPenempatan::whereIn('status', UsulanPenempatan::AKTIF)->pluck('guru_id')
        );

        $hasil = [];
        foreach (Guru::with('sekolah')->whereIn('id', $ids->unique())->get(['id', 'sekolah_id', 'kecamatan', 'mapel']) as $guru) {
            // Agregat formasi berada di kecamatan sekolah penugasan, bukan domisili guru.
            $kecamatanAsal = $guru->sekolah?->kecamatan ?? $guru->kecamatan;
            $k = $kecamatanAsal.'|'.$guru->mapel;
            $hasil[$k] = ($hasil[$k] ?? 0) + 1;
        }

        return $hasil;
    }

    /** Daftar kecamatan & mapel untuk filter. */
    public function daftarFilter(): array
    {
        return [
            'kecamatan' => Guru::distinct()->orderBy('kecamatan')->pluck('kecamatan'),
            'mapel' => Guru::distinct()->orderBy('mapel')->pluck('mapel'),
        ];
    }
}
