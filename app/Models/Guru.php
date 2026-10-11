<?php
// Model Guru: merepresentasikan satu baris data guru ASN di tabel 'guru'.

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Guru extends Model
{
    // Nama tabel tidak mengikuti jamak Inggris, jadi disebut eksplisit
    protected $table = 'guru';

    // Kolom-kolom yang boleh diisi massal (create/update)
    protected $fillable = [
        'nip',
        'nama',
        'mapel',
        'jenjang',
        'domisili_alamat',
        'kecamatan',
        'sekolah_id',
        'domisili_lat',
        'domisili_lng',
        'sekolah_saat_ini',
        'sekolah_saat_ini_lat',
        'sekolah_saat_ini_lng',
    ];

    // Pastikan koordinat selalu dibaca sebagai angka desimal
    protected $casts = [
        'domisili_lat'          => 'float',
        'domisili_lng'          => 'float',
        'sekolah_saat_ini_lat'  => 'float',
        'sekolah_saat_ini_lng'  => 'float',
    ];

    /**
     * Relasi: guru bertugas di satu sekolah (administratif).
     * Dipakai untuk scope Kepala Sekolah.
     */
    public function sekolah()
    {
        return $this->belongsTo(Sekolah::class);
    }

    /**
     * Hitung jarak (km) dari domisili guru ke sebuah titik koordinat.
     * Menggunakan rumus Haversine (jarak garis lurus di permukaan Bumi).
     */
    public function jarakKe(float $lat, float $lng): float
    {
        $r = 6371; // jari-jari Bumi (km)
        $dLat = deg2rad($lat - $this->domisili_lat);
        $dLng = deg2rad($lng - $this->domisili_lng);
        $a = sin($dLat / 2) ** 2
           + cos(deg2rad($this->domisili_lat)) * cos(deg2rad($lat))
           * sin($dLng / 2) ** 2;
        return 2 * $r * asin(sqrt($a));
    }
}
