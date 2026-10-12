<?php

// Seeder: referensi wilayah administrasi Indonesia (nasional, read-only).
// Sumber CSV: github.com/guzfirdaus/Wilayah-Administrasi-Indonesia
// (Kepmendagri 100.1.1-6117/2022, lisensi MIT). Format: delimiter ';'.
// 38 provinsi + 514 kab/kota + ~7,2rb kecamatan + ~83,7rb desa.
// Insert batch 1000 baris agar cepat; idempotent (dilewati bila sudah penuh).

namespace Database\Seeders;

use App\Models\WilayahDesa;
use App\Models\WilayahKabupaten;
use App\Models\WilayahKecamatan;
use App\Models\WilayahProvinsi;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class WilayahSeeder extends Seeder
{
    public function run(): void
    {
        $this->isi('provinces.csv', WilayahProvinsi::class,
            fn ($b) => ['kode' => $b[0], 'nama' => $b[1]], ['kode', 'nama']);
        $this->isi('regencies.csv', WilayahKabupaten::class,
            fn ($b) => ['kode' => $b[0], 'provinsi_kode' => $b[1], 'nama' => $b[2]],
            ['kode', 'provinsi_kode', 'nama']);
        $this->isi('districts.csv', WilayahKecamatan::class,
            fn ($b) => ['kode' => $b[0], 'kabupaten_kode' => $b[1], 'nama' => $b[2]],
            ['kode', 'kabupaten_kode', 'nama']);
        $this->isi('villages.csv', WilayahDesa::class,
            fn ($b) => ['kode' => $b[0], 'kecamatan_kode' => $b[1], 'nama' => $b[2]],
            ['kode', 'kecamatan_kode', 'nama']);
    }

    /**
     * Isi satu tabel dari CSV. Dilewati bila jumlah baris sudah sesuai file
     * (seeder aman dijalankan ulang).
     */
    private function isi(string $berkas, string $model, callable $petakan, array $kolom): void
    {
        $path = database_path('seeders/data/wilayah/'.$berkas);
        $baris = $this->bacaCsv($path);

        if ($model::count() === count($baris)) {
            $this->command->info("  {$berkas}: sudah {$model::count()} baris, dilewati.");

            return;
        }

        Schema::disableForeignKeyConstraints();
        $model::truncate();
        foreach (array_chunk($baris, 1000) as $potong) {
            $model::insert(array_map($petakan, $potong));
        }
        Schema::enableForeignKeyConstraints();

        // Validasi kolom tak terduga (format sumber berubah)
        $contoh = $baris[0] ?? [];
        if (count($contoh) !== count($kolom)) {
            $this->command->warn("  {$berkas}: jumlah kolom ".count($contoh)
                .', ekspektasi '.count($kolom).' — periksa format sumber.');
        }

        $this->command->info("  {$berkas}: ".count($baris).' baris masuk.');
    }

    /**
     * Baca CSV berdelimiter ';' (dengan/tanpa kutip), kembalikan baris data
     * tanpa header.
     */
    private function bacaCsv(string $path): array
    {
        $handle = fopen($path, 'r');
        $baris = [];
        $pertama = true;
        while (($data = fgetcsv($handle, 0, ';')) !== false) {
            if ($pertama) {
                $pertama = false; // lewati header id;name...

                continue;
            }
            if (count(array_filter($data, fn ($v) => trim((string) $v) !== '')) === 0) {
                continue; // lewati baris kosong
            }
            $baris[] = array_map(fn ($v) => trim((string) $v, " \t\n\r\0\x0B\""), $data);
        }
        fclose($handle);

        return $baris;
    }
}
