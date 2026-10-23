<?php

// RegistriMenu: daftar resmi menu/modul aplikasi beserta rute, grup bilah
// sisi, ikon, dan peran bawaan yang boleh mengaksesnya. Pengaturan Akses
// (App\Services\AksesMenu) hanya menyimpan penyimpangan dari bawaan ini,
// jadi menambah modul baru cukup dilakukan di sini.

namespace App\Services;

class RegistriMenu
{
    /**
     * Item menu terurut sesuai tampilan bilah sisi.
     *
     * @var array<string, array{label: string, grup: string, rute: string, pola: string, ikon: string, bawaan: list<string>}>
     */
    public const ITEM = [
        'eksekutif' => [
            'label' => 'Eksekutif', 'grup' => '', 'rute' => 'pemda.dashboard',
            'pola' => 'pemda.*', 'ikon' => 'distribusi',
            'bawaan' => ['pemda', 'kadis', 'kabid_ptk'],
        ],
        'guru' => [
            'label' => 'Data Guru', 'grup' => 'Data Master', 'rute' => 'guru.index',
            'pola' => 'guru.*', 'ikon' => 'guru',
            'bawaan' => ['guru', 'kepala_sekolah', 'ketua_pgri', 'kabid_ptk', 'kadis'],
        ],
        'sekolah' => [
            'label' => 'Data Sekolah', 'grup' => 'Data Master', 'rute' => 'sekolah.index',
            'pola' => 'sekolah.*', 'ikon' => 'sekolah',
            'bawaan' => ['guru', 'kepala_sekolah', 'ketua_pgri', 'kabid_ptk', 'kadis'],
        ],
        'wilayah-provinsi' => [
            'label' => 'Provinsi', 'grup' => 'Referensi Wilayah', 'rute' => 'wilayah.provinsi',
            'pola' => 'wilayah.provinsi', 'ikon' => 'peta',
            'bawaan' => ['guru', 'kepala_sekolah', 'ketua_pgri', 'kabid_ptk', 'kadis', 'pemda'],
        ],
        'wilayah-kabupaten' => [
            'label' => 'Kabupaten/Kota', 'grup' => 'Referensi Wilayah', 'rute' => 'wilayah.kabupaten',
            'pola' => 'wilayah.kabupaten', 'ikon' => 'peta',
            'bawaan' => ['guru', 'kepala_sekolah', 'ketua_pgri', 'kabid_ptk', 'kadis', 'pemda'],
        ],
        'wilayah-kecamatan' => [
            'label' => 'Kecamatan', 'grup' => 'Referensi Wilayah', 'rute' => 'wilayah.kecamatan',
            'pola' => 'wilayah.kecamatan', 'ikon' => 'peta',
            'bawaan' => ['guru', 'kepala_sekolah', 'ketua_pgri', 'kabid_ptk', 'kadis', 'pemda'],
        ],
        'wilayah-desa' => [
            'label' => 'Desa/Kelurahan', 'grup' => 'Referensi Wilayah', 'rute' => 'wilayah.desa',
            'pola' => 'wilayah.desa', 'ikon' => 'peta',
            'bawaan' => ['guru', 'kepala_sekolah', 'ketua_pgri', 'kabid_ptk', 'kadis', 'pemda'],
        ],
        'usulan' => [
            'label' => 'Usulan', 'grup' => 'Proses Penempatan', 'rute' => 'usulan.index',
            'pola' => 'usulan.*', 'ikon' => 'usulan',
            'bawaan' => ['guru', 'kepala_sekolah', 'ketua_pgri', 'kabid_ptk', 'kadis'],
        ],
        'swap' => [
            'label' => 'Mutasi Silang', 'grup' => 'Proses Penempatan', 'rute' => 'swap.index',
            'pola' => 'swap.*', 'ikon' => 'swap',
            'bawaan' => ['guru', 'kepala_sekolah', 'ketua_pgri', 'kabid_ptk', 'kadis'],
        ],
        'spk' => [
            'label' => 'SPK Penempatan', 'grup' => 'Proses Penempatan', 'rute' => 'spk.index',
            'pola' => 'spk.*', 'ikon' => 'spk',
            'bawaan' => ['guru', 'kepala_sekolah', 'ketua_pgri', 'kabid_ptk', 'kadis'],
        ],
        'kualitas-data' => [
            'label' => 'Kualitas Data', 'grup' => 'Analisis', 'rute' => 'kualitas-data.index',
            'pola' => 'kualitas-data.*', 'ikon' => 'kualitas',
            'bawaan' => ['kabid_ptk', 'kadis'],
        ],
        'distribusi' => [
            'label' => 'Distribusi', 'grup' => 'Analisis', 'rute' => 'distribusi.index',
            'pola' => 'distribusi.*', 'ikon' => 'distribusi',
            'bawaan' => ['kabid_ptk', 'kadis', 'pemda'],
        ],
        'aksesibilitas' => [
            'label' => 'Aksesibilitas', 'grup' => 'Analisis', 'rute' => 'aksesibilitas.index',
            'pola' => 'aksesibilitas.*', 'ikon' => 'akses',
            'bawaan' => ['kabid_ptk', 'kadis'],
        ],
        'simulasi' => [
            'label' => 'Simulasi', 'grup' => 'Analisis', 'rute' => 'simulasi.index',
            'pola' => 'simulasi.*', 'ikon' => 'simulasi',
            'bawaan' => ['kabid_ptk', 'kadis'],
        ],
        'proyeksi' => [
            'label' => 'Proyeksi', 'grup' => 'Analisis', 'rute' => 'proyeksi.index',
            'pola' => 'proyeksi.*', 'ikon' => 'proyeksi',
            'bawaan' => ['kabid_ptk', 'kadis'],
        ],
        'pengguna' => [
            'label' => 'Kelola Pengguna', 'grup' => 'Sistem', 'rute' => 'pengguna.index',
            'pola' => 'pengguna.*', 'ikon' => 'pengguna',
            'bawaan' => ['kabid_ptk'],
        ],
        'aktivitas' => [
            'label' => 'Jejak Audit', 'grup' => 'Sistem', 'rute' => 'aktivitas.index',
            'pola' => 'aktivitas.*', 'ikon' => 'audit',
            'bawaan' => ['kabid_ptk'],
        ],
        'pengaturan-pemda' => [
            'label' => 'Pengaturan Pemda', 'grup' => 'Sistem', 'rute' => 'pengaturan.pemda',
            'pola' => 'pengaturan.pemda*', 'ikon' => 'peta',
            'bawaan' => ['kabid_ptk'],
        ],
        'pengaturan-akses' => [
            'label' => 'Pengaturan Akses', 'grup' => 'Sistem', 'rute' => 'pengaturan.akses',
            'pola' => 'pengaturan.akses*', 'ikon' => 'akses',
            'bawaan' => ['kabid_ptk'],
        ],
    ];

    /** Awalan nama rute → kunci menu (untuk sub-rute satu modul). */
    private const PREFIX_RUTE = [
        'guru' => 'guru',
        'sekolah' => 'sekolah',
        'usulan' => 'usulan',
        'swap' => 'swap',
        'spk' => 'spk',
        'kualitas-data' => 'kualitas-data',
        'distribusi' => 'distribusi',
        'aksesibilitas' => 'aksesibilitas',
        'simulasi' => 'simulasi',
        'proyeksi' => 'proyeksi',
        'pengguna' => 'pengguna',
        'aktivitas' => 'aktivitas',
        'pemda' => 'eksekutif',
    ];

    /** Nama rute persis → kunci menu (halaman wilayah & pengaturan). */
    private const RUTE_PERSIS = [
        'wilayah.provinsi' => 'wilayah-provinsi',
        'wilayah.kabupaten' => 'wilayah-kabupaten',
        'wilayah.kecamatan' => 'wilayah-kecamatan',
        'wilayah.desa' => 'wilayah-desa',
        'pengaturan.pemda' => 'pengaturan-pemda',
        'pengaturan.akses' => 'pengaturan-akses',
    ];

    public static function ada(string $kunci): bool
    {
        return array_key_exists($kunci, self::ITEM);
    }

    /** @return list<string> */
    public static function peranBawaan(string $kunci): array
    {
        return self::ITEM[$kunci]['bawaan'] ?? [];
    }

    /**
     * Item terkelompok per grup bilah sisi, urut sesuai registri.
     *
     * @return array<string, array<string, array{label: string, grup: string, rute: string, pola: string, ikon: string, bawaan: list<string>}>>
     */
    public static function grup(): array
    {
        $hasil = [];
        foreach (self::ITEM as $kunci => $item) {
            $hasil[$item['grup']][$kunci] = $item;
        }

        return $hasil;
    }

    /**
     * Kunci menu yang menjaga sebuah nama rute, atau null bila rute itu
     * bebas dari pengaturan akses (dashboard, profil, API wilayah,
     * impersonate, autentikasi).
     */
    public static function kunciUntukRute(?string $namaRute): ?string
    {
        if ($namaRute === null || $namaRute === '') {
            return null;
        }
        foreach (self::RUTE_PERSIS as $rute => $kunci) {
            if ($namaRute === $rute || str_starts_with($namaRute, $rute.'.')) {
                return $kunci;
            }
        }
        $prefix = explode('.', $namaRute)[0];

        return self::PREFIX_RUTE[$prefix] ?? null;
    }
}
