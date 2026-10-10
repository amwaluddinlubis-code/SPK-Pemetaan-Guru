<?php

// Controller Dashboard: hanya menampilkan halaman utama.
// Peta & rekomendasi ditangani komponen Livewire PetaRekomendasi.

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\Sekolah;
use App\Models\UsulanPenempatan;

class DashboardController extends Controller
{
    public function index()
    {
        // Angka ringkasan untuk kartu statistik
        $totalGuru = Guru::count();
        $totalSekolah = Sekolah::count();

        // Hitung rata-rata jarak domisili → sekolah saat ini
        // MAP-001: lewat relasi sekolah (sumber kebenaran tunggal)
        $semuaGuru = Guru::with('sekolah')->get();
        $rataJarak = $semuaGuru->isNotEmpty()
            ? $semuaGuru->avg(fn ($g) => $g->jarakKeSekolahSaatIni() ?? 0)
            : 0;

        // Guru yang jaraknya > 5 km (butuh perhatian)
        $guruJauh = $semuaGuru->filter(
            fn ($g) => ($g->jarakKeSekolahSaatIni() ?? 0) > 5
        )->count();

        // Usulan yang masih menunggu diproses (untuk Kabid/Kadis)
        $usulanMenunggu = UsulanPenempatan::whereIn('status', UsulanPenempatan::AKTIF)->count();

        return view('dashboard', compact('totalGuru', 'totalSekolah', 'rataJarak', 'guruJauh', 'usulanMenunggu'));
    }
}
