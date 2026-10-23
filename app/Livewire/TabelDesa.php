<?php

// Komponen Livewire: tabel referensi Desa/Kelurahan dengan filter berjenjang
// provinsi → kabupaten → kecamatan. Daftar hanya tampil setelah kecamatan
// dipilih (83rb+ baris nasional tidak ditumpahkan sekaligus).

namespace App\Livewire;

use App\Livewire\Concerns\DenganPerHalaman;
use App\Models\WilayahDesa;
use App\Models\WilayahKabupaten;
use App\Models\WilayahKecamatan;
use App\Models\WilayahProvinsi;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class TabelDesa extends Component
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
    public string $kecamatan = '';

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
        $this->reset(['kabupaten', 'kecamatan']);
        $this->resetPage();
    }

    public function updatedKabupaten(): void
    {
        $this->reset(['kecamatan']);
        $this->resetPage();
    }

    public function updatedKecamatan(): void
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
        $this->reset(['q', 'provinsi', 'kabupaten', 'kecamatan', 'sort', 'arah']);
        $this->sort = 'nama';
        $this->arah = 'asc';
        $this->resetPage();
    }

    public function render()
    {
        $daftar = collect();
        $total = 0;

        if ($this->kecamatan !== '') {
            $query = WilayahDesa::with(['kecamatan.kabupaten.provinsi'])
                ->where('kecamatan_kode', $this->kecamatan);

            $kataKunci = trim($this->q);
            if ($kataKunci !== '') {
                $query->where(function ($w) use ($kataKunci) {
                    $w->where('nama', 'like', "%{$kataKunci}%")
                        ->orWhere('kode', 'like', "%{$kataKunci}%");
                });
            }

            $daftar = $query->orderBy($this->sort, $this->arah)->paginate($this->perPage);
            $total = $daftar->total();
        }

        $daftarProvinsi = WilayahProvinsi::orderBy('nama')->get(['kode', 'nama']);
        $daftarKabupaten = $this->provinsi === '' ? collect()
            : WilayahKabupaten::where('provinsi_kode', $this->provinsi)
                ->orderBy('nama')->get(['kode', 'nama']);
        $daftarKecamatan = $this->kabupaten === '' ? collect()
            : WilayahKecamatan::where('kabupaten_kode', $this->kabupaten)
                ->orderBy('nama')->get(['kode', 'nama']);

        return view('livewire.tabel-desa', compact(
            'daftar', 'total', 'daftarProvinsi', 'daftarKabupaten', 'daftarKecamatan'
        ));
    }
}
