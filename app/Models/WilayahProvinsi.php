<?php

// Model WilayahProvinsi: referensi provinsi (kode Kemendagri 2 digit).
// Data read-only dari Kepmendagri 100.1.1-6117/2022.

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WilayahProvinsi extends Model
{
    protected $table = 'wilayah_provinsi';

    protected $primaryKey = 'kode';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = ['kode', 'nama'];

    public function kabupaten()
    {
        return $this->hasMany(WilayahKabupaten::class, 'provinsi_kode', 'kode');
    }
}
