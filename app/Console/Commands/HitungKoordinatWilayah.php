<?php

// Perintah: isi koordinat titik tengah kecamatan & desa dari rata-rata
// koordinat sekolah di wilayahnya. Dipakai peta formulir agar terbang ke
// wilayah terpilih. Aman dijalankan ulang (menimpa hasil hitungan lama);
// koreksi manual lewat database tetap dihormati API sebagai sumber
// pertama, tetapi perintah ini akan menghitung ulang dari sekolah.

namespace App\Console\Commands;

use App\Services\KoordinatWilayah;
use Illuminate\Console\Command;

class HitungKoordinatWilayah extends Command
{
    protected $signature = 'wilayah:hitung-koordinat';

    protected $description = 'Isi lat/lng kecamatan & desa dari rata-rata koordinat sekolah di wilayahnya';

    public function handle(KoordinatWilayah $layanan): int
    {
        $hasil = $layanan->isiDariSekolah();

        $this->info("Kecamatan terisi: {$hasil['kecamatan']}");
        $this->info("Desa terisi: {$hasil['desa']}");
        if ($hasil['ambigu'] > 0) {
            $this->warn("Nama kecamatan ambigu (sama di banyak kabupaten), dilewati: {$hasil['ambigu']}");
        }

        return self::SUCCESS;
    }
}
