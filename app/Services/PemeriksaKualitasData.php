<?php

// Service Pemeriksa Kualitas Data (A): deteksi masalah data secara read-only.
// Tidak mengubah data. Setiap temuan: entitas, jenis, dampak, pesan, tautan.

namespace App\Services;

use App\Models\Guru;
use App\Models\KebutuhanGuru;
use App\Models\Sekolah;
use Illuminate\Support\Facades\DB;

class PemeriksaKualitasData
{
    // Batas koordinat Indonesia (kasar).
    public const LAT_MIN = -11.0;

    public const LAT_MAX = 6.5;

    public const LNG_MIN = 94.5;

    public const LNG_MAX = 141.5;

    /**
     * Jalankan semua pemeriksaan. Kembalikan array temuan:
     * ['entitas', 'entitas_id', 'nama', 'jenis', 'dampak', 'pesan', 'tautan']
     */
    public function periksa(): array
    {
        $temuan = [];
        $temuan = array_merge($temuan, $this->periksaGuru());
        $temuan = array_merge($temuan, $this->periksaSekolah());
        $temuan = array_merge($temuan, $this->periksaKebutuhan());

        return $temuan;
    }

    private function periksaGuru(): array
    {
        $temuan = [];

        // NIP duplikat
        $duplikat = Guru::select('nip', DB::raw('COUNT(*) as jml'))
            ->whereNotNull('nip')->where('nip', '!=', '')
            ->groupBy('nip')->having('jml', '>', 1)->pluck('nip');
        foreach ($duplikat as $nip) {
            foreach (Guru::where('nip', $nip)->get() as $guru) {
                $temuan[] = $this->buat('Guru', $guru->id, $guru->nama ?? '-', 'nip_duplikat', 'tinggi',
                    "NIP {$nip} dipakai oleh lebih dari satu guru.",
                    route('guru.edit', $guru->id));
            }
        }

        foreach (Guru::with('sekolah')->get() as $guru) {
            // Koordinat domisili
            if ($guru->domisili_lat === null || $guru->domisili_lng === null
                || ((float) $guru->domisili_lat === 0.0 && (float) $guru->domisili_lng === 0.0)) {
                $temuan[] = $this->buat('Guru', $guru->id, $guru->nama, 'koordinat_kosong', 'tinggi',
                    'Koordinat domisili kosong/belum diisi — jarak tidak bisa dihitung.',
                    route('guru.edit', $guru->id));
            } elseif (! $this->koordinatValid($guru->domisili_lat, $guru->domisili_lng)) {
                $temuan[] = $this->buat('Guru', $guru->id, $guru->nama, 'koordinat_invalid', 'tinggi',
                    "Koordinat domisili ({$guru->domisili_lat}, {$guru->domisili_lng}) di luar wilayah Indonesia.",
                    route('guru.edit', $guru->id));
            }

            // Relasi sekolah invalid
            if (! $guru->sekolah) {
                $temuan[] = $this->buat('Guru', $guru->id, $guru->nama, 'relasi_invalid', 'tinggi',
                    "sekolah_id {$guru->sekolah_id} tidak merujuk ke sekolah yang ada.",
                    route('guru.edit', $guru->id));
            }

            // Kecamatan kosong
            if (empty($guru->kecamatan)) {
                $temuan[] = $this->buat('Guru', $guru->id, $guru->nama, 'kecamatan_kosong', 'sedang',
                    'Kecamatan kosong — scope ketua PGRI tidak akurat.',
                    route('guru.edit', $guru->id));
            }
        }

        return $temuan;
    }

    private function periksaSekolah(): array
    {
        $temuan = [];

        // NPSN duplikat
        $duplikat = Sekolah::select('npsn', DB::raw('COUNT(*) as jml'))
            ->whereNotNull('npsn')->where('npsn', '!=', '')
            ->groupBy('npsn')->having('jml', '>', 1)->pluck('npsn');
        foreach ($duplikat as $npsn) {
            foreach (Sekolah::where('npsn', $npsn)->get() as $sekolah) {
                $temuan[] = $this->buat('Sekolah', $sekolah->id, $sekolah->nama ?? '-', 'npsn_duplikat', 'tinggi',
                    "NPSN {$npsn} dipakai oleh lebih dari satu sekolah.",
                    route('sekolah.edit', $sekolah->id));
            }
        }

        foreach (Sekolah::get() as $sekolah) {
            if ($sekolah->lat === null || $sekolah->lng === null
                || ((float) $sekolah->lat === 0.0 && (float) $sekolah->lng === 0.0)) {
                $temuan[] = $this->buat('Sekolah', $sekolah->id, $sekolah->nama, 'koordinat_kosong', 'tinggi',
                    'Koordinat sekolah kosong/belum diisi — tidak bisa jadi tujuan SPK.',
                    route('sekolah.edit', $sekolah->id));
            } elseif (! $this->koordinatValid($sekolah->lat, $sekolah->lng)) {
                $temuan[] = $this->buat('Sekolah', $sekolah->id, $sekolah->nama, 'koordinat_invalid', 'tinggi',
                    "Koordinat ({$sekolah->lat}, {$sekolah->lng}) di luar wilayah Indonesia.",
                    route('sekolah.edit', $sekolah->id));
            }

            if (empty($sekolah->kecamatan)) {
                $temuan[] = $this->buat('Sekolah', $sekolah->id, $sekolah->nama, 'kecamatan_kosong', 'sedang',
                    'Kecamatan kosong — agregat per kecamatan tidak akurat.',
                    route('sekolah.edit', $sekolah->id));
            }
        }

        return $temuan;
    }

    private function periksaKebutuhan(): array
    {
        $temuan = [];

        // get() (bukan cursor()) agar with('sekolah') benar-benar eager loading;
        // cursor() memuat relasi per baris (N+1: ±2.100 query untuk 1.052 baris).
        foreach (KebutuhanGuru::with('sekolah')->get() as $butuh) {
            if ($butuh->jumlah <= 0) {
                $temuan[] = $this->buat('Kebutuhan', $butuh->id,
                    ($butuh->sekolah->nama ?? '-').' / '.$butuh->mapel,
                    'jumlah_tidak_masuk_akal', 'sedang',
                    "Jumlah kebutuhan {$butuh->jumlah} tidak masuk akal.",
                    route('sekolah.edit', $butuh->sekolah_id));
            } elseif ($butuh->jumlah > 20) {
                $temuan[] = $this->buat('Kebutuhan', $butuh->id,
                    ($butuh->sekolah->nama ?? '-').' / '.$butuh->mapel,
                    'jumlah_tidak_masuk_akal', 'rendah',
                    "Jumlah kebutuhan {$butuh->jumlah} sangat besar — periksa kembali.",
                    route('sekolah.edit', $butuh->sekolah_id));
            }

            if (! $butuh->sekolah) {
                $temuan[] = $this->buat('Kebutuhan', $butuh->id, '-', 'relasi_invalid', 'tinggi',
                    "sekolah_id {$butuh->sekolah_id} tidak merujuk ke sekolah yang ada.",
                    '#');
            }
        }

        return $temuan;
    }

    private function koordinatValid($lat, $lng): bool
    {
        return $lat >= self::LAT_MIN && $lat <= self::LAT_MAX
            && $lng >= self::LNG_MIN && $lng <= self::LNG_MAX;
    }

    private function buat(string $entitas, $id, string $nama, string $jenis, string $dampak, string $pesan, string $tautan): array
    {
        return compact('entitas', 'id', 'nama', 'jenis', 'dampak', 'pesan', 'tautan');
    }
}
