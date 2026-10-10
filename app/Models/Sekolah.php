<?php
// Model Sekolah: merepresentasikan satu sekolah di tabel 'sekolah'.

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sekolah extends Model
{
    // Nama tabel tidak mengikuti jamak Inggris, jadi disebut eksplisit
    protected $table = 'sekolah';

    protected $fillable = [
        'npsn',
        'nama',
        'alamat',
        'kecamatan',
        'lat',
        'lng',
    ];

    protected $casts = [
        'lat' => 'float',
        'lng' => 'float',
    ];

    /**
     * Satu sekolah punya BANYAK kebutuhan guru (per mata pelajaran).
     * Contoh: SDN 1 butuh 2 guru Matematika + 1 guru IPA.
     */
    public function kebutuhan()
    {
        return $this->hasMany(KebutuhanGuru::class);
    }

    /**
     * Shortcut: berapa guru yang dibutuhkan untuk satu mapel tertentu?
     * Mengembalikan 0 jika tidak ada kebutuhan untuk mapel itu.
     */
    public function butuhMapel(string $mapel): int
    {
        return (int) $this->kebutuhan->where('mapel', $mapel)->sum('jumlah');
    }
}
