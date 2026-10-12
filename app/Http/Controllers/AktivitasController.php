<?php

// Controller Aktivitas: baca jejak audit operasional (append-only).
// Hanya kabid_ptk. Filter: rentang waktu, aktor, tipe aksi, hasil.

namespace App\Http\Controllers;

class AktivitasController extends Controller
{
    /**
     * Halaman index dirender komponen Livewire TabelAktivitas
     * (filter aksi/aktor/hasil/tanggal, pagination tanpa reload).
     */
    public function index()
    {
        return view('aktivitas.index');
    }
}
