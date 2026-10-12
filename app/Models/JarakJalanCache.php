<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Cache hasil query jarak jalan ke layanan routing eksternal.
// Kunci: koordinat dibulatkan 4 desimal (~11 m presisi).
class JarakJalanCache extends Model
{
    protected $table = 'jarak_jalan_cache';

    protected $fillable = [
        'lat_asal', 'lng_asal', 'lat_tujuan', 'lng_tujuan',
        'jarak_km', 'waktu_menit', 'sumber',
    ];

    protected $casts = [
        'lat_asal' => 'decimal:4',
        'lng_asal' => 'decimal:4',
        'lat_tujuan' => 'decimal:4',
        'lng_tujuan' => 'decimal:4',
        'jarak_km' => 'decimal:2',
    ];

    // Bulatkan koordinat 4 desimal untuk kunci cache yang konsisten.
    public static function kunci(float $latAsal, float $lngAsal, float $latTujuan, float $lngTujuan): array
    {
        return [
            'lat_asal' => round($latAsal, 4),
            'lng_asal' => round($lngAsal, 4),
            'lat_tujuan' => round($latTujuan, 4),
            'lng_tujuan' => round($lngTujuan, 4),
        ];
    }

    public static function cari(float $latAsal, float $lngAsal, float $latTujuan, float $lngTujuan): ?self
    {
        return static::where(static::kunci($latAsal, $lngAsal, $latTujuan, $lngTujuan))->first();
    }
}
