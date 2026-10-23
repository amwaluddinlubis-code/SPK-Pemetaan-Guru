<?php

// Controller Distribusi (B): agregat defisit/surplus — hanya angka agregat,
// tanpa identitas guru/NIP/alamat. Akses: kabid_ptk, kadis, pemda.
// Tabel + filter + ringkasan dirender komponen Livewire TabelDistribusi.

namespace App\Http\Controllers;

class DistribusiController extends Controller
{
    public function index()
    {
        return view('distribusi.index');
    }
}
