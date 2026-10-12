<?php

// Komponen Livewire: tabel Usulan Penempatan dengan pencarian, filter status,
// sorting kolom, dan pagination — semuanya tanpa reload halaman.

namespace App\Livewire;

use App\Models\UsulanPenempatan;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class TabelUsulan extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $q = '';

    #[Url]
    public string $status = '';

    #[Url]
    public string $sort = 'created_at';

    #[Url]
    public string $arah = 'desc';

    private const KOLOM_SORTIR = ['created_at', 'status'];

    public function updatedQ(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
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
        $this->reset(['q', 'status', 'sort', 'arah']);
        $this->sort = 'created_at';
        $this->arah = 'desc';
        $this->resetPage();
    }

    public function render()
    {
        $user = auth()->user();
        $query = UsulanPenempatan::with(['guru', 'sekolah', 'pengaju']);

        if ($user->role === 'guru') {
            $query->where('guru_id', $user->guru_id);
        } elseif ($user->role === 'kepala_sekolah') {
            $query->whereHas('guru', fn ($q) => $q->where('sekolah_id', $user->sekolah_id));
        } elseif ($user->role === 'ketua_pgri') {
            $query->whereHas('guru', fn ($q) => $q->where('kecamatan', $user->kecamatan));
        }

        $kataKunci = trim($this->q);
        if ($kataKunci !== '') {
            $query->whereHas('guru', function ($w) use ($kataKunci) {
                $w->where('nama', 'like', "%{$kataKunci}%")
                    ->orWhere('nip', 'like', "%{$kataKunci}%");
            });
        }

        if ($this->status !== '' && array_key_exists($this->status, UsulanPenempatan::STATUS)) {
            $query->where('status', $this->status);
        }

        $daftarUsulan = $query->orderBy($this->sort, $this->arah)->paginate(15);

        return view('livewire.tabel-usulan', compact('daftarUsulan'));
    }
}
