
<div>
<div class="mb-4">
    <h1 class="text-2xl font-bold">Simulasi Kebijakan “What-If”</h1>
    <p class="text-sm opacity-70 mt-1">Uji skenario parameter tanpa mengubah data operasional. Hasil bersifat hipotetis, bukan keputusan resmi.</p>
</div>

<form wire:submit="hitung" class="card bg-base-100 shadow p-6 w-full max-w-xl min-w-0">
    <div class="grid grid-cols-1 gap-4 min-w-0">
        <div class="min-w-0">
            <label class="label" for="f-ambang_km"><span class="label-text font-semibold">Ambang jarak (km) *</span></label>
            <input id="f-ambang_km" aria-label="Ambang jarak (km)" type="number" wire:model="ambang_km" min="1" max="500" step="0.5" class="input w-full" required>
            <p class="text-xs opacity-60 mt-1">Hanya guru yang jaraknya melebihi ambang ini yang dipertimbangkan.</p>
            @error('ambang_km')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
        </div>
        <div class="min-w-0">
            <label class="label" for="f-mapel"><span class="label-text font-semibold">Mapel</span></label>
            <select id="f-mapel" aria-label="Mata pelajaran" wire:model="mapel" class="select w-full">
                <option value="">Semua mapel</option>
                @foreach ($daftarMapel as $m)
                    <option value="{{ $m }}">{{ $m }}</option>
                @endforeach
            </select>
        </div>
        <div class="min-w-0">
            <label class="label" for="f-kecamatan"><span class="label-text font-semibold">Kecamatan domisili</span></label>
            <select id="f-kecamatan" aria-label="Kecamatan domisili" wire:model="kecamatan" class="select w-full">
                <option value="">Semua kecamatan</option>
                @foreach ($daftarKecamatan as $k)
                    <option value="{{ $k }}">{{ $k }}</option>
                @endforeach
            </select>
        </div>
        <div class="min-w-0">
            <label class="label cursor-pointer justify-start gap-3 min-w-0">
                <input type="checkbox" wire:model="sertakan_3t" class="checkbox shrink-0">
                <span class="label-text min-w-0 whitespace-normal">Sertakan guru dari sekolah 3T sebagai kandidat</span>
            </label>
        </div>
        <button type="submit" class="btn btn-primary">Jalankan Simulasi</button>
    </div>
</form>

@if ($hasil)
<h2 class="text-xl font-bold mt-8 mb-4">Hasil Simulasi</h2>
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
@endif

</div>
