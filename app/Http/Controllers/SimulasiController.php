<?php

// Controller Simulasi (C): what-if policy simulation — read-only,
// tidak mengubah data operasional. Akses: kabid_ptk, kadis.

namespace App\Http\Controllers;

use App\Services\SimulasiKebijakan;
use Illuminate\Http\Request;

class SimulasiController extends Controller
{
    public function jalankan(Request $request, SimulasiKebijakan $simulasi)
    {
        $data = $request->validate([
            'ambang_km' => ['required', 'numeric', 'min:1', 'max:500'],
            'mapel' => ['nullable', 'string', 'max:50'],
            'kecamatan' => ['nullable', 'string', 'max:100'],
            'sertakan_3t' => ['nullable', 'boolean'],
        ]);

        $hasil = $simulasi->jalankan($data);

        return view('simulasi.hasil', compact('hasil'));
    }
}
