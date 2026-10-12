<?php

// Controller Aksesibilitas (E): analisis jarak agregat — tanpa identitas guru.
// Akses: kabid_ptk, kadis.

namespace App\Http\Controllers;

use App\Services\AnalisisJarak;
use Illuminate\Http\Request;

class AksesibilitasController extends Controller
{
    public function index(Request $request, AnalisisJarak $analisis)
    {
        $data = $analisis->perKecamatan($request->kategori_daerah);

        return view('aksesibilitas.index', [
            'data' => $data,
            'total_guru' => array_sum(array_column($data, 'jumlah')),
        ]);
    }
}
