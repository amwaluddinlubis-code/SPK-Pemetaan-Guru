{{-- Daftar sekolah (filter + tabel Livewire, tanpa reload) --}}
@extends('layouts.app')
@section('judul', 'Data Sekolah — PetaPenempatan ASN')

@section('konten')
    <div class="flex flex-wrap justify-between items-center gap-2 mb-4">
        <h1 class="text-2xl font-bold">🏫 Data Sekolah</h1>
        @if (auth()->user()->hasRole('kabid_ptk', 'kadis'))
            <div class="flex flex-wrap gap-2">
                @if (auth()->user()->hasRole('kabid_ptk'))
                    <a href="{{ route('sekolah.import') }}" class="btn btn-secondary">📥 Import Excel</a>
                @endif
                <a href="{{ route('sekolah.export', request()->only(['q', 'kecamatan'])) }}" class="btn btn-accent">📊 Export Excel</a>
                <a href="{{ route('sekolah.cetak', request()->only(['q', 'kecamatan'])) }}" target="_blank" class="btn btn-neutral">🖨️ Cetak/PDF</a>
                @if (auth()->user()->hasRole('kabid_ptk'))
                    <a href="{{ route('sekolah.create') }}" class="btn btn-primary">+ Tambah Sekolah</a>
                @endif
            </div>
        @endif
    </div>

    {{-- Rincian baris yang gagal saat import terakhir --}}
    @if (session('import_gagal'))
        <div class="alert alert-warning mb-4">
            <div class="text-sm">
                <strong>⚠️ Baris yang gagal diimpor:</strong>
                <ul class="list-disc list-inside mt-1">
                    @foreach (session('import_gagal') as $pesan)
                        <li>{{ $pesan }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    @livewire('tabel-sekolah')
@endsection
