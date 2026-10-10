<?php
// Controller Dashboard: hanya menampilkan halaman utama.
// Peta & rekomendasi ditangani komponen Livewire PetaRekomendasi.

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\Sekolah;

class DashboardController extends Controller
{
    public function index()
    {
        // Angka ringkasan untuk kartu statistik
        $totalGuru    = Guru::count();
        $totalSekolah = Sekolah::count();

        // Hitung rata-rata jarak domisili → sekolah saat ini
        $semuaGuru = Guru::all();
        $rataJarak = $semuaGuru->isNotEmpty()
            ? $semuaGuru->avg(fn ($g) => $g->jarakKe(
                (float) $g->sekolah_saat_ini_lat,
                (float) $g->sekolah_saat_ini_lng
              ))
            : 0;

        // Guru yang jaraknya > 5 km (butuh perhatian)
        $guruJauh = $semuaGuru->filter(fn ($g) => $g->jarakKe(
            (float) $g->sekolah_saat_ini_lat,
            (float) $g->sekolah_saat_ini_lng
        ) > 5)->count();

        return view('dashboard', compact('totalGuru', 'totalSekolah', 'rataJarak', 'guruJauh'));
    }
}
