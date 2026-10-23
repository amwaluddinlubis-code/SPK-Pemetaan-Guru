<?php

// Komponen Livewire: Tabel Data Guru dengan filter, sorting, pagination tanpa reload.
// Sinkronisasi properti ke URL via #[Url]; setara dengan aturan TabelGuru.

namespace App\Livewire;

use App\Models\Guru;
use App\Models\Sekolah;
use App\Livewire\Concerns\DenganPerHalaman;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class TabelGuruLivewire extends Component
{
    use DenganPerHalaman, WithPagination;

    #[Url] public int $perPage = 20;
    #[Url] public string $q = '';
    #[Url] public string $kecamatan = '';

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
        $this->reset(['q', 'kecamatan']);
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
        $query = Guru::with('sekolah');

        if ($user->role === 'guru') {
            $query->where('id', $user->guru_id);
        } elseif ($user->role === 'kepala_sekolah') {
            $query->where('sekolah_id', $user->sekolah_id);
        } elseif ($user->role === 'ketua_pgri') {
            $query->where('kecamatan', $user->kecamatan);
        }
        // kabid_ptk, kadis: semua data

        if ($this->q !== '') {
            $cari = '%'.$this->q.'%';
            $query->where(fn ($q) => $q
                ->where('nama', 'like', $cari)
                ->orWhere('nip', 'like', $cari)
                ->orWhere('mapel', 'like', $cari)
                ->orWhere('domisili_alamat', 'like', $cari)
                ->orWhere('sekolah_saat_ini', 'like', $cari));
        }

        if ($this->kecamatan !== '') {
            $query->where('kecamatan', $this->kecamatan);
        }

        $daftarGuru = $query
            ->orderByRaw("FIELD(status_kepegawaian, 'pns', 'pppk', 'honorer')")
            ->orderBy($this->sort, $this->arah)
            ->paginate($this->perPage);

        $daftarKecamatan = Sekolah::whereNotNull('kecamatan')
            ->distinct()->orderBy('kecamatan')->pluck('kecamatan');

        return view('livewire.guru-index', [
            'daftarGuru' => $daftarGuru,
            'daftarKecamatan' => $daftarKecamatan,
            'sort' => $this->sort,
            'arah' => $this->arah,
        ]);
    }
}