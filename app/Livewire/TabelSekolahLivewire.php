<?php

// Komponen Livewire: Tabel Data Sekolah dengan filter, sorting, pagination tanpa reload.
// Sinkronisasi properti ke URL via #[Url]; setiap filter berubah WAJIB resetPage(), sediakan resetFilter().

namespace App\Livewire;

use App\Models\Guru;
use App\Models\KebutuhanGuru;
use App\Models\Sekolah;
use App\Livewire\Concerns\DenganPerHalaman;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class TabelSekolahLivewire extends Component
{
    use DenganPerHalaman, WithPagination;

    #[Url] public int $perPage = 20;
    #[Url] public string $q = '';
    #[Url] public string $kecamatan = '';
    #[Url] public string $status = '';

    public string $arah = 'asc';
    public string $sort = 'nama';

    public function updatedQ(): void
    {
        $this->resetPage();
    }

    public function updatedKececamatan(): void
    {
        $this->resetPage();
    }

    public function resetFilter(): void
    {
        $this->reset(['q', 'kecamatan', 'status']);
        $this->resetPage();
    }

    public function urutkan(string $kolom): void
    {
        if ($this->sort === $kolom) {
            $this->arah = $this->arah === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sort = $kolom;
            $this->arah = 'asc';
        }
        $this->resetPage();
    }

    public function render()
    {
        $user = auth()->user();
        $query = Sekolah::with(['kebutuhan' => function ($q) {
            $q->whereNotNull('mapel');
        }]);

        if ($user->role === 'kepala_sekolah') {
            $query->where('kepala_sekolah_id', $user->id); // asum ada relasi
        }
        // kabid_ptk, kadis, guru: semua data (dikontrol di controller/scope)

        if ($this->q !== '') {
            $cari = '%'.$this->q.'%';
            $query->where(function ($w) use ($cari) {
                $w->where('nama', 'like', $cari)
                    ->orWhere('npsn', 'like', $cari)
                    ->orWhere('alamat', 'like', $cari);
            });
        }

        if ($this->kecamatan !== '') {
            $query->where('kecamatan', $this->kecamatan);
        }

        if ($this->status !== '') {
            $query->where('status', $this->status);
        }

        $daftarSekolah = $query
            ->orderByRaw("FIELD(status, 'aktif', 'nonaktif', 'draf')")
            ->orderBy($this->sort, $this->arah)
            ->paginate($this->perPage);

        $daftarKecamatan = Sekolah::whereNotNull('kecamatan')
            ->distinct()->orderBy('kecamatan')->pluck('kecamatan');

        return view('livewire.sekolah-index', [
            'daftarSekolah' => $daftarSekolah,
            'daftarKecamatan' => $daftarKecamatan,
            'sort' => $this->sort,
            'arah' => $this->arah,
        ]);
    }
}