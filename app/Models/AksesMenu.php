<?php

// Model AksesMenu: satu baris = keputusan akses satu peran ke satu menu/modul.
// Baris yang tidak ada berarti "ikuti bawaan" dari App\Services\RegistriMenu.

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AksesMenu extends Model
{
    protected $table = 'akses_menu';

    protected $fillable = ['role', 'kunci', 'boleh'];

    protected function casts(): array
    {
        return ['boleh' => 'boolean'];
    }
}
