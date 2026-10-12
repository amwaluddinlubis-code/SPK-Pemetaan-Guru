@extends('layouts.app')

@section('judul', 'Distribusi Defisit & Surplus — PetaPenempatan ASN')

@section('konten')
<div class="mb-4">
    <h1 class="text-2xl font-bold">Distribusi Defisit & Surplus Guru</h1>
    <p class="text-sm opacity-70 mt-1">
        Rumus: <code>defisit = max(kebutuhan − tersedia, 0)</code>,
        <code>surplus = max(tersedia − kebutuhan, 0)</code>.
        Guru dalam proses mutasi (rekomendasi aktif) dihitung sekali di sekolah asal.
        Hanya data agregat — tanpa identitas guru.
    </p>
</div>

<div class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-4">
    <div class="stat bg-base-100 shadow rounded"><div class="stat-title">Tersedia</div><div class="stat-value">{{ $total['tersedia'] }}</div></div>
    <div class="stat bg-base-100 shadow rounded"><div class="stat-title">Kebutuhan</div><div class="stat-value">{{ $total['kebutuhan'] }}</div></div>
    <div class="stat bg-base-100 shadow rounded"><div class="stat-title">Defisit</div><div class="stat-value text-error">{{ $total['defisit'] }}</div></div>
    <div class="stat bg-base-100 shadow rounded"><div class="stat-title">Surplus</div><div class="stat-value text-success">{{ $total['surplus'] }}</div></div>
    <div class="stat bg-base-100 shadow rounded"><div class="stat-title">Dalam Proses</div><div class="stat-value text-warning">{{ $total['dalam_proses'] }}</div></div>
</div>

<form method="GET" action="{{ route('distribusi.index') }}" class="flex gap-2 mb-4">
    <select name="kecamatan" class="select select-sm">
        <option value="">Semua kecamatan</option>
        @foreach ($filter['kecamatan'] as $k)
            <option value="{{ $k }}" @selected(request('kecamatan') === $k)>{{ $k }}</option>
        @endforeach
    </select>
    <select name="mapel" class="select select-sm">
        <option value="">Semua mapel</option>
        @foreach ($filter['mapel'] as $m)
            <option value="{{ $m }}" @selected(request('mapel') === $m)>{{ $m }}</option>
        @endforeach
    </select>
    <button type="submit" class="btn btn-primary btn-sm">Filter</button>
    <a href="{{ route('distribusi.index') }}" class="btn btn-ghost btn-sm">Reset</a>
</form>

<div class="card bg-base-100 shadow overflow-x-auto">
    <table class="table table-zebra table-sm">
        <thead>
            <tr><th>Kecamatan</th><th>Mapel</th><th class="text-right">Tersedia</th><th class="text-right">Kebutuhan</th><th class="text-right">Defisit</th><th class="text-right">Surplus</th><th class="text-right">Dalam Proses</th></tr>
        </thead>
        <tbody>
            @forelse ($data as $d)
                <tr>
                    <td>{{ $d['kecamatan'] ?: '—' }}</td>
                    <td>{{ $d['mapel'] }}</td>
                    <td class="text-right">{{ $d['tersedia'] }}</td>
                    <td class="text-right">{{ $d['kebutuhan'] }}</td>
                    <td class="text-right {{ $d['defisit'] > 0 ? 'text-error font-bold' : '' }}">{{ $d['defisit'] }}</td>
                    <td class="text-right {{ $d['surplus'] > 0 ? 'text-success font-bold' : '' }}">{{ $d['surplus'] }}</td>
                    <td class="text-right">{{ $d['dalam_proses'] }}</td>
                </tr>
            @empty
                <x-kosong pesan="Tidak ada data untuk filter ini." kolom="7" />
            @endforelse
        </tbody>
    </table>
</div>
@endsection
