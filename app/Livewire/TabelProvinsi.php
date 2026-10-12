<?php

// Komponen Livewire: tabel referensi Provinsi (pencarian, sortir,
// pagination tanpa reload). Data read-only Kepmendagri 100.1.1-6117/2022.

namespace App\Livewire;

use App\Models\WilayahProvinsi;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class TabelProvinsi extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $q = '';

    #[Url]
    public string $sort = 'nama';

    #[Url]
    public string $arah = 'asc';

    private const KOLOM_SORTIR = ['kode', 'nama'];

    public function updatedQ(): void
    {
        $this->resetPage();
    }

    /**
     * Klik header kolom: ganti kolom atau balik arah.
     */
    public function urutkan(string $kolom): void
    {
        if (! in_array($kolom, self::KOLOM_SORTIR, true)) {
            return;
        }
        if ($this->sort === $kolom) {
            $this->arah = $this->arah === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sort = $kolom;
            $this->arah = 'asc';
        }
        $this->resetPage();
    }

    public function resetFilter(): void
    {
        $this->reset(['q', 'sort', 'arah']);
        $this->sort = 'nama';
        $this->arah = 'asc';
        $this->resetPage();
    }

    public function render()
    {
        $query = WilayahProvinsi::withCount('kabupaten');

        $kataKunci = trim($this->q);
        if ($kataKunci !== '') {
            $query->where(function ($w) use ($kataKunci) {
                $w->where('nama', 'like', "%{$kataKunci}%")
                    ->orWhere('kode', 'like', "%{$kataKunci}%");
            });
        }

        $daftar = $query->orderBy($this->sort, $this->arah)->paginate(20);

        return view('livewire.tabel-provinsi', compact('daftar'));
    }
}
