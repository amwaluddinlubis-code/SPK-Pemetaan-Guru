<?php

// Model HasilSpk: satu baris = satu rekomendasi penempatan (Skenario 1 SPK) —
// usulan memindahkan satu guru ke sekolah terdekat yang kekurangan
// formasi untuk mata pelajarannya.

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HasilSpk extends Model
{
    protected $table = 'hasil_spks';

    protected $fillable = [
        'guru_id',
        'sekolah_asal_id',
        'sekolah_tujuan_id',
        'jarak_km',
        'jarak_sekarang_km',
        'hemat_km',
        'status',
        'diproses_oleh',
        'catatan_admin',
    ];

    // Pastikan metrik jarak selalu dibaca sebagai angka desimal
    protected $casts = [
        'jarak_km' => 'float',
        'jarak_sekarang_km' => 'float',
        'hemat_km' => 'float',
    ];

    // Daftar status yang sah + label bahasa Indonesia untuk tampilan
    public const STATUS = [
        'rekomendasi' => 'Rekomendasi',
        'disetujui' => 'Disetujui',
        'ditolak' => 'Ditolak',
    ];

    public function guru()
    {
        return $this->belongsTo(Guru::class);
    }

    public function sekolahTujuan()
    {
        return $this->belongsTo(Sekolah::class, 'sekolah_tujuan_id');
    }

    public function sekolahAsal()
    {
        return $this->belongsTo(Sekolah::class, 'sekolah_asal_id');
    }

    public function diprosesOleh()
    {
        return $this->belongsTo(User::class, 'diproses_oleh');
    }

    public function labelStatus(): string
    {
        return self::STATUS[$this->status] ?? $this->status;
    }

    // Warna badge daisyUI per status (lihat docs/standar-gui.md bagian 9)
    public function badgeStatus(): string
    {
        return match ($this->status) {
            'rekomendasi' => 'badge-warning',
            'disetujui' => 'badge-success',
            'ditolak' => 'badge-error',
            default => 'badge-ghost',
        };
    }

    // Selisih penghematan jarak jika rekomendasi ini disetujui (km).
    // Pakai nilai tersimpan bila ada; hitung manual untuk data lama.
    public function hematKm(): float
    {
        return (float) ($this->hemat_km ?? ($this->jarak_sekarang_km - $this->jarak_km));
    }
}
