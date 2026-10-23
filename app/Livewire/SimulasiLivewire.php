<?php

// Komponen Livewire: Halaman Simulasi "What-If" Policy — read-only,
// interaktif tanpa reload halaman. Parameter (ambang, mapel, kecamatan,
// sertakan_3t) disinkronkan ke URL via #[Url] dan state Livewire.

namespace App\Livewire;

use App\Models\Guru;
use App\Models\HasilSpk;
use App\Models\KebutuhanGuru;
use App\Models\Sekolah;
use App\Services\SimulasiKebijakan;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class SimulasiLivewire extends Component
{
    use WithPagination;

    #[Url]
    public int $perPage = 15;

    #[Url]
    public ?string $ambang_km = null;

    #[Url]
    public ?string $mapel = null;

    #[Url]
    public ?string $kecamatan = null;

    #[Url]
    public ?bool $sertakan_3t = null;

    public ?array $hasil = null;

    public function mount(): void
    {
        // Inisialisasi dari query string jika ada (akan terisi oleh #[Url] attribute)
    }

    public function render()
    {
        $this->hasil = null; // reset per render

        // Ambil parameter dari query string / state Livewire
        $param = [
            'ambang_km' => $this->ambang_km ?? 15,
            'mapel' => $this->mapel,
            'kecamatan' => $this->kecamatan,
            'sertakan_3t' => $this->sertikan_3t ?? false,
        ];

        // Jalankan simulasi via service
        $service = new SimulasiKebijakan;
        $this->hasil = $service->jalankan($param);

        return view('livewire.simulasi-index', [
            'hasil' => $this->hasil,
        ]);
    }
}