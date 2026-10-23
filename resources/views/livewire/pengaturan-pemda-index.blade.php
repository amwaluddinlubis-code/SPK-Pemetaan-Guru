@extends('layouts.app')

@section('judul', 'Pengaturan Pemda — PetaPenempata ASN')

@section('konten')
<div class="mb-4">
    <h1 class="text-2xl font-bold">Pengaturan Konteks Pemda</h1>
    <p class="text-sm opacity-70 mt-1">Atur konteks wilayah kerja aplikasi — data akan difilter sesuai wilayah yang dipilih.</p>
</div>

<div class="card bg-base-100 shadow p-4 mb-4">
    <form wire:submit.prevent="filter">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            <div>
                <label class="label"><span class="label-text font-semibold">Provinsi</span></label>
                <select wire:model="provinsi" class="select w-full">
                    <option value="">Semua provinsi</option>
                    @foreach (config('app.provinces') ?? ['Jawa Timur', 'Jawa Tengah', 'DKI Jakarta', 'Banten', 'West Java'] as $p)
                        <option value="{{ $p }}" @if($p === $provinsi) selected @endif>
                            {{ $p }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="label"><span class="label-text font-semibold">Kabupaten</span></label>
                <select wire:model="kabupaten" class="select w-full">
                    <option value="">Semua kabupaten</option>
                    @foreach (config('app.regencies') ?? ['Kota', 'Kabupaten'] as $k)
                        <option value="{{ $k }}" @if($k === $kabupaten) selected @endif>
                            {{ $k }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <button type="submit" class="btn btn-primary">Terapkan Konteks</button>
                <a href="#" wire:click="resetFilter" class="btn btn-ghost ml-2">Reset</a>
            </div>
        </div>
    </form>
</div>

@if ($totalSekolah > 0 || $totalGuru > 0)
<div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4">
    <div class="stat bg-base-100 shadow rounded">
        <div class="stat-title">Total Sekolah</div>
        <div class="stat-value">{{ $totalSekolah }}</div>
    </div>
    <div class="stat bg-base-100 shadow rounded">
        <div class="stat-title">Total Guru</div>
        <div class="stat-value">{{ $totalGuru }}</div>
    </div>
</div>
@endif
@endsection