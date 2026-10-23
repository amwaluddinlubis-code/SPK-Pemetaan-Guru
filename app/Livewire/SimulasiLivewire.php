<?php

// Komponen Livewire halaman penuh: Simulasi Kebijakan "What-If" —
// read-only, tidak mengubah data operasional. Parameter disubmit via
// wire:submit dan hasil tampil seketika di halaman yang sama.
// (Route POST /simulasi lama dipertahankan untuk kompatibilitas.)

namespace App\Livewire;

use App\Models\Guru;
use App\Services\SimulasiKebijakan;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Simulasi Kebijakan — PetaPenempatan ASN')]
class SimulasiLivewire extends Component
{
    public float $ambang_km = 15;

    public string $mapel = '';

    public string $kecamatan = '';

    public bool $sertakan_3t = false;

    public ?array $hasil = null;

    public function hitung(SimulasiKebijakan $simulasi): void
    {
        $data = $this->validate([
            'ambang_km' => ['required', 'numeric', 'min:1', 'max:500'],
            'mapel' => ['nullable', 'string', 'max:50'],
            'kecamatan' => ['nullable', 'string', 'max:100'],
            'sertakan_3t' => ['nullable', 'boolean'],
        ]);

        $this->hasil = $simulasi->jalankan($data);
    }

    public function render()
    {
        return view('livewire.simulasi-index', [
            'daftarMapel' => Guru::distinct()->orderBy('mapel')->pluck('mapel'),
            'daftarKecamatan' => Guru::distinct()->orderBy('kecamatan')->pluck('kecamatan'),
            'hasil' => $this->hasil,
        ]);
    }
}
