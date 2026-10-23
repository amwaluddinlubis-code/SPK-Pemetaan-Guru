<?php

// Komponen Livewire halaman penuh: Analisis Jarak & Aksesibilitas —
// read-only, agregat jarak Haversine per kecamatan tanpa identitas guru.
// Filter kategori daerah sinkron ke URL (#[Url]), bereaksi tanpa reload.

namespace App\Livewire;

use App\Services\AnalisisJarak;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Title('Aksesibilitas Sekolah — PetaPenempatan ASN')]
class AksesibilitasLivewire extends Component
{
    #[Url]
    public string $kategori_daerah = '';

    public function resetFilter(): void
    {
        $this->reset(['kategori_daerah']);
    }

    public function render(AnalisisJarak $analisis)
    {
        $data = $analisis->perKecamatan($this->kategori_daerah !== '' ? $this->kategori_daerah : null);

        return view('livewire.aksesibilitas-index', [
            'data' => $data,
            'total_guru' => array_sum(array_column($data, 'jumlah')),
        ]);
    }
}
