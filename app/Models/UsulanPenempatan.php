<?php
// Model UsulanPenempatan: satu pengajuan mutasi guru ke sekolah tujuan.
// Status: diajukan → diverifikasi → disetujui | ditolak (dari diajukan/diverifikasi).

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UsulanPenempatan extends Model
{
    protected $table = 'usulan_penempatan';

    protected $fillable = [
        'guru_id',
        'sekolah_id',
        'alasan',
        'status',
        'diajukan_oleh',
        'diproses_oleh',
        'catatan',
    ];

    // Daftar status + label Indonesianya
    public const STATUS = [
        'diajukan'     => 'Diajukan',
        'diverifikasi' => 'Diverifikasi Kabid',
        'disetujui'    => 'Disetujui Kadis',
        'ditolak'      => 'Ditolak',
    ];

    // Status yang dianggap "masih berjalan" (guru tak boleh punya 2 usulan aktif)
    public const AKTIF = ['diajukan', 'diverifikasi'];

    public function guru()
    {
        return $this->belongsTo(Guru::class);
    }

    public function sekolah()
    {
        return $this->belongsTo(Sekolah::class);
    }

    public function pengaju()
    {
        return $this->belongsTo(User::class, 'diajukan_oleh');
    }

    public function pemroses()
    {
        return $this->belongsTo(User::class, 'diproses_oleh');
    }

    public function labelStatus(): string
    {
        return self::STATUS[$this->status] ?? $this->status;
    }

    // Warna badge daisyUI per status
    public function badgeStatus(): string
    {
        return match ($this->status) {
            'diajukan'     => 'badge-warning',
            'diverifikasi' => 'badge-info',
            'disetujui'    => 'badge-success',
            'ditolak'      => 'badge-error',
            default        => 'badge-ghost',
        };
    }

    public function masihAktif(): bool
    {
        return in_array($this->status, self::AKTIF, true);
    }
}
