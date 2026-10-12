{{-- Referensi Desa/Kelurahan (tabel Livewire, tanpa reload) --}}
@extends('layouts.app')
@section('judul', 'Referensi Desa/Kelurahan — PetaPenempatan ASN')

@section('konten')
    <x-halaman judul="🏡 Referensi Desa/Kelurahan">
        <x-slot:deskripsi>83.761 desa/kelurahan se-Indonesia.</x-slot:deskripsi>
    </x-halaman>

    @livewire('tabel-desa')
@endsection
