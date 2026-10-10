<?php
// Model RekomendasiSwap: satu baris = satu usulan pertukaran (swap) dua guru
// yang saling menguntungkan — keduanya menjadi lebih dekat ke domisili
// masing-masing setelah bertukar sekolah.

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RekomendasiSwap extends Model
{
    protected $table = 'rekomendasi_swaps';

    protected $fillable = [
        'guru_a_id',
        'guru_b_id',
        'sekolah_tujuan_a_id',
        'sekolah_tujuan_b_id',
        'jarak_a_sekarang_km',
        'jarak_b_sekarang_km',
        'jarak_a_usulan_km',
        'jarak_b_usulan_km',
        'hemat_a_km',
        'hemat_b_km',
        'total_hemat_km',
        'status',
        'diproses_oleh',
        'catatan_admin',
    ];

    // Pastikan metrik jarak selalu dibaca sebagai angka desimal
    protected $casts = [
        'jarak_a_sekarang_km' => 'float',
        'jarak_b_sekarang_km' => 'float',
        'jarak_a_usulan_km' => 'float',
        'jarak_b_usulan_km' => 'float',
        'hemat_a_km' => 'float',
        'hemat_b_km' => 'float',
        'total_hemat_km' => 'float',
    ];

    // Daftar status yang sah + label bahasa Indonesia untuk tampilan
    public const STATUS = [
        'menunggu' => 'Menunggu',
        'disetujui' => 'Disetujui',
        'ditolak' => 'Ditolak',
        'batal_karena_konflik' => 'Batal (konflik)',
    ];

    public function guruA()
    {
        return $this->belongsTo(Guru::class, 'guru_a_id');
    }

    public function guruB()
    {
        return $this->belongsTo(Guru::class, 'guru_b_id');
    }

    public function sekolahTujuanA()
    {
        return $this->belongsTo(Sekolah::class, 'sekolah_tujuan_a_id');
    }

    public function sekolahTujuanB()
    {
        return $this->belongsTo(Sekolah::class, 'sekolah_tujuan_b_id');
    }

    public function diprosesOleh()
    {
        return $this->belongsTo(User::class, 'diproses_oleh');
    }

    public function labelStatus(): string
    {
        return self::STATUS[$this->status] ?? $this->status;
    }
}
