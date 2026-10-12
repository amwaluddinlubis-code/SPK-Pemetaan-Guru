{{-- Keadaan kosong: baris tabel atau blok teks.
     Pakai tabel: <x-kosong pesan="Tidak ada data." kolom="5" />
     Pakai blok: <x-kosong pesan="Belum ada data." /> --}}
@props(['pesan' => 'Tidak ada data.', 'kolom' => null])
@if ($kolom)
    <tr><td colspan="{{ $kolom }}" class="text-center opacity-60 py-8">{{ $pesan }}</td></tr>
@else
    <p class="text-center opacity-60 py-8">{{ $pesan }}</p>
@endif
