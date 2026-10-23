<?php

// Komponen Livewire: Tabel Data Pengguna dengan filter peran, sorting, pagination tanpa reload.

namespace App\Livewire;

use App\Models\User;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class TabelPenggunaLivewire extends Component
{
    use WithPagination;

    #[Url] public int $perPage = 20;
    #[Url] public string $role = '';

    public function render()
    {
        $query = User::query();

        if ($this->role !== '') {
            $query->where('role', $this->role);
        }

        $data = $query->paginate($this->perPage);

        return view('livewire.pengguna-index', [
            'data' => $data,
        ]);
    }

    public function resetFilter(): void
    {
        $this->reset(['role']);
        $this->resetPage();
    }
}