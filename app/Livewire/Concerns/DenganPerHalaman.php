<?php

namespace App\Livewire\Concerns;

/**
 * Pemilih jumlah baris per halaman untuk tabel Livewire.
 *
 * Komponen yang memakai trait ini WAJIB mendeklarasikan sendiri propertinya
 * agar nilai bawaan per tabel tetap terjaga:
 *
 *     #[Url]
 *     public int $perPage = 15;
 *
 * lalu memakai ->paginate($this->perPage) pada query-nya. Mengubah pilihan
 * mengembalikan tabel ke halaman pertama; nilai di luar opsi (mis. diubah
 * manual lewat URL) dipaksa kembali ke nilai aman.
 */
trait DenganPerHalaman
{
    /** @var list<int> */
    public const OPSI_PER_HALAMAN = [10, 15, 20, 25, 50, 100];

    public const PER_HALAMAN_AMAN = 15;

    public function mountDenganPerHalaman(): void
    {
        $this->perPage = $this->perPageTervalidasi($this->perPage);
    }

    public function updatedPerPage(): void
    {
        $this->perPage = $this->perPageTervalidasi($this->perPage);
        $this->resetPage();
    }

    protected function perPageTervalidasi(int $nilai): int
    {
        return in_array($nilai, self::OPSI_PER_HALAMAN, true) ? $nilai : self::PER_HALAMAN_AMAN;
    }
}
