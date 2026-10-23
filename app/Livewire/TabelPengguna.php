<?php

// Komponen Livewire: tabel Kelola Pengguna dengan pencarian dan filter peran
// — semuanya tanpa reload halaman. Khusus administrator (kabid_ptk).

namespace App\Livewire;

use App\Livewire\Concerns\DenganPerHalaman;
use App\Models\User;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Kelola Pengguna — PetaPenempatan ASN')]
class TabelPengguna extends Component
{
    use DenganPerHalaman, WithPagination;

    #[Url]
    public int $perPage = 15;

    #[Url(as: 'q')]
    public string $q = '';

    #[Url]
    public string $role = '';

    public function updatedQ(): void
    {
        $this->resetPage();
    }

    public function updatedRole(): void
    {
        $this->resetPage();
    }

    public function resetFilter(): void
    {
        $this->reset(['q', 'role']);
        $this->resetPage();
    }

    public function render()
    {
        $query = User::with(['guru', 'sekolah']);

        $kataKunci = trim($this->q);
        if ($kataKunci !== '') {
            $query->where(function ($w) use ($kataKunci) {
                $w->where('name', 'like', "%{$kataKunci}%")
                    ->orWhere('email', 'like', "%{$kataKunci}%");
            });
        }

        if ($this->role !== '' && array_key_exists($this->role, User::ROLES)) {
            $query->where('role', $this->role);
        }

        $daftarUser = $query->orderBy('name')->paginate($this->perPage);

        return view('livewire.tabel-pengguna', compact('daftarUser'));
    }
}
