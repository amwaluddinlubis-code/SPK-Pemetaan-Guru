<?php

// Komponen Livewire: tabel referensi Kecamatan dengan filter berjenjang
// provinsi → kabupaten.

namespace App\Livewire;

use App\Livewire\Concerns\DenganPerHalaman;
use App\Models\WilayahKabupaten;
use App\Models\WilayahKecamatan;
use App\Models\WilayahProvinsi;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class TabelKecamatan extends Component
{
    use DenganPerHalaman, WithPagination;

    #[Url]
    public int $perPage = 20;

    #[Url(as: 'q')]
    public string $q = '';

    #[Url]
    public string $provinsi = '';

    #[Url]
    public string $kabupaten = '';

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
        $this->reset(['kabupaten']);
        $this->resetPage();
    }

    public function updatedKabupaten(): void
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
        $this->reset(['q', 'provinsi', 'kabupaten', 'sort', 'arah']);
        $this->sort = 'nama';
        $this->arah = 'asc';
        $this->resetPage();
    }

    public function render()
    {
        $query = WilayahKecamatan::with(['kabupaten.provinsi'])->withCount('desa');

        if ($this->kabupaten !== '') {
            $query->where('kabupaten_kode', $this->kabupaten);
        } elseif ($this->provinsi !== '') {
            $query->whereHas('kabupaten',
                fn ($w) => $w->where('provinsi_kode', $this->provinsi));
        }

        $kataKunci = trim($this->q);
        if ($kataKunci !== '') {
            $query->where(function ($w) use ($kataKunci) {
                $w->where('nama', 'like', "%{$kataKunci}%")
                    ->orWhere('kode', 'like', "%{$kataKunci}%");
            });
        }

        $daftar = $query->orderBy($this->sort, $this->arah)->paginate($this->perPage);
        $daftarProvinsi = WilayahProvinsi::orderBy('nama')->get(['kode', 'nama']);
        $daftarKabupaten = $this->provinsi === '' ? collect()
            : WilayahKabupaten::where('provinsi_kode', $this->provinsi)
                ->orderBy('nama')->get(['kode', 'nama']);

        return view('livewire.tabel-kecamatan', compact('daftar', 'daftarProvinsi', 'daftarKabupaten'));
    }
}
