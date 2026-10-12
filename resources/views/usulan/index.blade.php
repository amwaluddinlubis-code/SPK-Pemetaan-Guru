{{-- Daftar usulan penempatan guru (tabel Livewire, tanpa reload) --}}
@extends('layouts.app')
@section('judul', 'Usulan Penempatan — PetaPenempatan ASN')

@section('konten')
    <div class="flex flex-wrap justify-between items-center gap-2 mb-4">
        <h1 class="text-2xl font-bold">📝 Usulan Penempatan</h1>
        <div class="flex flex-wrap gap-2">
            @if (auth()->user()->hasRole('kabid_ptk', 'kadis'))
                <a href="{{ route('usulan.export', request()->only(['q', 'status'])) }}" class="btn btn-accent">📊 Export Excel</a>
                <a href="{{ route('usulan.cetak', request()->only(['q', 'status'])) }}" target="_blank" class="btn btn-neutral">🖨️ Cetak/PDF</a>
            @endif
            @if (auth()->user()->hasRole('guru', 'kepala_sekolah', 'kabid_ptk'))
                <a href="{{ route('usulan.create') }}" class="btn btn-primary">+ Ajukan Usulan</a>
            @endif
        </div>
    </div>

    @livewire('tabel-usulan')
@endsection
