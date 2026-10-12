{{-- Daftar akun pengguna (tabel Livewire, tanpa reload) --}}
@extends('layouts.app')
@section('judul', 'Kelola Pengguna — PetaPenempatan ASN')

@section('konten')
    <div class="flex flex-wrap justify-between items-center gap-2 mb-4">
        <h1 class="text-2xl font-bold">👥 Kelola Pengguna</h1>
        <a href="{{ route('pengguna.create') }}" class="btn btn-primary">+ Tambah Pengguna</a>
    </div>

    @if ($errors->any())
        <div class="alert alert-error mb-4">
            <ul class="list-disc list-inside text-sm">
                @foreach ($errors->all() as $pesan)
                    <li>{{ $pesan }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @livewire('tabel-pengguna')
@endsection
