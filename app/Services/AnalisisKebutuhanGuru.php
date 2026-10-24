<?php

// Service Analisis Kebutuhan Guru: hitung kebutuhan guru per sekolah dari
// jumlah rombel (Dapodik) dan alokasi JP Permendikdasmen No. 13/2025.
//
// Aturan jenis guru (keputusan 12 Okt 2026):
//   SD  → hanya 3 jenis: guru kelas (= jumlah rombel), guru PJOK, guru agama.
//   SMP → guru mata pelajaran per mapel.
//
// Rumus per mapel:  kebutuhan = ceil(rombel × JP/minggu ÷ 24)
// 24 = acuan beban mengajar guru per minggu (dapat diubah via parameter).
//
// Label mapel memakai kosakata pendek aplikasi ('IPA', 'PJOK', ...) agar
// cocok dengan kolom guru.mapel yang dipakai pencocokan SPK
// (strtolower+trim, lihat SpkController & KebutuhanGuru::sisaDenganAlokasi).

namespace App\Services;

class AnalisisKebutuhanGuru
{
    /**
     * Acuan beban mengajar satu guru penuh per minggu (JP).
     */
    public const BEBAN_MENGAJAR_JP = 24;

    /**
     * Alokasi JP intrakurikuler per minggu per mapel untuk SMP
     * (Permendikdasmen 13/2025, Tabel 6–7; sama untuk kelas VII, VIII, IX).
     * Kunci = label kanonis yang dipakai di tabel kebutuhan_guru.
     *
     * @var array<string, int>
     */
    public const JP_SMP = [
        'Agama Islam' => 2,
        'Agama Kristen' => 2,
        'Pancasila' => 2,
        'Bahasa Indonesia' => 5,
        'Matematika' => 4,
        'IPA' => 4,
        'IPS' => 3,
        'Bahasa Inggris' => 3,
        'PJOK' => 2,
        'Informatika' => 2,
        'Seni/Prakarya' => 2,
    ];

    /**
     * Mapel khusus SD yang diampu guru mapel (JP/minggu, Permendikdasmen
     * 13/2025 Tabel 1–5: PJOK & Agama = 3 JP di semua tingkat kelas).
     * Selain dua ini, SD diampu guru kelas.
     *
     * @var array<string, int>
     */
    public const JP_SD_KHUSUS = [
        'PJOK' => 3,
        'Agama Islam' => 3,
        'Agama Kristen' => 3,
    ];

    /**
     * Label agama yang TIDAK dihitung otomatis oleh analisis massal.
     * Kebutuhan guru agama dihitung dari total rombel sekolah; data
     * rombel per agama siswa tidak tersedia, jadi analisis massal hanya
     * menghitung Agama Islam (label gabungan "Agama" sebelumnya, rumus
     * dan total tidak berubah — keputusan pemisahan label 23 Okt 2026).
     * Kebutuhan Agama Kristen diisi manual per sekolah bila diperlukan.
     */
    public const AGAMA_TIDAK_DIHITUNG_MASSAL = ['Agama Kristen'];

    /**
     * Label kanonis untuk guru kelas SD.
     */
    public const LABEL_GURU_KELAS = 'Guru Kelas';

    /**
     * Peta alias nama mapel → label kanonis. Dipakai agar varian penulisan
     * di guru.mapel (mis. "Ilmu Pengetahuan Alam", "PAI") tetap terhitung
     * pada baris yang benar. Kunci sudah lowercase+trim.
     *
     * @var array<string, string>
     */
    public const ALIAS_MAPEL = [
        'pai' => 'Agama Islam',
        'agama islam' => 'Agama Islam',
        'pendidikan agama islam' => 'Agama Islam',
        'agama kristen' => 'Agama Kristen',
        'pendidikan agama kristen' => 'Agama Kristen',
        'pendidikan agama katolik' => 'Agama Katolik',
        'pendidikan agama hindu' => 'Agama Hindu',
        'pendidikan agama buddha' => 'Agama Buddha',
        'pendidikan agama khonghucu' => 'Agama Khonghucu',
        'pkn' => 'Pancasila',
        'ppkn' => 'Pancasila',
        'pendidikan pancasila' => 'Pancasila',
        'bindo' => 'Bahasa Indonesia',
        'b. indonesia' => 'Bahasa Indonesia',
        'bahasa indonesia' => 'Bahasa Indonesia',
        'bing' => 'Bahasa Inggris',
        'b. inggris' => 'Bahasa Inggris',
        'bahasa inggris' => 'Bahasa Inggris',
        'ilmu pengetahuan alam' => 'IPA',
        'ilmu pengetahuan sosial' => 'IPS',
        'penjaskes' => 'PJOK',
        'pendidikan jasmani' => 'PJOK',
        'pendidikan jasmani olahraga dan kesehatan' => 'PJOK',
        'olahraga' => 'PJOK',
        'seni budaya' => 'Seni/Prakarya',
        'seni dan budaya' => 'Seni/Prakarya',
        'seni' => 'Seni/Prakarya',
        'prakarya' => 'Seni/Prakarya',
        'tik' => 'Informatika',
        'teknologi informasi' => 'Informatika',
        'guru kelas' => 'Guru Kelas',
        'wali kelas' => 'Guru Kelas',
    ];

    /**
     * Normalisasi satu nama mapel menjadi label kanonis.
     * Nama yang tidak dikenal dikembalikan apa adanya (trim).
     */
    public static function kanonis(string $mapel): string
    {
        $kunci = strtolower(trim($mapel));
        if ($kunci === '') {
            return '';
        }

        return self::ALIAS_MAPEL[$kunci] ?? trim($mapel);
    }

    /**
     * Daftar label mapel kanonis untuk pilihan isian (form guru dsb.):
     * Guru Kelas (SD) + semua mapel SMP pada JP_SMP. Satu-satunya sumber
     * daftar mapel agar ejaan tidak menyimpang antar isian.
     *
     * @return array<int, string>
     */
    public static function daftarKanonis(): array
    {
        return [self::LABEL_GURU_KELAS, ...array_keys(self::JP_SMP)];
    }

    /**
     * Daftar mapel kanonis per jenjang untuk pilihan isian kebutuhan
     * guru di form sekolah (searchable select mengikuti jenjang):
     *   SD  → Guru Kelas + mapel khusus SD (PJOK & agama);
     *   SMP → semua mapel JP_SMP.
     * Agama selain Islam/Kristen ikut ditawarkan (diisi manual per
     * sekolah; analisis massal hanya menghitung Agama Islam).
     *
     * @return array{SD: array<int, string>, SMP: array<int, string>}
     */
    public static function daftarMapelPerJenjang(): array
    {
        $agamaLain = ['Agama Katolik', 'Agama Hindu', 'Agama Buddha', 'Agama Khonghucu'];

        return [
            'SD' => array_values(array_unique([
                self::LABEL_GURU_KELAS, ...array_keys(self::JP_SD_KHUSUS), ...$agamaLain,
            ])),
            'SMP' => array_values(array_unique([
                ...array_keys(self::JP_SMP), ...$agamaLain,
            ])),
        ];
    }

    /**
     * Hitung kebutuhan guru sebuah sekolah.
     *
     * @param  string  $jenjang  'SD' atau 'SMP' (case-insensitive)
     * @param  int  $rombel  jumlah rombongan belajar (dari Dapodik)
     * @param  array<string, int>  $eksisting  jumlah guru per label kanonis
     * @param  int  $bebanMengajar  acuan JP/minggu per guru penuh
     * @return array<int, array{label: string, jp_per_minggu: ?int, kebutuhan: int, eksisting: int, selisih: int}>
     *                                                                                                             selisih = eksisting − kebutuhan (positif = surplus, negatif = kurang)
     */
    public static function hitung(
        string $jenjang,
        int $rombel,
        array $eksisting = [],
        int $bebanMengajar = self::BEBAN_MENGAJAR_JP
    ): array {
        $jenjang = strtoupper(trim($jenjang));
        $rombel = max(0, $rombel);
        $baris = [];

        if ($jenjang === 'SMP') {
            foreach (self::JP_SMP as $label => $jp) {
                if (in_array($label, self::AGAMA_TIDAK_DIHITUNG_MASSAL, true)) {
                    continue;
                }
                $baris[] = self::baris($label, $jp, $rombel, $eksisting, $bebanMengajar);
            }

            return $baris;
        }

        // SD: guru kelas + dua mapel khusus.
        $baris[] = [
            'label' => self::LABEL_GURU_KELAS,
            'jp_per_minggu' => null,
            'kebutuhan' => $rombel,
            'eksisting' => (int) ($eksisting[self::LABEL_GURU_KELAS] ?? 0),
            'selisih' => (int) ($eksisting[self::LABEL_GURU_KELAS] ?? 0) - $rombel,
        ];
        foreach (self::JP_SD_KHUSUS as $label => $jp) {
            if (in_array($label, self::AGAMA_TIDAK_DIHITUNG_MASSAL, true)) {
                continue;
            }
            $baris[] = self::baris($label, $jp, $rombel, $eksisting, $bebanMengajar);
        }

        return $baris;
    }

    /**
     * Satu baris kebutuhan untuk satu mapel.
     *
     * @param  array<string, int>  $eksisting
     * @return array{label: string, jp_per_minggu: ?int, kebutuhan: int, eksisting: int, selisih: int}
     */
    private static function baris(
        string $label,
        int $jpPerMinggu,
        int $rombel,
        array $eksisting,
        int $bebanMengajar
    ): array {
        $kebutuhan = (int) ceil($rombel * $jpPerMinggu / max(1, $bebanMengajar));
        $ada = (int) ($eksisting[$label] ?? 0);

        return [
            'label' => $label,
            'jp_per_minggu' => $jpPerMinggu,
            'kebutuhan' => $kebutuhan,
            'eksisting' => $ada,
            'selisih' => $ada - $kebutuhan,
        ];
    }
}
