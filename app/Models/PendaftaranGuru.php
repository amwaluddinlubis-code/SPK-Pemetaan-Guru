<?php

// PendaftaranGuru: pendaftaran akun mandiri oleh guru yang belum punya
// akun (mis. NIP belum tercatat). Wajib persetujuan operator dinas
// (kabid_ptk) sebelum data guru & akun dibuat.

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PendaftaranGuru extends Model
{
    protected $table = 'pendaftaran_guru';

    protected $fillable = [
        'nama', 'nip', 'mapel', 'jenjang', 'status_kepegawaian',
        'sekolah_id', 'nama_sekolah', 'no_hp', 'status',
        'catatan_operator', 'diproses_oleh', 'guru_id',
    ];

    public const STATUS = [
        'menunggu' => 'Menunggu Persetujuan',
        'disetujui' => 'Disetujui',
        'ditolak' => 'Ditolak',
    ];

    public function sekolah(): BelongsTo
    {
        return $this->belongsTo(Sekolah::class);
    }

    public function penilai(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diproses_oleh');
    }

    public function guru(): BelongsTo
    {
        return $this->belongsTo(Guru::class);
    }

    /** Nama sekolah tampilan: dari relasi, atau teks bebas pendaftar. */
    public function namaSekolah(): string
    {
        return $this->sekolah?->nama ?? (string) $this->nama_sekolah;
    }
}
