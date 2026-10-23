
<div>
<x-halaman judul="Analisis Jarak & Aksesibilitas" ikon="akses">
    <x-slot:deskripsi>
        Jarak dihitung dengan <strong>Haversine (garis lurus)</strong>, konsisten dengan mesin SPK.
        Ini <strong>bukan</strong> waktu tempuh perjalanan darat — jangan gunakan sebagai dasar klaim waktu tempuh.
        Data agregat per kecamatan; tanpa identitas guru.
    </x-slot:deskripsi>
</x-halaman>

<div class="flex gap-2 mb-4">
    <select aria-label="kategori daerah" wire:model.live="kategori_daerah" class="select select-sm">
        <option value="">Semua kategori daerah</option>
        @foreach (['biasa', 'terpencil', 'sangat_terpencil'] as $k)
            <option value="{{ $k }}">{{ ucfirst(str_replace('_', ' ', $k)) }}</option>
        @endforeach
    </select>
    @if ($kategori_daerah !== '')
        <button wire:click="resetFilter" class="btn btn-ghost btn-sm">Reset</button>
    @endif
</div>

<div class="card bg-base-100 shadow overflow-x-auto">
    <table class="table table-zebra table-sm">
        <thead>
            <tr><th>Kecamatan</th><th>Kategori Daerah</th><th class="text-right">Guru</th><th class="text-right">Rata-rata (km)</th><th class="text-right">Maks (km)</th><th class="text-right">&gt; 15 km</th></tr>
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
                <x-kosong pesan="Tidak ada data jarak yang valid." kolom="6" />
            @endforelse
        </tbody>
        <tfoot>
            <tr class="font-bold"><td colspan="2">Total</td><td class="text-right">{{ $total_guru }}</td><td colspan="3"></td></tr>
        </tfoot>
    </table>
</div>

<div class="alert alert-info mt-4">
    <span class="text-sm">⚠️ Kualitas data: guru/sekolah tanpa koordinat valid tidak masuk perhitungan. Periksa <a href="{{ route('kualitas-data.index') }}" class="link">Pusat Kualitas Data</a> untuk temuan.</span>
</div>

</div>
