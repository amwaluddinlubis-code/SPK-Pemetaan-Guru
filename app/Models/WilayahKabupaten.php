<?php

// Model WilayahKabupaten: referensi kabupaten/kota (kode 4 digit).

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WilayahKabupaten extends Model
{
    protected $table = 'wilayah_kabupaten';

    protected $primaryKey = 'kode';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = ['kode', 'provinsi_kode', 'nama'];

    public function provinsi()
    {
        return $this->belongsTo(WilayahProvinsi::class, 'provinsi_kode', 'kode');
    }

    public function kecamatan()
    {
        return $this->hasMany(WilayahKecamatan::class, 'kabupaten_kode', 'kode');
    }
}
