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
        'jenjang',
        'rombel',
        'alamat',
        'kecamatan',
        'desa_kelurahan',
        'kategori_daerah',
        'lat',
        'lng',
    ];

    /**
     * Kategori daerah sekolah.
     */
    public const KATEGORI_DAERAH = [
        'biasa' => 'Biasa',
        'terpencil' => 'Terpencil',
        'sangat_terpencil' => 'Sangat Terpencil (3T)',
    ];

    public function labelKategoriDaerah(): string
    {
        return self::KATEGORI_DAERAH[$this->kategori_daerah] ?? '-';
    }

    public function isDaerah3T(): bool
    {
        return in_array($this->kategori_daerah, ['terpencil', 'sangat_terpencil'], true);
    }

    protected $casts = [
        'lat' => 'float',
        'lng' => 'float',
        'rombel' => 'integer',
    ];

    /**
     * Jenjang efektif: kolom 'jenjang' bila terisi, jika tidak ditebak dari
     * nama sekolah (mengandung SMP/MTs → SMP, selain itu SD).
     */
    public function jenjangEfektif(): string
    {
        if (! empty($this->jenjang)) {
            return strtoupper($this->jenjang);
        }
        $nama = strtoupper((string) $this->nama);
        if (str_contains($nama, 'SMP') || str_contains($nama, 'MTS')) {
            return 'SMP';
        }

        return 'SD';
    }

    /**
     * Guru-guru yang saat ini bertugas di sekolah ini.
     */
    public function guru()
    {
        return $this->hasMany(Guru::class);
    }

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
