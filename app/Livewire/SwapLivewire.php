<?php

// Komponen Livewire: Tabel Hasil Swap Mutasi Silang dengan filter status, pagination tanpa reload.

namespace App\Livewire;

use App\Models\RekomendasiSwap;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class SwapLivewire extends Component
{
    use WithPagination;

    #[Url] public int $perPage = 20;
    #[Url] public string $status = '';

    public function resetFilter(): void
    {
        $this->reset(['status']);
        $this->resetPage();
    }
}