<?php

// Komponen Livewire: Pengaturan Konteks Pemda — provinsi/kabupaten
// Sinkronisasi ke URL via #[Url]; setara dengan konteks di layout.

namespace App\Livewire;

use App\Models\Sekolah;
use App\Models\Guru;
use Livewire\Attributes\Url;
use Livewire\Component;

class PengaturanPemdaLivewire extends Component
{
    #[Url] public string $provinsi = '';
    #[Url] public string $kabupaten = '';

    public function mount(): void
    {
        // Inisialisasi dari query string / state Livewire
    }

    public function render()
    {
        // Hitung statistik berdasarkan konteks yang dipilih
        $s Sekolah query jika ada provinsi/kabupaten dipilih
        $query = Sekolah::query();

        if ($this->provinsi !== '') {
            $query->whereHas('wilayah', function ($q) {
                $q->where('provinsi', $this->provinsi);
            });
        }
        if ($this->kabupaten !== '') {
            $query->whereHas('wilayah', function ($q) {
                $q->where('kabupaten', $this->kabupaten);
            });
        }

        $totalSekolah = $query->count();
        $totalGuru = Guru::whereHas('sekolah', $query->whereExists())->count();

        return view('livewire.pengaturan-pemda-index', [
            'totalSekolah' => $totalSekolah,
            'totalGuru' => $totalGuru,
            'provinsi' => $this->provinsi,
            'kabupaten' => $this->kabupaten,
        ]);
    }

    public function resetFilter(): void
    {
        $this->reset(['provinsi', 'kabupaten']);
    }
}