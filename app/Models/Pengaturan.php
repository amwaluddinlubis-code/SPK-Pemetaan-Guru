<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Pengaturan aplikasi berbentuk kunci-nilai (satu baris per kunci).
 * Dipakai antara lain untuk konteks Pemda terpilih (lihat KonteksPemda).
 */
class Pengaturan extends Model
{
    protected $table = 'pengaturan';

    protected $fillable = [
        'kunci',
        'nilai',
    ];

    /** Ambil nilai satu kunci; $bawaan bila kunci belum ada. */
    public static function ambil(string $kunci, ?string $bawaan = null): ?string
    {
        $baris = static::query()->where('kunci', $kunci)->first();

        return $baris?->nilai ?? $bawaan;
    }

    /** Simpan (buat/perbarui) nilai satu kunci; null menghapus barisnya. */
    public static function simpan(string $kunci, ?string $nilai): void
    {
        if ($nilai === null) {
            static::query()->where('kunci', $kunci)->delete();

            return;
        }

        static::query()->updateOrCreate(['kunci' => $kunci], ['nilai' => $nilai]);
    }
}
