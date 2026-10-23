<?php

// Komponen Livewire: Pusat Kualitas Data — read-only, ringkasan temuan kualitas data.
// Filter: dampak & jenis. Ekspor: tanpa PII berlebih.

namespace App\Livewire;

use App\Models\Guru;
use App\Models\KebutuhanGuru;
use App\Models\Sekolah;
use App\Services\PemeriksaKualitasData;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class KualitasDataLivewire extends Component
{
    use WithPagination;

    #[Url] public int $perPage = 20;
    #[Url] public string $dampak = '';
    #[Url] public string $jenis = '';

    public ?array $temuan = null;
    public ?array $ringkasan = null;
    public ?array $daftarJenis = null;
    public ?int $total = null;

    public function mount(): void
    {
        // Parameters terisi dari #[Url] attribute (sinkron ke query string)
    }

    public function render()
    {
        $pemeriksa = new PemeriksaKualitasData;
        $semua = $pemeriksa->periksa();

        $daftarJenis = array_unique(array_column($semua, 'jenis'));
        sort($daftarJenis);

        $this->daftarJenis = $daftarJenis;

        // Apply filters from Livewire state
        $this->temuan = $semua;
        if ($this->filled('dampak')) {
            $this->temuan = array_filter($this->temuan, fn ($t) => $t['dampak'] === $this->dampak);
        }
        if ($this->filled('jenis')) {
            $this->temuan = array_filter($this->temuan, fn ($t) => $t['jenis'] === $this->jenis);
        }

        $ringkasan = [
            'tinggi' => count(array_filter($this->temuan, fn ($t) => $t['dampak'] === 'tinggi')),
            'sedang' => count(array_filter($this->temuan, fn ($t) => $t['dampak'] === 'sedang')),
            'rendah' => count(array_filter($this->temuan, fn ($t) => $t['dampak'] === 'rendah')),
        ];
        $this->ringkasan = $ringkasan;
        $this->total = count($this->temuan);

        return view('livewire.kualitas-data-index', [
            'temuan' => array_values($this->temuan),
            'ringkasan' => $this->ringkasan,
            'daftarJenis' => $this->daftarJenis,
            'total' => $this->total,
        ]);
    }

    public function resetFilter(): void
    {
        $this->reset(['dampak', 'jenis']);
        $this->resetPage();
    }
}