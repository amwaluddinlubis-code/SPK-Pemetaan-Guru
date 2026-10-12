<?php

// Model WilayahKecamatan: referensi kecamatan (kode 7 digit).

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WilayahKecamatan extends Model
{
    protected $table = 'wilayah_kecamatan';

    protected $primaryKey = 'kode';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = ['kode', 'kabupaten_kode', 'nama'];

    public function kabupaten()
    {
        return $this->belongsTo(WilayahKabupaten::class, 'kabupaten_kode', 'kode');
    }

    public function desa()
    {
        return $this->hasMany(WilayahDesa::class, 'kecamatan_kode', 'kode');
    }
}
