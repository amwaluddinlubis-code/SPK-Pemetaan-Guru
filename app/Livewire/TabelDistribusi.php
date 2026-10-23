<?php

// Komponen Livewire: tabel Distribusi defisit/surplus per kecamatan x mapel.
// Data agregat dari AgregatKebutuhan (tanpa identitas guru). Filter kecamatan
// & mapel tersinkron ke URL; pagination memakai tema daisyUI seperti tabel lain.

namespace App\Livewire;

use App\Livewire\Concerns\DenganPerHalaman;
use App\Services\AgregatKebutuhan;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Distribusi Defisit & Surplus — PetaPenempatan ASN')]
class TabelDistribusi extends Component
{
    use DenganPerHalaman, WithPagination;

    #[Url]
    public int $perPage = 20;

    #[Url]
    public string $kecamatan = '';

    #[Url]
    public string $mapel = '';

    #[Url]
    public string $sort = 'kecamatan';

    #[Url]
    public string $arah = 'asc';

    private const KOLOM_SORTIR = ['kecamatan', 'mapel', 'tersedia', 'kebutuhan', 'defisit', 'surplus', 'dalam_proses'];

    public function updatedKecamatan(): void
    {
        $this->resetPage();
    }

    public function updatedMapel(): void
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
        $this->reset(['kecamatan', 'mapel', 'sort', 'arah']);
        $this->sort = 'kecamatan';
        $this->arah = 'asc';
        $this->resetPage();
    }

    public function render(AgregatKebutuhan $agregat)
    {
        $semua = collect($agregat->perKecamatanMapel(
            $this->kecamatan !== '' ? $this->kecamatan : null,
            $this->mapel !== '' ? $this->mapel : null,
        ));

        // Kartu ringkasan mengikuti filter aktif (dari seluruh baris, bukan per halaman).
        $total = [
            'tersedia' => (int) $semua->sum('tersedia'),
            'kebutuhan' => (int) $semua->sum('kebutuhan'),
            'defisit' => (int) $semua->sum('defisit'),
            'surplus' => (int) $semua->sum('surplus'),
            'dalam_proses' => (int) $semua->sum('dalam_proses'),
        ];

        $kolom = $this->sort;
        $terurut = $semua->sort(function (array $a, array $b) use ($kolom) {
            $cmp = is_numeric($a[$kolom]) && is_numeric($b[$kolom])
                ? $a[$kolom] <=> $b[$kolom]
                : strcasecmp((string) $a[$kolom], (string) $b[$kolom]);
            if ($cmp === 0) {
                $cmp = strcasecmp($a['kecamatan'].'|'.$a['mapel'], $b['kecamatan'].'|'.$b['mapel']);
            }

            return $this->arah === 'asc' ? $cmp : -$cmp;
        })->values();

        $daftar = new LengthAwarePaginator(
            $terurut->forPage($this->getPage(), $this->perPage)->values(),
            $terurut->count(),
            $this->perPage,
            $this->getPage(),
            ['path' => request()->url()],
        );

        $filter = $agregat->daftarFilter();

        return view('livewire.tabel-distribusi', [
            'daftar' => $daftar,
            'total' => $total,
            'opsiKecamatan' => collect($filter['kecamatan'])->map(fn ($k) => ['value' => $k, 'label' => $k])->all(),
            'opsiMapel' => collect($filter['mapel'])->map(fn ($m) => ['value' => $m, 'label' => $m])->all(),
        ]);
    }
}
