<?php

// Controller Wilayah: referensi administrasi Indonesia (read-only).
// Halaman index dirender komponen Livewire Tabel* (tanpa reload).
// Method api* mengembalikan JSON untuk dropdown berjenjang di form.

namespace App\Http\Controllers;

use App\Models\WilayahDesa;
use App\Models\WilayahKabupaten;
use App\Models\WilayahKecamatan;
use App\Models\WilayahProvinsi;
use Illuminate\Http\Request;

class WilayahController extends Controller
{
    public function provinsi()
    {
        return view('wilayah.provinsi');
    }

    public function kabupaten()
    {
        return view('wilayah.kabupaten');
    }

    public function kecamatan()
    {
        return view('wilayah.kecamatan');
    }

    public function desa()
    {
        return view('wilayah.desa');
    }

    /**
     * Daftar provinsi untuk dropdown (kode + nama, urut nama).
     */
    public function apiProvinsi()
    {
        return response()->json(
            WilayahProvinsi::orderBy('nama')->get(['kode', 'nama'])
        );
    }

    /**
     * Daftar kabupaten/kota dalam satu provinsi (?provinsi=12).
     */
    public function apiKabupaten(Request $request)
    {
        $provinsi = trim($request->get('provinsi', ''));
        if ($provinsi === '') {
            return response()->json([]);
        }

        return response()->json(
            WilayahKabupaten::where('provinsi_kode', $provinsi)
                ->orderBy('nama')->get(['kode', 'nama'])
        );
    }

    /**
     * Daftar kecamatan dalam satu kabupaten/kota (?kabupaten=1213).
     */
    public function apiKecamatan(Request $request)
    {
        $kabupaten = trim($request->get('kabupaten', ''));
        if ($kabupaten === '') {
            return response()->json([]);
        }

        return response()->json(
            WilayahKecamatan::where('kabupaten_kode', $kabupaten)
                ->orderBy('nama')->get(['kode', 'nama'])
        );
    }

    /**
     * Daftar desa/kelurahan dalam satu kecamatan (?kecamatan=121301).
     */
    public function apiDesa(Request $request)
    {
        $kecamatan = trim($request->get('kecamatan', ''));
        if ($kecamatan === '') {
            return response()->json([]);
        }

        return response()->json(
            WilayahDesa::where('kecamatan_kode', $kecamatan)
                ->orderBy('nama')->get(['kode', 'nama'])
        );
    }
}
