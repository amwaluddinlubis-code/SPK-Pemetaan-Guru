{{-- Halaman Dashboard: kartu statistik + komponen peta Livewire --}}
@extends('layouts.app')

@section('judul', 'Dashboard — PetaPenempatan ASN')

{{-- Leaflet hanya dibutuhkan di halaman ini --}}
@push('kepala')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
@endpush
@push('skrip')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
@endpush

@section('konten')
    <h1 class="text-2xl font-bold mb-4">🗺️ Dashboard Pemetaan</h1>

    {{-- Kartu statistik (daisyUI stats) --}}
    <div class="stats stats-vertical sm:stats-horizontal shadow w-full mb-6">
        <div class="stat">
            <div class="stat-title">Total Guru</div>
            <div class="stat-value text-primary">{{ $totalGuru }}</div>
            <div class="stat-desc">terdata di sistem</div>
        </div>
        <div class="stat">
            <div class="stat-title">Total Sekolah</div>
            <div class="stat-value text-secondary">{{ $totalSekolah }}</div>
            <div class="stat-desc">terdata di sistem</div>
        </div>
        <div class="stat">
            <div class="stat-title">Rata-rata Jarak</div>
            <div class="stat-value">{{ number_format($rataJarak, 1) }} km</div>
            <div class="stat-desc">rumah → sekolah saat ini</div>
        </div>
        <div class="stat">
            <div class="stat-title">Butuh Perhatian ⚠️</div>
            <div class="stat-value text-error">{{ $guruJauh }}</div>
            <div class="stat-desc">guru berjarak &gt; 5 km</div>
        </div>
        @if (auth()->user()->hasRole('kabid_ptk', 'kadis'))
            <div class="stat">
                <div class="stat-title">Usulan Menunggu 📝</div>
                <div class="stat-value text-warning">{{ $usulanMenunggu }}</div>
                <div class="stat-desc"><a href="{{ route('usulan.index') }}" class="link">proses sekarang</a></div>
            </div>
        @endif
    </div>

    {{-- Komponen Livewire: peta + rekomendasi (interaktif tanpa reload) --}}
    @livewire('peta-rekomendasi')
@endsection
