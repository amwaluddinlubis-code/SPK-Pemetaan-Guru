<?php

// Komponen Livewire halaman penuh: Dashboard Pemetaan.
// Kartu statistik dihitung di sini; peta & rekomendasi ditangani
// komponen anak PetaRekomendasi (wire:ignore untuk peta Leaflet).

namespace App\Livewire;

use App\Models\Guru;
use App\Models\Sekolah;
use App\Models\UsulanPenempatan;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Dashboard — PetaPenempatan ASN')]
class Dashboard extends Component
{
    public function render()
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

        // Konteks kartu KPI (Redesign R2): komposisi nyata di balik angka
        $guruBerdomisili = $semuaGuru
            ->filter(fn ($guru) => $guru->domisili_lat !== null && $guru->domisili_lng !== null)
            ->count();
        $sekolahPerJenjang = $this->konteksSekolahPerJenjang();

        // Usulan yang masih menunggu diproses (untuk Kabid/Kadis)
        $usulanMenunggu = UsulanPenempatan::whereIn('status', UsulanPenempatan::AKTIF)->count();

        return view('livewire.dashboard', compact(
            'totalGuru',
            'totalSekolah',
            'rataJarak',
            'guruDenganJarak',
            'guruJauh',
            'guruBerdomisili',
            'sekolahPerJenjang',
            'usulanMenunggu'
        ));
    }

    /** Ringkasan sekolah per jenjang untuk konteks KPI, mis. "SD 400 · SMP 81". */
    private function konteksSekolahPerJenjang(): string
    {
        $urutan = ['TK' => 0, 'SD' => 1, 'SMP' => 2, 'SMA' => 3, 'SMK' => 4];
        $perJenjang = Sekolah::query()
            ->selectRaw('jenjang, count(*) as jml')
            ->groupBy('jenjang')
            ->pluck('jml', 'jenjang');

        $ringkasan = $perJenjang
            ->sortBy(fn ($jml, $jenjang) => $urutan[$jenjang] ?? 9)
            ->map(fn ($jml, $jenjang) => $jenjang.' '.number_format((int) $jml))
            ->implode(' · ');

        return $ringkasan !== '' ? $ringkasan : 'terdata di sistem';
    }
}
