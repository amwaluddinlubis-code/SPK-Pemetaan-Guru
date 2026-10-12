{{-- Daftar akun pengguna (tabel Livewire, tanpa reload) --}}
@extends('layouts.app')
@section('judul', 'Kelola Pengguna — PetaPenempatan ASN')

@section('konten')
    <x-halaman judul="👥 Kelola Pengguna">
        <x-slot:aksi>
            <a href="{{ route('pengguna.create') }}" class="btn btn-primary">+ Tambah Pengguna</a>
        </x-slot:aksi>
    </x-halaman>

    <x-galat />

    @livewire('tabel-pengguna')
@endsection
