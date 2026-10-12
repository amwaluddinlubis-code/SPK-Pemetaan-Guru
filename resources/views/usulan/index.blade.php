{{-- Daftar usulan penempatan guru (tabel Livewire, tanpa reload) --}}
@extends('layouts.app')
@section('judul', 'Usulan Penempatan — PetaPenempatan ASN')

@section('konten')
    <x-halaman judul="📝 Usulan Penempatan">
        <x-slot:aksi>
            @if (auth()->user()->hasRole('kabid_ptk', 'kadis'))
                <a href="{{ route('usulan.export', request()->only(['q', 'status'])) }}" class="btn btn-accent">📊 Export Excel</a>
                <a href="{{ route('usulan.cetak', request()->only(['q', 'status'])) }}" target="_blank" class="btn btn-neutral">🖨️ Cetak/PDF</a>
            @endif
            @if (auth()->user()->hasRole('guru', 'kepala_sekolah', 'kabid_ptk'))
                <a href="{{ route('usulan.create') }}" class="btn btn-primary">+ Ajukan Usulan</a>
            @endif
        </x-slot:aksi>
    </x-halaman>

    @livewire('tabel-usulan')
@endsection
