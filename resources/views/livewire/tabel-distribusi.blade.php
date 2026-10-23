{{-- Tabel Distribusi defisit/surplus (Livewire): kartu ringkasan + filter terpencarian + tabel berpaginasi. --}}
<div>
<x-halaman judul="Distribusi Defisit & Surplus Guru" ikon="distribusi">
    <x-slot:deskripsi>
        Rumus: <code>defisit = max(kebutuhan − tersedia, 0)</code>,
        <code>surplus = max(tersedia − kebutuhan, 0)</code>.
        Guru dalam proses mutasi (rekomendasi aktif) dihitung sekali di sekolah asal.
        Hanya data agregat — tanpa identitas guru.
    </x-slot:deskripsi>
</x-halaman>

<div>
    <div class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-4">
        <div class="stat bg-base-100 shadow rounded"><div class="stat-title">Tersedia</div><div class="stat-value">{{ $total['tersedia'] }}</div></div>
        <div class="stat bg-base-100 shadow rounded"><div class="stat-title">Kebutuhan</div><div class="stat-value">{{ $total['kebutuhan'] }}</div></div>
        <div class="stat bg-base-100 shadow rounded"><div class="stat-title">Defisit</div><div class="stat-value text-error">{{ $total['defisit'] }}</div></div>
        <div class="stat bg-base-100 shadow rounded"><div class="stat-title">Surplus</div><div class="stat-value text-success">{{ $total['surplus'] }}</div></div>
        <div class="stat bg-base-100 shadow rounded"><div class="stat-title">Dalam Proses</div><div class="stat-value text-warning">{{ $total['dalam_proses'] }}</div></div>
    </div>

    <x-kartu class="mb-4">
        <div class="card-body py-3">
            <div class="flex flex-wrap gap-2">
                <x-searchable-select wire-model="kecamatan" :selected="$kecamatan"
                    :options="$opsiKecamatan"
                    placeholder="— Semua Kecamatan —" class="w-full max-w-xs" />
                <x-searchable-select wire-model="mapel" :selected="$mapel"
                    :options="$opsiMapel"
                    placeholder="— Semua Mapel —" class="w-full max-w-xs" />
                @if ($kecamatan !== '' || $mapel !== '')
                    <button wire:click="resetFilter" class="btn btn-ghost">Reset</button>
                @endif
            </div>
            @if ($kecamatan !== '' || $mapel !== '')
                <p class="text-sm opacity-60 mt-2">Menampilkan {{ $daftar->total() }} hasil.</p>
            @endif
        </div>
    </x-kartu>

    <x-kartu class="overflow-hidden">
        <div class="card-body p-0 overflow-x-auto min-w-0">
            <table class="table table-zebra table-sm">
                <thead>
                    <tr>
                        <th><button wire:click="urutkan('kecamatan')" class="link py-1">Kecamatan {{ $sort === 'kecamatan' ? ($arah === 'asc' ? '▲' : '▼') : '' }}</button></th>
                        <th><button wire:click="urutkan('mapel')" class="link py-1">Mapel {{ $sort === 'mapel' ? ($arah === 'asc' ? '▲' : '▼') : '' }}</button></th>
                        <th class="text-right"><button wire:click="urutkan('tersedia')" class="link py-1">Tersedia {{ $sort === 'tersedia' ? ($arah === 'asc' ? '▲' : '▼') : '' }}</button></th>
                        <th class="text-right"><button wire:click="urutkan('kebutuhan')" class="link py-1">Kebutuhan {{ $sort === 'kebutuhan' ? ($arah === 'asc' ? '▲' : '▼') : '' }}</button></th>
                        <th class="text-right"><button wire:click="urutkan('defisit')" class="link py-1">Defisit {{ $sort === 'defisit' ? ($arah === 'asc' ? '▲' : '▼') : '' }}</button></th>
                        <th class="text-right"><button wire:click="urutkan('surplus')" class="link py-1">Surplus {{ $sort === 'surplus' ? ($arah === 'asc' ? '▲' : '▼') : '' }}</button></th>
                        <th class="text-right"><button wire:click="urutkan('dalam_proses')" class="link py-1">Dalam Proses {{ $sort === 'dalam_proses' ? ($arah === 'asc' ? '▲' : '▼') : '' }}</button></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($daftar as $d)
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
    </x-kartu>

    <div class="mt-4 flex flex-wrap items-center justify-between gap-2">
        <x-info-halaman :data="$daftar" />
        <div class="flex flex-wrap items-center gap-3">
            {{ $daftar->links() }}
            <x-per-halaman />
        </div>
    </div>
</div>

</div>
