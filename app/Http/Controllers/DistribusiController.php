<?php

// Controller Distribusi (B): agregat defisit/surplus — hanya angka agregat,
// tanpa identitas guru/NIP/alamat. Akses: kabid_ptk, kadis, pemda.

namespace App\Http\Controllers;

use App\Services\AgregatKebutuhan;
use Illuminate\Http\Request;

class DistribusiController extends Controller
{
    public function index(Request $request, AgregatKebutuhan $agregat)
    {
        $data = $agregat->perKecamatanMapel($request->kecamatan, $request->mapel);
        $filter = $agregat->daftarFilter();

        $total = [
            'tersedia' => array_sum(array_column($data, 'tersedia')),
            'kebutuhan' => array_sum(array_column($data, 'kebutuhan')),
            'defisit' => array_sum(array_column($data, 'defisit')),
            'surplus' => array_sum(array_column($data, 'surplus')),
            'dalam_proses' => array_sum(array_column($data, 'dalam_proses')),
        ];

        return view('distribusi.index', compact('data', 'filter', 'total'));
    }
}
