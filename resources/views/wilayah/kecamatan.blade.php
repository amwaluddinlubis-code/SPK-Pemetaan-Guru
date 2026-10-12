{{-- Referensi Kecamatan (tabel Livewire, tanpa reload) --}}
@extends('layouts.app')
@section('judul', 'Referensi Kecamatan — PetaPenempatan ASN')

@section('konten')
    <x-halaman judul="🏘️ Referensi Kecamatan">
        <x-slot:deskripsi>7.277 kecamatan se-Indonesia. Klik nama untuk melihat desa/kelurahan.</x-slot:deskripsi>
    </x-halaman>

    @livewire('tabel-kecamatan')
@endsection
