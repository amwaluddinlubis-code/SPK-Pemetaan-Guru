<?php

// Model WilayahDesa: referensi desa/kelurahan (kode 13 digit).

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WilayahDesa extends Model
{
    protected $table = 'wilayah_desa';

    protected $primaryKey = 'kode';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = ['kode', 'kecamatan_kode', 'nama'];

    public function kecamatan()
    {
        return $this->belongsTo(WilayahKecamatan::class, 'kecamatan_kode', 'kode');
    }
}
