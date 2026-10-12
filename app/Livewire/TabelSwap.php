<?php

// Komponen Livewire: tabel Mutasi Silang dengan filter status dan pagination
// — semuanya tanpa reload halaman.

namespace App\Livewire;

use App\Models\RekomendasiSwap;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class TabelSwap extends Component
{
    use WithPagination;

    #[Url]
    public string $status = '';

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function resetFilter(): void
    {
        $this->reset(['status']);
        $this->resetPage();
    }

    public function render()
    {
        $user = auth()->user();
        $query = RekomendasiSwap::with([
            'guruA.sekolah', 'guruB.sekolah',
            'sekolahTujuanA', 'sekolahTujuanB', 'diprosesOleh',
        ]);

        if ($user->role === 'guru') {
            $query->where(fn ($q) => $q
                ->where('guru_a_id', $user->guru_id)
                ->orWhere('guru_b_id', $user->guru_id));
        } elseif ($user->role === 'kepala_sekolah') {
            $query->where(fn ($q) => $q
                ->whereHas('guruA', fn ($s) => $s->where('sekolah_id', $user->sekolah_id))
                ->orWhereHas('guruB', fn ($s) => $s->where('sekolah_id', $user->sekolah_id)));
        } elseif ($user->role === 'ketua_pgri') {
            $query->where(fn ($q) => $q
                ->whereHas('guruA', fn ($s) => $s->where('kecamatan', $user->kecamatan))
                ->orWhereHas('guruB', fn ($s) => $s->where('kecamatan', $user->kecamatan)));
        }

        if ($this->status !== '' && array_key_exists($this->status, RekomendasiSwap::STATUS)) {
            $query->where('status', $this->status);
        }

        $swaps = $query
            ->orderByRaw("FIELD(status, 'menunggu', 'disetujui', 'ditolak', 'batal_karena_konflik')")
            ->orderByDesc('total_hemat_km')
            ->paginate(20);

        return view('livewire.tabel-swap', compact('swaps'));
    }
}
