@extends('layouts.app')

@section('judul', 'Simulasi Kebijakan — PetaPenempatan ASN')

@section('konten')
<div class="mb-4">
    <h1 class="text-2xl font-bold">Simulasi Kebijakan "What-If"</h1>
    <p class="text-sm opacity-70 mt-1">Uji skenario parameter tanpa mengubah data operasional. Hasil bersifat hipotetis, bukan keputusan resmi.</p>
</div>

<!-- Parameter interaktif via Livewire -->
<div class="card bg-base-100 shadow p-4 mb-4">
    <form wire:submit.prevent="jalankan">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4">
            <div>
                <label class="label"><span class="label-text font-semibold">Ambang jarak (km) *</span></label>
                <input type="number" wire:model.live.debounce.500ms="ambang_km" value="15" min="1" max="500" step="0.5" class="input w-full" required>
                <p class="text-xs opacity-60 mt-1">Hanya guru yang jaraknya melebihi ambang ini yang dipertimbangkan.</p>
            </div>
            <div>
                <label class="label"><span class="label-text font-semibold">Mapel</span></label>
                <select wire:model="mapel" class="select w-full">
                    <option value="">Semua mapel</option>
                    @foreach ($daftarMapel as $m)
                        <option value="{{ $m }}">{{ $m }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="label"><span class="label-text font-semibold">Kecamatan domisili</span></label>
                <select wire:model="kecamatan" class="select w-full">
                    <option value="">Semua kecamatan</option>
                    @foreach ($daftarKecamatan as $k)
                        <option value="{{ $k }}">{{ $k }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="label cursor-pointer justify-start gap-3">
                    <input type="checkbox" wire:model="sertikan_3t" value="1" class="checkbox" @if($sertakan_3t) checked @endif>
                    <span class="label-text">Sertakan guru dari sekolah 3T sebagai kandidat</span>
                </label>
            </div>
        </div>

        <div class="text-right mt-4">
            <button type="submit" class="btn btn-primary">Jalankan Simulasi</button>
            <a href="{{ route('simulasi.index') }}" class="btn btn-outline btn-sm ms-2">← Reset</a>
        </div>
    </form>
</div>

@if ($hasil)
<div class="alert alert-warning mb-4">
    <span class="text-sm">⚠️ Ini hasil <strong>simulasi hipotetis</strong> ({{ $hasil['versi_algoritme'] }}, {{ $hasil['waktu'] }}) — <strong>bukan</strong> rekomendasi resmi dan <strong>tidak</strong> mengubah data operasional.</span>
</div>

<div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4">
    <div class="stat bg-base-100 shadow rounded"><div class="stat-title">Kandidat</div><div class="stat-value">{{ $hasil['jumlah_kandidat'] }}</div></div>
    <div class="stat bg-base-100 shadow rounded"><div class="stat-title">Total Hemat (km)</div><div class="stat-value">{{ $hasil['total_hemat_km'] }}</div></div>
    <div class="stat bg-base-100 shadow rounded"><div class="stat-title">Rata-rata Hemat (km)</div><div class="stat-value">{{ $hasil['rata_hemat_km'] }}</div></div>
    <div class="stat bg-base-100 shadow rounded"><div class="stat-title">Baseline: Rekomendasi Aktif</div><div class="stat-value">{{ $hasil['baseline']['rekomendasi_aktif'] }}</div></div>
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
        <thead><tr><th>No</th><th>Kandidat</th><th>Asal</th><th>Tujuan (simulasi)</th><th class="text-right">Hemat (km)</th></tr></thead>
        <tbody>
            @forelse ($hasil['kandidat'] as $k)
                <tr>
                    <td>Kandidat {{ $loop->iteration }}</td>
                    <td>{{ $k['sekolah_tujuan'] }}</td>
                    <td>{{ $k['sekolah_asal'] }}</td>
                    <td class="text-right">{{ $k['hemat_km'] }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center py-6 opacity-60">Tidak ada kandidat untuk parameter ini.</td></tr>
            @endforelse
        </tbody>
    </table>
    @if ($hasil['jumlah_kandidat'] > 50)
        <p class="text-xs opacity-60 p-3">Menampilkan 50 dari {{ $hasil['jumlah_kandidat'] }} kandidat.</p>
    @endif
</div>
@endif
@endsection