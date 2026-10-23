@extends('layouts.app')

@section('judul', 'Kualitas Data — PetaPenempatan ASN')

@section('konten')
<div class="flex flex-wrap items-center justify-between gap-3 mb-4">
    <h1 class="text-2xl font-bold">Pusat Kualitas Data</h1>
    <a href="#" class="btn btn-outline btn-sm">📥 Ekspor Laporan</a>
</div>

<p class="text-sm opacity-70 mb-4">Pemeriksaan bersifat read-only — tidak mengubah data. Klik tautan perbaikan untuk mengedit record yang bermasalah.</p>

<div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4">
    <div class="stat bg-base-100 shadow rounded">
        <div class="stat-title">Total Temuan</div>
        <div class="stat-value">{{ $total }}</div>
    </div>
    @foreach ($ringkasan as $status => $jml)
    <div class="stat bg-base-100 shadow rounded">
        <div class="stat-title">{{ ucfirst($status) }}</div>
        <div class="stat-value {{ $status === 'tinggi' ? 'text-error' : ($status === 'sedang' ? 'text-warning' : 'text-info') }}">{{ $jml }}</div>
    </div>
    @endforeach
</div>

<form wire:submit.prevent="filter">
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4">
        <div>
            <label class="label"><span class="label-text font-semibold">Dampak</span></label>
            <select wire:model="dampak" class="select select-sm">
                <option value="">Semua dampak</option>
                @foreach (['tinggi', 'sedang', 'rendah'] as $d)
                    <option value="{{ $d }}" @if($d === $dampak) selected @endif>
                        {{ ucfirst($d) }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="label"><span class="label-text font-semibold">Jenis</span></label>
            <select wire:model="jenis" class="select select-sm">
                <option value="">Semua jenis</option>
                @foreach ($daftarJenis as $j)
                    <option value="{{ $j }}">{{ $j }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <button type="submit" class="btn btn-primary">Filter</button>
            <a href="#" wire:click="resetFilter" class="btn btn-ghost ml-2">Reset</a>
        </div>
    </div>
</form>

@if ($temuan)
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

@if (count($temuan) > 0)
<div class="alert alert-info mt-4">
    <span class="text-sm">Menampilkan <strong>{{ count($temuan) }}</strong> temuan dari total.</span>
</div>
@endif
@endsection