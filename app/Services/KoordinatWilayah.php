<?php

// Layanan Koordinat Wilayah: titik tengah (centroid) kecamatan & desa
// untuk membantu pemilih koordinat pada formulir (peta terbang ke
// wilayah terpilih). Urutan sumber: (1) kolom lat/lng wilayah bila
// sudah terisi, (2) rata-rata koordinat sekolah di wilayah itu,
// (3) rata-rata koordinat domisili guru di wilayah itu.
// Pencocokan nama memakai bentuk kanonik KonteksPemda (huruf besar +
// ejaan baku, mis. MERAPI→MARAPI) karena data sekolah/guru memakai
// ejaan Dapodik sedangkan referensi wilayah memakai ejaan Kemendagri.

namespace App\Services;

use App\Models\Guru;
use App\Models\Sekolah;
use App\Models\WilayahDesa;
use App\Models\WilayahKecamatan;

class KoordinatWilayah
{
    /**
     * Koordinat kecamatan: ['lat' => float, 'lng' => float,
     * 'sumber' => 'wilayah'|'sekolah'|'guru', 'jumlah' => int] atau null.
     */
    public function untukKecamatan(WilayahKecamatan $kecamatan): ?array
    {
        if ($kecamatan->lat !== null && $kecamatan->lng !== null) {
            return ['lat' => (float) $kecamatan->lat, 'lng' => (float) $kecamatan->lng, 'sumber' => 'wilayah', 'jumlah' => 1];
        }

        $nama = KonteksPemda::namaKanonik($kecamatan->nama);

        $sekolah = Sekolah::whereNotNull('lat')->whereNotNull('lng')
            ->get(['kecamatan', 'lat', 'lng'])
            ->filter(fn ($s) => KonteksPemda::namaKanonik((string) $s->kecamatan) === $nama);
        if ($sekolah->isNotEmpty()) {
            return $this->centroid($sekolah, 'sekolah');
        }

        $guru = Guru::whereNotNull('domisili_lat')->whereNotNull('domisili_lng')
            ->get(['kecamatan', 'domisili_lat', 'domisili_lng'])
            ->filter(fn ($g) => KonteksPemda::namaKanonik((string) $g->kecamatan) === $nama);
        if ($guru->isNotEmpty()) {
            return $this->centroid($guru, 'guru', 'domisili_lat', 'domisili_lng');
        }

        return null;
    }

    /**
     * Koordinat desa: sama seperti kecamatan, dengan pencocokan
     * desa di dalam kecamatan induknya.
     */
    public function untukDesa(WilayahDesa $desa): ?array
    {
        if ($desa->lat !== null && $desa->lng !== null) {
            return ['lat' => (float) $desa->lat, 'lng' => (float) $desa->lng, 'sumber' => 'wilayah', 'jumlah' => 1];
        }

        $induk = $desa->kecamatan;
        if ($induk === null) {
            return null;
        }
        $namaKec = KonteksPemda::namaKanonik($induk->nama);
        $namaDesa = KonteksPemda::namaKanonik($desa->nama);

        $sekolah = Sekolah::whereNotNull('lat')->whereNotNull('lng')
            ->get(['kecamatan', 'desa_kelurahan', 'lat', 'lng'])
            ->filter(fn ($s) => KonteksPemda::namaKanonik((string) $s->kecamatan) === $namaKec
                && KonteksPemda::namaKanonik((string) $s->desa_kelurahan) === $namaDesa);
        if ($sekolah->isNotEmpty()) {
            return $this->centroid($sekolah, 'sekolah');
        }

        $guru = Guru::whereNotNull('domisili_lat')->whereNotNull('domisili_lng')
            ->get(['kecamatan', 'desa_kelurahan', 'domisili_lat', 'domisili_lng'])
            ->filter(fn ($g) => KonteksPemda::namaKanonik((string) $g->kecamatan) === $namaKec
                && KonteksPemda::namaKanonik((string) $g->desa_kelurahan) === $namaDesa);
        if ($guru->isNotEmpty()) {
            return $this->centroid($guru, 'guru', 'domisili_lat', 'domisili_lng');
        }

        return null;
    }

    /**
     * Isi massal kolom lat/lng wilayah: pertama dari centroid SEKOLAH
     * (sumber paling otoritatif), lalu baris yang masih kosong dilengkapi
     * dari centroid domisili GURU. Nama kecamatan yang ambigu (muncul di
     * lebih dari satu kabupaten) dilewati agar tidak salah wilayah; desa
     * hanya diisi di dalam kecamatan yang berhasil dicocokkan.
     * Mengembalikan ringkasan jumlah.
     *
     * @return array{kecamatan: int, desa: int, ambigu: int}
     */
    public function isiDariSekolah(): array
    {
        $sekolah = Sekolah::whereNotNull('lat')->whereNotNull('lng')
            ->get(['kecamatan', 'desa_kelurahan', 'lat', 'lng']);

        // Kelompokkan sekolah per nama kecamatan kanonik.
        $perKecamatan = $sekolah->groupBy(fn ($s) => KonteksPemda::namaKanonik((string) $s->kecamatan));

        // Peta nama kanonik → baris wilayah kecamatan (untuk deteksi ambigu).
        $barisPerNama = WilayahKecamatan::all(['kode', 'nama'])
            ->groupBy(fn ($k) => KonteksPemda::namaKanonik($k->nama));

        $terisiKecamatan = 0;
        $ambigu = 0;
        $kecamatanTerpetakan = []; // kode => kumpulan sekolah

        foreach ($perKecamatan as $nama => $grup) {
            $kandidat = $barisPerNama->get($nama);
            if ($kandidat === null) {
                continue; // tidak ada wilayah bernama ini
            }
            if ($kandidat->count() > 1) {
                $ambigu++;

                continue; // nama sama di banyak kabupaten: jangan menebak
            }
            $baris = $kandidat->first();
            $c = $this->centroid($grup, 'sekolah');
            WilayahKecamatan::where('kode', $baris->kode)
                ->update(['lat' => $c['lat'], 'lng' => $c['lng']]);
            $terisiKecamatan++;
            $kecamatanTerpetakan[$baris->kode] = $grup;
        }

        // Desa: di dalam kecamatan yang terpetakan, kelompokkan per nama desa.
        $terisiDesa = 0;
        foreach ($kecamatanTerpetakan as $kodeKec => $grup) {
            $perDesa = $grup
                ->filter(fn ($s) => trim((string) $s->desa_kelurahan) !== '')
                ->groupBy(fn ($s) => KonteksPemda::namaKanonik((string) $s->desa_kelurahan));
            $barisDesa = WilayahDesa::where('kecamatan_kode', $kodeKec)
                ->get(['kode', 'nama'])
                ->keyBy(fn ($d) => KonteksPemda::namaKanonik($d->nama));
            foreach ($perDesa as $namaDesa => $grupDesa) {
                $baris = $barisDesa->get($namaDesa);
                if ($baris === null) {
                    continue;
                }
                $c = $this->centroid($grupDesa, 'sekolah');
                WilayahDesa::where('kode', $baris->kode)
                    ->update(['lat' => $c['lat'], 'lng' => $c['lng']]);
                $terisiDesa++;
            }
        }

        // --- Lengkapi dari domisili guru untuk yang masih kosong ---
        // (Data sekolah nyata tidak selalu memuat desa_kelurahan, sedangkan
        // domisili guru memuatnya — sumber kedua untuk titik tengah desa.)
        $guru = Guru::whereNotNull('domisili_lat')->whereNotNull('domisili_lng')
            ->get(['kecamatan', 'desa_kelurahan', 'domisili_lat', 'domisili_lng']);
        $guruPerKecamatan = $guru->groupBy(fn ($g) => KonteksPemda::namaKanonik((string) $g->kecamatan));

        foreach ($guruPerKecamatan as $nama => $grup) {
            $kandidat = $barisPerNama->get($nama);
            if ($kandidat === null || $kandidat->count() > 1) {
                continue;
            }
            $baris = $kandidat->first();
            if (! isset($kecamatanTerpetakan[$baris->kode])) {
                $c = $this->centroid($grup, 'guru', 'domisili_lat', 'domisili_lng');
                $diubah = WilayahKecamatan::where('kode', $baris->kode)
                    ->whereNull('lat')->update(['lat' => $c['lat'], 'lng' => $c['lng']]);
                if ($diubah > 0) {
                    $terisiKecamatan++;
                }
                $kecamatanTerpetakan[$baris->kode] = collect();
            }
            // Desa dari domisili guru di kecamatan ini.
            $perDesaGuru = $grup
                ->filter(fn ($g) => trim((string) $g->desa_kelurahan) !== '')
                ->groupBy(fn ($g) => KonteksPemda::namaKanonik((string) $g->desa_kelurahan));
            if ($perDesaGuru->isEmpty()) {
                continue;
            }
            $barisDesa = WilayahDesa::where('kecamatan_kode', $baris->kode)
                ->get(['kode', 'nama'])
                ->keyBy(fn ($d) => KonteksPemda::namaKanonik($d->nama));
            foreach ($perDesaGuru as $namaDesa => $grupDesa) {
                $barisDesaRow = $barisDesa->get($namaDesa);
                if ($barisDesaRow === null) {
                    continue;
                }
                $c = $this->centroid($grupDesa, 'guru', 'domisili_lat', 'domisili_lng');
                $diubah = WilayahDesa::where('kode', $barisDesaRow->kode)
                    ->whereNull('lat')->update(['lat' => $c['lat'], 'lng' => $c['lng']]);
                if ($diubah > 0) {
                    $terisiDesa++;
                }
            }
        }

        return ['kecamatan' => $terisiKecamatan, 'desa' => $terisiDesa, 'ambigu' => $ambigu];
    }

    /** @param iterable<object> $baris */
    private function centroid(iterable $baris, string $sumber, string $kolomLat = 'lat', string $kolomLng = 'lng'): array
    {
        $jumlah = 0;
        $totalLat = 0.0;
        $totalLng = 0.0;
        foreach ($baris as $b) {
            $totalLat += (float) $b->{$kolomLat};
            $totalLng += (float) $b->{$kolomLng};
            $jumlah++;
        }

        return [
            'lat' => round($totalLat / $jumlah, 7),
            'lng' => round($totalLng / $jumlah, 7),
            'sumber' => $sumber,
            'jumlah' => $jumlah,
        ];
    }
}
