<?php

// Komponen Livewire: tabel referensi Kabupaten/Kota dengan filter provinsi.

namespace App\Livewire;

use App\Models\WilayahKabupaten;
use App\Models\WilayahProvinsi;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class TabelKabupaten extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $q = '';

    #[Url]
    public string $provinsi = '';

    #[Url]
    public string $sort = 'nama';

    #[Url]
    public string $arah = 'asc';

    private const KOLOM_SORTIR = ['kode', 'nama'];

    public function updatedQ(): void
    {
        $this->resetPage();
    }

    public function updatedProvinsi(): void
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
        $this->reset(['q', 'provinsi', 'sort', 'arah']);
        $this->sort = 'nama';
        $this->arah = 'asc';
        $this->resetPage();
    }

    public function render()
    {
        $query = WilayahKabupaten::with(['provinsi'])->withCount('kecamatan');

        if ($this->provinsi !== '') {
            $query->where('provinsi_kode', $this->provinsi);
        }

        $kataKunci = trim($this->q);
        if ($kataKunci !== '') {
            $query->where(function ($w) use ($kataKunci) {
                $w->where('nama', 'like', "%{$kataKunci}%")
                    ->orWhere('kode', 'like', "%{$kataKunci}%");
            });
        }

        $daftar = $query->orderBy($this->sort, $this->arah)->paginate(20);
        $daftarProvinsi = WilayahProvinsi::orderBy('nama')->get(['kode', 'nama']);

        return view('livewire.tabel-kabupaten', compact('daftar', 'daftarProvinsi'));
    }
}
