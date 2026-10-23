{{-- Keadaan kosong (Redesign R3): ikon pudar dalam lingkaran lembut +
     pesan — tampil sebagai keadaan yang disengaja, bukan halaman rusak.
     Pakai tabel: <x-kosong pesan="Tidak ada data." kolom="5" />
     Pakai blok: <x-kosong pesan="Belum ada data." /> --}}
@props(['pesan' => 'Tidak ada data.', 'kolom' => null])
@php
    $isiKosong = '<div class="flex flex-col items-center gap-2 py-8 px-4">'
        .'<span class="rounded-full bg-base-200 p-3 opacity-70">'
        .'<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>'
        .'</span>'
        .'<p class="text-sm opacity-60 text-center max-w-md">'.e($pesan).'</p>'
        .'</div>';
@endphp
@if ($kolom)
    <tr><td colspan="{{ $kolom }}" class="p-0">{!! $isiKosong !!}</td></tr>
@else
    {!! $isiKosong !!}
@endif
