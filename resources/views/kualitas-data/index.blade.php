@extends('layouts.app')

@section('judul', 'Kualitas Data — PetaPenempatan ASN')

@section('konten')
<div class="flex flex-wrap items-center justify-between gap-3 mb-4">
    <h1 class="text-2xl font-bold">Pusat Kualitas Data</h1>
    <a href="{{ route('kualitas-data.export', request()->query()) }}" class="btn btn-outline btn-sm">📥 Ekspor Laporan</a>
</div>

<p class="text-sm opacity-70 mb-4">Pemeriksaan bersifat read-only — tidak mengubah data. Klik tautan perbaikan untuk mengedit record yang bermasalah.</p>

<div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4">
    <div class="stat bg-base-100 shadow rounded">
        <div class="stat-title">Total Temuan</div>
        <div class="stat-value">{{ $total }}</div>
    </div>
    <div class="stat bg-base-100 shadow rounded">
        <div class="stat-title">Dampak Tinggi</div>
        <div class="stat-value text-error">{{ $ringkasan['tinggi'] }}</div>
    </div>
    <div class="stat bg-base-100 shadow rounded">
        <div class="stat-title">Dampak Sedang</div>
        <div class="stat-value text-warning">{{ $ringkasan['sedang'] }}</div>
    </div>
    <div class="stat bg-base-100 shadow rounded">
        <div class="stat-title">Dampak Rendah</div>
        <div class="stat-value text-info">{{ $ringkasan['rendah'] }}</div>
    </div>
</div>

<form method="GET" action="{{ route('kualitas-data.index') }}" class="flex gap-2 mb-4">
    <select name="dampak" class="select select-bordered select-sm">
        <option value="">Semua dampak</option>
        @foreach (['tinggi', 'sedang', 'rendah'] as $d)
            <option value="{{ $d }}" @selected(request('dampak') === $d)>{{ ucfirst($d) }}</option>
        @endforeach
    </select>
    <select name="jenis" class="select select-bordered select-sm">
        <option value="">Semua jenis</option>
        @foreach ($daftarJenis as $j)
            <option value="{{ $j }}" @selected(request('jenis') === $j)>{{ $j }}</option>
        @endforeach
    </select>
    <button type="submit" class="btn btn-primary btn-sm">Filter</button>
    <a href="{{ route('kualitas-data.index') }}" class="btn btn-ghost btn-sm">Reset</a>
</form>

<div class="card bg-base-100 shadow overflow-x-auto">
    <table class="table table-zebra table-sm">
        <thead>
            <tr><th>Entitas</th><th>Nama</th><th>Jenis</th><th>Dampak</th><th>Pesan</th><th>Perbaikan</th></tr>
        </thead>
        <tbody>
            @forelse ($temuan as $t)
                <tr>
                    <td>{{ $t['entitas'] }}</td>
                    <td>{{ $t['nama'] }}</td>
                    <td><code class="text-xs">{{ $t['jenis'] }}</code></td>
                    <td>
                        <span class="badge {{ $t['dampak'] === 'tinggi' ? 'badge-error' : ($t['dampak'] === 'sedang' ? 'badge-warning' : 'badge-info') }}">{{ $t['dampak'] }}</span>
                    </td>
                    <td class="text-xs">{{ $t['pesan'] }}</td>
                    <td>
                        @if ($t['tautan'] !== '#')
                            <a href="{{ $t['tautan'] }}" class="btn btn-xs btn-outline">Perbaiki</a>
                        @else
                            <span class="opacity-40 text-xs">—</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center py-8">
                    <div class="text-lg">✅</div>
                    <div class="opacity-60">Tidak ada temuan kualitas data. Data dalam kondisi baik.</div>
                </td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
