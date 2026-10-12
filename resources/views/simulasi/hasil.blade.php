@extends('layouts.app')

@section('judul', 'Hasil Simulasi — PetaPenempatan ASN')

@section('konten')
<div class="flex flex-wrap items-center justify-between gap-3 mb-4">
    <h1 class="text-2xl font-bold">Hasil Simulasi</h1>
    <a href="{{ route('simulasi.index') }}" class="btn btn-outline btn-sm">← Simulasi Baru</a>
</div>

<div class="alert alert-warning mb-4">
    <span class="text-sm">⚠️ Ini hasil <strong>simulasi hipotetis</strong> ({{ $hasil['versi_algoritme'] }}, {{ $hasil['waktu'] }}) — <strong>bukan</strong> rekomendasi resmi dan <strong>tidak</strong> mengubah data operasional.</span>
</div>

<div class="card bg-base-100 shadow p-4 mb-4">
    <h2 class="font-bold mb-2">Parameter</h2>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-2 text-sm">
        <div>Ambang jarak: <strong>{{ $hasil['parameter']['ambang_km'] }} km</strong></div>
        <div>Mapel: <strong>{{ $hasil['parameter']['mapel'] }}</strong></div>
        <div>Kecamatan: <strong>{{ $hasil['parameter']['kecamatan'] }}</strong></div>
        <div>Sertakan 3T: <strong>{{ $hasil['parameter']['sertakan_3t'] ? 'Ya' : 'Tidak' }}</strong></div>
    </div>
</div>

<div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4">
    <div class="stat bg-base-100 shadow rounded"><div class="stat-title">Kandidat</div><div class="stat-value">{{ $hasil['jumlah_kandidat'] }}</div></div>
    <div class="stat bg-base-100 shadow rounded"><div class="stat-title">Total Hemat (km)</div><div class="stat-value">{{ $hasil['total_hemat_km'] }}</div></div>
    <div class="stat bg-base-100 shadow rounded"><div class="stat-title">Rata-rata Hemat (km)</div><div class="stat-value">{{ $hasil['rata_hemat_km'] }}</div></div>
    <div class="stat bg-base-100 shadow rounded"><div class="stat-title">Baseline: Rekomendasi Aktif</div><div class="stat-value">{{ $hasil['baseline']['rekomendasi_aktif'] }}</div></div>
</div>

<div class="card bg-base-100 shadow p-4 mb-4">
    <h2 class="font-bold mb-2">Asumsi & Keterbatasan</h2>
    <ul class="list-disc list-inside text-sm opacity-80">
        @foreach ($hasil['asumsi'] as $a)
            <li>{{ $a }}</li>
        @endforeach
    </ul>
</div>

@if (count($hasil['sekolah_terdampak']) > 0)
<div class="card bg-base-100 shadow p-4 mb-4">
    <h2 class="font-bold mb-2">Sekolah Terdampak ({{ count($hasil['sekolah_terdampak']) }})</h2>
    <div class="flex flex-wrap gap-2">
        @foreach ($hasil['sekolah_terdampak'] as $s)
            <span class="badge badge-outline">{{ $s }}</span>
        @endforeach
    </div>
</div>
@endif

<div class="card bg-base-100 shadow overflow-x-auto">
    <table class="table table-zebra table-sm">
        <thead><tr><th>Kandidat</th><th>Asal</th><th>Tujuan (simulasi)</th><th class="text-right">Hemat (km)</th></tr></thead>
        <tbody>
            @forelse ($hasil['kandidat'] as $k)
                <tr>
                    <td>Kandidat {{ $loop->iteration }}</td>
                    <td>{{ $k['sekolah_asal'] }}</td>
                    <td>{{ $k['sekolah_tujuan'] }}</td>
                    <td class="text-right">{{ $k['hemat_km'] }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center py-6 opacity-60">Tidak ada kandidat untuk parameter ini.</td></tr>
            @endforelse
        </tbody>
    </table>
    @if ($hasil['jumlah_kandidat'] > 50)
        <p class="text-xs opacity-60 p-3">Menampilkan 50 dari {{ $hasil['jumlah_kandidat'] }} kandidat.</p>
    @endif
</div>
@endsection
