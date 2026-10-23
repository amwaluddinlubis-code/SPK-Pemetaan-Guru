<?php

// Komponen Livewire: tabel Jejak Audit dengan filter aksi/aktor/hasil/tanggal
// dan pagination — semuanya tanpa reload halaman. Khusus kabid_ptk.

namespace App\Livewire;

use App\Livewire\Concerns\DenganPerHalaman;
use App\Models\ActivityLog;
use App\Models\User;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Jejak Audit — PetaPenempatan ASN')]
class TabelAktivitas extends Component
{
    use DenganPerHalaman, WithPagination;

    #[Url]
    public int $perPage = 20;

    #[Url]
    public string $aksi = '';

    #[Url]
    public string $user_id = '';

    #[Url]
    public string $hasil = '';

    #[Url]
    public string $dari = '';

    #[Url]
    public string $sampai = '';

    public function updatedAksi(): void
    {
        $this->resetPage();
    }

    public function updatedUserId(): void
    {
        $this->resetPage();
    }

    public function updatedHasil(): void
    {
        $this->resetPage();
    }

    public function updatedDari(): void
    {
        $this->resetPage();
    }

    public function updatedSampai(): void
    {
        $this->resetPage();
    }

    public function resetFilter(): void
    {
        $this->reset(['aksi', 'user_id', 'hasil', 'dari', 'sampai']);
        $this->resetPage();
    }

    public function render()
    {
        $query = ActivityLog::with('user')->orderByDesc('waktu');

        if ($this->aksi !== '') {
            $query->where('aksi', $this->aksi);
        }
        if ($this->user_id !== '') {
            $query->where('user_id', $this->user_id);
        }
        if ($this->hasil !== '') {
            $query->where('hasil', $this->hasil);
        }
        if ($this->dari !== '') {
            $query->whereDate('waktu', '>=', $this->dari);
        }
        if ($this->sampai !== '') {
            $query->whereDate('waktu', '<=', $this->sampai);
        }

        $log = $query->paginate($this->perPage);
        $daftarAksi = ActivityLog::distinct()->orderBy('aksi')->pluck('aksi');
        $daftarUser = User::orderBy('name')->get(['id', 'name', 'role']);

        return view('livewire.tabel-aktivitas', compact('log', 'daftarAksi', 'daftarUser'));
    }
}
