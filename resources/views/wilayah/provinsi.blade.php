{{-- Referensi Provinsi (tabel Livewire, tanpa reload) --}}
@extends('layouts.app')
@section('judul', 'Referensi Provinsi — PetaPenempatan ASN')

@section('konten')
    <x-halaman judul="🗺️ Referensi Provinsi">
        <x-slot:deskripsi>37 provinsi se-Indonesia (Kepmendagri 100.1.1-6117/2022). Klik nama untuk melihat kabupaten/kota.</x-slot:deskripsi>
    </x-halaman>

    @livewire('tabel-provinsi')
@endsection
