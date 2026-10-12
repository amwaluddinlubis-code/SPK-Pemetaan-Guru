{{-- Referensi Kabupaten/Kota (tabel Livewire, tanpa reload) --}}
@extends('layouts.app')
@section('judul', 'Referensi Kabupaten/Kota — PetaPenempatan ASN')

@section('konten')
    <x-halaman judul="🏙️ Referensi Kabupaten/Kota">
        <x-slot:deskripsi>514 kabupaten/kota se-Indonesia. Klik nama untuk melihat kecamatan.</x-slot:deskripsi>
    </x-halaman>

    @livewire('tabel-kabupaten')
@endsection
