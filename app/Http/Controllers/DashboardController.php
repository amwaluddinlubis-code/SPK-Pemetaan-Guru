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
        $jarakValid = $semuaGuru
            ->map(fn ($guru) => $guru->jarakKeSekolahSaatIni())
            ->filter(fn ($jarak) => $jarak !== null)
            ->values();

        // Jangan mengubah koordinat yang hilang menjadi 0 km: itu akan
        // mengecilkan rata-rata dan menyembunyikan data geografis yang belum lengkap.
        $rataJarak = $jarakValid->isNotEmpty() ? $jarakValid->avg() : null;
        $guruDenganJarak = $jarakValid->count();

        // Guru yang jaraknya > 5 km (hanya dari jarak yang valid)
        $guruJauh = $jarakValid->filter(fn ($jarak) => $jarak > 5)->count();

        // Usulan yang masih menunggu diproses (untuk Kabid/Kadis)
        $usulanMenunggu = UsulanPenempatan::whereIn('status', UsulanPenempatan::AKTIF)->count();

        return view('dashboard', compact(
            'totalGuru',
            'totalSekolah',
            'rataJarak',
            'guruDenganJarak',
            'guruJauh',
            'usulanMenunggu'
        ));
    }
}
