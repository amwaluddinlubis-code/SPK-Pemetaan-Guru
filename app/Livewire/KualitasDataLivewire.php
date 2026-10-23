<?php

// Komponen Livewire halaman penuh: Pusat Kualitas Data — read-only,
// ringkasan temuan kualitas data. Filter dampak & jenis sinkron ke URL
// (#[Url]) dan bereaksi tanpa reload; ekspor tetap route controller.

namespace App\Livewire;

use App\Services\PemeriksaKualitasData;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Title('Kualitas Data — PetaPenempatan ASN')]
class KualitasDataLivewire extends Component
{
    #[Url]
    public string $dampak = '';

    #[Url]
    public string $jenis = '';

    public function resetFilter(): void
    {
        $this->reset(['dampak', 'jenis']);
    }

    public function render(PemeriksaKualitasData $pemeriksa)
    {
        // Pemeriksaan penuh dijalankan SEKALI; daftar jenis diturunkan
        // dari hasil yang sama (sebelumnya periksa() dipanggil dua kali).
        $semua = $pemeriksa->periksa();
        $daftarJenis = array_unique(array_column($semua, 'jenis'));
        sort($daftarJenis);

        $temuan = $semua;
        if ($this->dampak !== '') {
            $temuan = array_filter($temuan, fn ($t) => $t['dampak'] === $this->dampak);
        }
        if ($this->jenis !== '') {
            $temuan = array_filter($temuan, fn ($t) => $t['jenis'] === $this->jenis);
        }

        $ringkasan = [
            'tinggi' => count(array_filter($temuan, fn ($t) => $t['dampak'] === 'tinggi')),
            'sedang' => count(array_filter($temuan, fn ($t) => $t['dampak'] === 'sedang')),
            'rendah' => count(array_filter($temuan, fn ($t) => $t['dampak'] === 'rendah')),
        ];

        return view('livewire.kualitas-data-index', [
            'temuan' => array_values($temuan),
            'ringkasan' => $ringkasan,
            'daftarJenis' => $daftarJenis,
            'total' => count($temuan),
        ]);
    }
}
