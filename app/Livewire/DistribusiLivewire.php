<?php

// Komponen Livewire: Tabel Distribusi Defisit & Surplus — read-only,
// aggregat per kecamatan x mapel. Filter sinkron ke URL via #[Url],
// setiap filter berubah WAJIB resetPage(), sediakan resetFilter().

namespace App\Livewire;

use App\Models\Guru;
use App\Models\HasilSpk;
use App\Models\KebutuhanGuru;
use App\Models\Sekolah;
use App\Services\AgregatKebutuhan;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class DistribusiLivewire extends Component
{
    use WithPagination;

    #[Url] public int $perPage = 20;
    #[Url] public string $kecamatan = '';
    #[Url] public string $mapel = '';

    public ?array $data = null;
    public ?array $filter = null;
    public ?array $total = null;

    public function mount(): void
    {
        // Parameters terisi dari #[Url] attribute (sinkron ke query string)
    }

    public function render()
    {
        $agregat = new AgregatKebutuhan;

        $this->data = $agregat->perKecamatanMapel(
            $this->kecamatan ?: null,
            $this->mapel
        );

        $this->filter = $agregat->daftarFilter();

        $this->total = [
            'tersedia' => array_sum(array_column($this->data, 'tersedia')),
            'kebutuhan' => array_sum(array_column($this->data, 'kebutuhan')),
            'defisit' => array_sum(array_column($this->data, 'defisit')),
            'surplus' => array_sum(array_column($this->data, 'surplus')),
            'dalam_proses' => array_sum(array_column($this->data, 'dalam_proses')),
        ];

        return view('livewire.distribusi-index', [
            'data' => $this->data,
            'filter' => $this->filter,
            'total' => $this->total,
        ]);
    }

    public function resetFilter(): void
    {
        $this->reset(['kecamatan', 'mapel']);
        $this->resetPage();
    }
}