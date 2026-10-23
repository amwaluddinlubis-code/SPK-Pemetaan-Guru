@extends('layouts.app')

@section('judul', 'Aksesibilitas Sekolah — PetaPenempatan ASN')

@section('konten')
<div class="mb-4">
    <h1 class="text-2xl font-bold">Analisis Jarak & Aksesibilitas</h1>
    <p class="text-sm opacity-70 mt-1">
        Jarak dihitung dengan <strong>Haversine (garis lurus)</strong>, konsisten dengan mesin SPK.
        Ini <strong>bukan</strong> waktu tempuh perjalanan darat — jangan gunakan sebagai dasar klaim waktu tempuh.
        Data agregat per kecamatan; tanpa identitas guru.
    </p>
</div>

<!-- Filter interaktif via Livewire -->
<div class="card bg-base-100 shadow p-4 mb-4">
    <form wire:submit.prevent="filter">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4">
            <div>
                <label class="label"><span class="label-text font-semibold">Kategori Daerah</span></label>
                <select wire:model="kategori_daerah" class="select w-full">
                    <option value="">Semua kategori daerah</option>
                    @foreach (['biasa', 'terpencil', 'sangat_terpencil'] as $k)
                        <option value="{{ $k }}" @if($k === $kategori_daerah) selected @endif>
                            {{ ucfirst(str_replace('_', ' ', $k)) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <button type="submit" class="btn btn-primary">Terapkan Filter</button>
                <a href="#" wire:click="resetFilter" class="btn btn-ghost ml-2">Reset</a>
            </div>
        </div>
    </form>
</div>

@if ($data)
<div class="card bg-base-100 shadow overflow-x-auto">
    <table class="table table-zebra table-sm">
        <thead>
            <tr><th>Kecamatan</th><th>Kategori Daerah</th><th class="text-right">Guru</th><th class="text-right">Rata-rata (km)</th><th class="text-right">Maks (km)</th><th class="text-right">> 15 km</th></tr>
        </thead>
        <tbody>
            @forelse ($data as $d)
                <tr>
                    <td>{{ $d['kecamatan'] ?: '—' }}</td>
                    <td>{{ ucfirst(str_replace('_', ' ', $d['kategori_daerah'] ?? 'biasa')) }}</td>
                    <td class="text-right">{{ $d['jumlah'] }}</td>
                    <td class="text-right">{{ $d['rata_km'] }}</td>
                    <td class="text-right">{{ $d['maks_km'] }}</td>
                    <td class="text-right {{ $d['di_atas_15km'] > 0 ? 'text-warning font-bold' : '' }}">{{ $d['di_atas_15km'] }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center py-8 opacity-60">Tidak ada data jarak yang valid.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4">
    <div class="stat bg-base-100 shadow rounded">
        <div class="stat-title">Total Guru</div>
        <div class="stat-value">{{ $total['guru'] }}</div>
    </div>
</div>
@endif
@endsection