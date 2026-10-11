<?php
// Model KebutuhanGuru: satu baris = "sekolah X butuh N guru mapel Y".

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KebutuhanGuru extends Model
{
    // Nama tabel tidak mengikuti jamak Inggris, jadi disebut eksplisit
    protected $table = 'kebutuhan_guru';

    protected $fillable = [
        'sekolah_id',
        'mapel',
        'jumlah',
    ];

    /**
     * Setiap kebutuhan milik SATU sekolah.
     */
    public function sekolah()
    {
        return $this->belongsTo(Sekolah::class);
    }
}
