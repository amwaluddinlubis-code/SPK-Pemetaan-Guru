{{-- Pengaturan Akses: matriks menu/route per peran (khusus Kabid PTK). --}}
@extends('layouts.app')
@section('judul', 'Pengaturan Akses — PetaPenempatan ASN')

@section('konten')
    <h1 class="text-2xl font-bold mb-4">Pengaturan Akses</h1>

    @livewire('pengaturan-akses')
@endsection
