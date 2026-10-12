<?php

// Komponen Livewire: tabel Data Guru dengan pencarian, filter kecamatan,
// sorting kolom, dan pagination — semuanya tanpa reload halaman.
// Scope peran sama seperti controller (guru diri sendiri, kepala sekolahnya,
// ketua PGRI se-kecamatan, kabid/kadis semua).

namespace App\Livewire;

use App\Models\Guru;
use App\Models\Sekolah;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class TabelGuru extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $q = '';

    #[Url]
    public string $kecamatan = '';

    #[Url]
    public string $sort = 'nama';

    #[Url]
    public string $arah = 'asc';

    private const KOLOM_SORTIR = ['nama', 'nip', 'mapel', 'kecamatan'];

    public function updatedQ(): void
    {
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
        $this->reset(['q', 'kecamatan', 'sort', 'arah']);
        $this->sort = 'nama';
        $this->arah = 'asc';
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

        $kataKunci = trim($this->q);
        if ($kataKunci !== '') {
            $query->where(function ($w) use ($kataKunci) {
                $w->where('nama', 'like', "%{$kataKunci}%")
                    ->orWhere('nip', 'like', "%{$kataKunci}%")
                    ->orWhere('mapel', 'like', "%{$kataKunci}%")
                    ->orWhere('domisili_alamat', 'like', "%{$kataKunci}%")
                    ->orWhere('sekolah_saat_ini', 'like', "%{$kataKunci}%");
            });
        }

        if ($this->kecamatan !== '') {
            $query->where('kecamatan', $this->kecamatan);
        }

        $daftarGuru = $query->orderBy($this->sort, $this->arah)->paginate(15);
        $daftarKecamatan = Sekolah::whereNotNull('kecamatan')
            ->distinct()->orderBy('kecamatan')->pluck('kecamatan');

        return view('livewire.tabel-guru', compact('daftarGuru', 'daftarKecamatan'));
    }
}
