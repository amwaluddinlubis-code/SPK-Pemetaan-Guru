<?php

// Komponen Livewire halaman penuh: Proyeksi kebutuhan guru 1–5 tahun.
//
// KEJUJURAN DATA: aplikasi saat ini hanya menyimpan snapshot kondisi
// terkini (tanpa deret waktu historis jumlah guru/kebutuhan per periode).
// Membuat angka proyeksi dari data yang tidak ada berarti mengarang —
// maka halaman ini menampilkan "data belum cukup" beserta syarat data
// yang dibutuhkan dan metodologi yang akan dipakai setelah data tersedia.

namespace App\Livewire;

use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Proyeksi Kebutuhan — PetaPenempatan ASN')]
class Proyeksi extends Component
{
    public function render()
    {
        // Syarat minimum: minimal 2 titik waktu historis yang valid.
        // Saat ini tidak ada tabel riwayat jumlah guru/kebutuhan per periode.
        return view('livewire.proyeksi', [
            'dataCukup' => false,
            'syarat' => [
                'Deret waktu jumlah guru per sekolah/mapel (minimal 2 periode, idealnya 5 tahun).',
                'Deret waktu kebutuhan formasi per sekolah/mapel.',
                'Data pensiun/mutasi/resign per tahun (arus keluar).',
                'Data rekrutmen CPNS/PPPK per tahun (arus masuk).',
            ],
            'metodologi' => [
                'Metode: ekstrapolasi linier dari tren historis + koreksi arus keluar/masuk.',
                'Satuan: agregat per kecamatan/mapel/jenjang — tidak memproyeksikan individu.',
                'Setiap angka proyeksi wajib mencantumkan: periode, metode, asumsi, dan tingkat ketidakpastian.',
                'Tidak ada klaim AI/akurasi statistik tanpa validasi.',
            ],
        ]);
    }
}
