
<div>
<x-halaman judul="Pusat Kualitas Data" ikon="kualitas">
    <x-slot:aksi>
        <a href="{{ route('kualitas-data.export') }}" class="btn btn-outline btn-sm tooltip tooltip-bottom" data-tip="Ekspor Laporan" aria-label="Ekspor Laporan"><x-ikon nama="unduh" class="h-4 w-4" /></a>
    </x-slot:aksi>
</x-halaman>

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

<div class="flex gap-2 mb-4">
    <select aria-label="dampak" wire:model.live="dampak" class="select select-sm">
        <option value="">Semua dampak</option>
        @foreach (['tinggi', 'sedang', 'rendah'] as $d)
            <option value="{{ $d }}">{{ ucfirst($d) }}</option>
        @endforeach
    </select>
    <select aria-label="jenis" wire:model.live="jenis" class="select select-sm">
        <option value="">Semua jenis</option>
        @foreach ($daftarJenis as $j)
            <option value="{{ $j }}">{{ $j }}</option>
        @endforeach
    </select>
    @if ($dampak !== '' || $jenis !== '')
        <button wire:click="resetFilter" class="btn btn-ghost btn-sm">Reset</button>
    @endif
</div>

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

</div>
