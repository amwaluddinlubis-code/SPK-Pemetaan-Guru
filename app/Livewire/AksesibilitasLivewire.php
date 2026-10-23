<?php

// Komponen Livewire: Tabel Aksesibilitas Sekolah — read-only,
// aggregat jarak Haversine per kategori daerah. Filter sinkron ke URL
// via #[Url], setiap filter berubah WAJIB resetPage(), sediakan resetFilter().

namespace App\Livewire;

use App\Models\Guru;
use App\Models\HasilSpk;
use App\Models\KebutuhanGuru;
use App\Models\Sekolah;
use App\Services\AnalisisJarak;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class AksesibilitasLivewire extends Component
{
    use WithPagination;

    #[Url] public int $perPage = 20;
    #[Url] public string $kategori_daerah = '';

    public ?array $data = null;
    public ?array $total = null;

    public function mount(): void
    {
        // Parameters terisi dari #[Url] attribute (sinkron ke query string)
    }

    public function render()
    {
        $analisis = new AnalisisJarak;

        $this->data = $analisis->perKecamatan($this->kategori_daerah ?? null);

        $this->total = [
            'guru' => array_sum(array_column($this->data, 'jumlah')),
        ];

        return view('livewire.aksesibilitas-index', [
            'data' => $this->data,
            'total' => $this->total,
        ]);
    }

    public function resetFilter(): void
    {
        $this->reset(['kategori_daerah']);
        $this->resetPage();
    }
}