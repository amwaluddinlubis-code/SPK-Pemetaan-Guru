<?php
// Seeder: 553 sekolah asli Kab. Mandailing Natal (Sumatera Utara).
// Sumber: referensi.data.kemendikdasmen.go.id (diakses 2026-10-10).
// Data: NPSN, nama, alamat, kecamatan, status = ASLI.
// Koordinat: hasil geocoding Nominatim (OSM); yang tidak ditemukan memakai
//   titik kecamatan (kolom koordinat_akurat=false di JSON sumber).
//
// ⚠️ KEBUTUHAN GURU di bawah ini hanya CONTOH untuk demo —
//    ganti dengan data kebutuhan riil (mis. dari Dapodik).

namespace Database\Seeders;

use App\Models\KebutuhanGuru;
use App\Models\Sekolah;
use Illuminate\Database\Seeder;

class SekolahMadinaSeeder extends Seeder
{
    public function run(): void
    {
        KebutuhanGuru::query()->delete();
        Sekolah::query()->delete();

        $daftar = json_decode(
            file_get_contents(database_path('seeders/data/sekolah-madina.json')),
            true
        );

        $i = 0;
        $npsnTerpakai = [];
        foreach ($daftar as $s) {
            // Lewati NPSN duplikat (ada 1 kasus di data sumber Kemendikdasmen:
            // NPSN 10207981 dipakai dua sekolah berbeda di Bukit Malintang)
            if (isset($npsnTerpakai[$s['npsn']])) {
                continue;
            }
            $npsnTerpakai[$s['npsn']] = true;

            $sekolah = Sekolah::create([
                'npsn'      => $s['npsn'],
                'nama'      => $s['nama'],
                'alamat'    => $s['alamat'] ?: $s['kelurahan'],
                'kecamatan' => $s['kecamatan'],
                'lat'       => $s['lat'],
                'lng'       => $s['lng'],
            ]);

            // --- Kebutuhan CONTOH (demo saja) ---
            $jenjang = $this->jenjang($s['nama']);
            $butuh = $jenjang === 'SMP'
                ? ['Matematika' => 1, 'IPA' => 1]
                : ['Matematika' => 1];
            // Variasi tambahan agar demo rekomendasi beragam
            $ekstra = ['Bahasa Inggris', 'Bahasa Indonesia', 'PJOK'];
            $butuh[$ekstra[$i % 3]] = 1;

            foreach ($butuh as $mapel => $jumlah) {
                KebutuhanGuru::create([
                    'sekolah_id' => $sekolah->id,
                    'mapel'      => $mapel,
                    'jumlah'     => $jumlah,
                ]);
            }
            $i++;
        }
    }

    private function jenjang(string $nama): string
    {
        $n = strtoupper($nama);
        if (str_contains($n, 'SMP') || str_contains($n, 'MTS')) {
            return 'SMP';
        }
        return 'SD';
    }
}
