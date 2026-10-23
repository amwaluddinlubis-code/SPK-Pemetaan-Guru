<?php

// Komponen Livewire: tabel Data Sekolah dengan pencarian, filter kecamatan,
// sorting kolom, dan pagination — semuanya tanpa reload halaman.

namespace App\Livewire;

use App\Models\Sekolah;
use App\Services\KonteksPemda;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class TabelSekolah extends Component
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

    private const KOLOM_SORTIR = ['nama', 'npsn', 'kecamatan'];

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
        $query = Sekolah::with('kebutuhan');

        if ($user->role === 'kepala_sekolah') {
            $query->where('id', $user->sekolah_id);
        } elseif ($user->role === 'ketua_pgri') {
            $query->where('kecamatan', $user->kecamatan);
        }

        $kataKunci = trim($this->q);
        if ($kataKunci !== '') {
            $query->where(function ($w) use ($kataKunci) {
                $w->where('nama', 'like', "%{$kataKunci}%")
                    ->orWhere('npsn', 'like', "%{$kataKunci}%")
                    ->orWhere('alamat', 'like', "%{$kataKunci}%");
            });
        }

        if ($this->kecamatan !== '') {
            $query->where('kecamatan', $this->kecamatan);
        }

        $daftarSekolah = $query->orderBy($this->sort, $this->arah)->paginate(15);
        $daftarKecamatan = Sekolah::whereNotNull('kecamatan')
            ->distinct()->orderBy('kecamatan')->pluck('kecamatan');

        // Keadaan kosong karena konteks: daftar kosong bukan akibat filter,
        // melainkan karena seluruh data berada di luar konteks Pemda aktif.
        $konteksKosong = KonteksPemda::aktif()
            && $daftarSekolah->total() === 0
            && trim($this->q) === '' && $this->kecamatan === ''
            && Sekolah::withoutGlobalScopes()->count() > 0;
        $namaPemdaKonteks = KonteksPemda::namaPemda();

        return view('livewire.tabel-sekolah', compact('daftarSekolah', 'daftarKecamatan', 'konteksKosong', 'namaPemdaKonteks'));
    }
}
