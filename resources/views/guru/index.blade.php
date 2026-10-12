{{-- Daftar guru (filter + tabel Livewire, tanpa reload) --}}
@extends('layouts.app')
@section('judul', 'Data Guru — PetaPenempatan ASN')

@section('konten')
    <x-halaman judul="👩‍🏫 Data Guru">
        <x-slot:aksi>
            @if (auth()->user()->hasRole('kabid_ptk', 'kepala_sekolah', 'kadis'))
                @if (auth()->user()->hasRole('kabid_ptk'))
                    <a href="{{ route('guru.import') }}" class="btn btn-secondary">📥 Import Excel</a>
                @endif
                <a href="{{ route('guru.export', request()->only(['q', 'kecamatan'])) }}" class="btn btn-accent">📊 Export Excel</a>
                <a href="{{ route('guru.cetak', request()->only(['q', 'kecamatan'])) }}" target="_blank" class="btn btn-neutral">🖨️ Cetak/PDF</a>
                @if (auth()->user()->hasRole('kabid_ptk', 'kepala_sekolah'))
                    <a href="{{ route('guru.create') }}" class="btn btn-primary">+ Tambah Guru</a>
                @endif
            @endif
        </x-slot:aksi>
    </x-halaman>

    {{-- Rincian baris yang gagal saat import terakhir --}}
    @if (session('import_gagal'))
        <x-alert tipe="warning" class="mb-4">
            <div class="text-sm">
                <strong>⚠️ Baris yang gagal diimpor:</strong>
                <ul class="list-disc list-inside mt-1">
                    @foreach (session('import_gagal') as $pesan)
                        <li>{{ $pesan }}</li>
                    @endforeach
                </ul>
            </div>
        </x-alert>
    @endif

    @livewire('tabel-guru')
@endsection
