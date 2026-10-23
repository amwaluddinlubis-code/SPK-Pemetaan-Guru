{{-- Daftar sekolah (filter + tabel Livewire, tanpa reload) --}}
@extends('layouts.app')
@section('judul', 'Data Sekolah — PetaPenempatan ASN')

@section('konten')
    <x-halaman judul="🏫 Data Sekolah">
        <x-slot:aksi>
            @if (auth()->user()->hasRole('kabid_ptk', 'kadis'))
                @if (auth()->user()->hasRole('kabid_ptk'))
                    <a href="{{ route('sekolah.import') }}" class="btn btn-secondary">📥 Import Excel</a>
                    <a href="{{ route('sekolah.import-rombel') }}" class="btn btn-secondary">📊 Import Rombel</a>
                    <form method="POST" action="{{ route('sekolah.analisis.massal') }}" class="inline-flex"
                          onsubmit="return confirm('Terapkan analisis kebutuhan guru ke SEMUA sekolah yang rombelnya terisi? Baris kebutuhan lama tiap sekolah akan diganti hasil hitungan rombel × JP.')">
                        @csrf
                        <button type="submit" class="btn btn-secondary">🧮 Terapkan Analisis Massal</button>
                    </form>
                @endif
                <a href="{{ route('sekolah.export', request()->only(['q', 'kecamatan'])) }}" class="btn btn-accent">📊 Export Excel</a>
                <a href="{{ route('sekolah.cetak', request()->only(['q', 'kecamatan'])) }}" target="_blank" class="btn btn-neutral">🖨️ Cetak/PDF</a>
                @if (auth()->user()->hasRole('kabid_ptk'))
                    <a href="{{ route('sekolah.create') }}" class="btn btn-primary">+ Tambah Sekolah</a>
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

    <x-chip-konteks jenis="sekolah" />

    @livewire('tabel-sekolah')
@endsection
