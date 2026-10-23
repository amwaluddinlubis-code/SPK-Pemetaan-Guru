<?php

// PengaturanSpk: pilihan perilaku mesin SPK yang ditetapkan dinas
// (operator/kabid) — disimpan pada tabel pengaturan kunci-nilai.
//
// Rekomendasi lintas jenjang (keputusan AMWAL, 23 Okt 2026): dibuat
// sebagai PENGATURAN, bukan perilaku tetap, agar dinas dapat memilih:
// - 'boleh' (Opsi B, bawaan — perilaku aplikasi selama ini):
//   rekomendasi lintas jenjang diizinkan; khusus rekomendasi tingkat 2
//   (linier kelompok), lencana linieritas dinilai dari jenjang guru —
//   konsisten dengan basis penurunan mesin — bukan jenjang sekolah
//   tujuan (menyamakan basis ini menutup temuan QA Fase 3).
// - 'larang' (Opsi A): mesin tidak membuat rekomendasi ke jenjang
//   berbeda (guru SMP hanya ke SMP, guru SD hanya ke SD).

namespace App\Services;

use App\Models\Pengaturan;

class PengaturanSpk
{
    public const KUNCI_LINTAS_JENJANG = 'spk_lintas_jenjang';

    public const MODE_LINTAS_JENJANG = [
        'larang' => 'Larang lintas jenjang (Opsi A)',
        'boleh' => 'Izinkan lintas jenjang (Opsi B)',
    ];

    public static function modeLintasJenjang(): string
    {
        $nilai = Pengaturan::ambil(self::KUNCI_LINTAS_JENJANG);

        return in_array($nilai, array_keys(self::MODE_LINTAS_JENJANG), true)
            ? $nilai
            : 'boleh';
    }

    public static function bolehLintasJenjang(): bool
    {
        return static::modeLintasJenjang() === 'boleh';
    }

    public static function simpanModeLintasJenjang(string $mode): void
    {
        Pengaturan::simpan(self::KUNCI_LINTAS_JENJANG, $mode);
    }
}
