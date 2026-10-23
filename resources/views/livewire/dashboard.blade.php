{{-- Halaman Dashboard (komponen Livewire penuh): kartu statistik + peta --}}
@push('kepala')
    @vite('resources/js/peta.js')
@endpush
<div>
    <h1 class="text-2xl font-bold mb-4"><x-ikon nama="dashboard" class="h-7 w-7 inline" /> Dashboard Pemetaan</h1>

    {{-- Kartu KPI berketerangan (Redesign R2): angka + konteks nyata --}}
    <div class="flex flex-wrap gap-4 mb-6">
        <x-kartu-kpi label="Total Guru" :nilai="number_format($totalGuru)" ikon="guru">
            {{ number_format($guruBerdomisili) }} dari {{ number_format($totalGuru) }} berdomisili terpetakan
        </x-kartu-kpi>
        <x-kartu-kpi label="Total Sekolah" :nilai="number_format($totalSekolah)" ikon="sekolah">
            {{ $sekolahPerJenjang }}
        </x-kartu-kpi>
        <x-kartu-kpi label="Rata-rata Jarak" :nilai="$rataJarak !== null ? number_format($rataJarak, 1).' km' : '—'" ikon="peta">
            {{ $guruDenganJarak }} dari {{ $totalGuru }} guru memiliki koordinat valid
        </x-kartu-kpi>
        <x-kartu-kpi label="Butuh Perhatian" :nilai="number_format($guruJauh)" :ikon="$guruJauh > 0 ? 'peringatan' : 'spk'" :nada="$guruJauh > 0 ? 'bahaya' : 'sukses'">
            @if ($guruJauh > 0)
                Guru berjarak &gt; 5 km dari sekolah tugasnya — telaah di menu Aksesibilitas
            @else
                Tidak ada guru berjarak &gt; 5 km dari sekolah tugasnya
            @endif
        </x-kartu-kpi>
        @if (auth()->user()->hasRole('kabid_ptk', 'kadis'))
            <x-kartu-kpi label="Usulan Menunggu" :nilai="number_format($usulanMenunggu)" ikon="usulan" :nada="$usulanMenunggu > 0 ? 'peringatan' : 'netral'" :tautan="route('usulan.index')">
                @if ($usulanMenunggu > 0)
                    Usulan penempatan menunggu diproses — klik untuk membuka antrean
                @else
                    Tidak ada usulan yang menunggu diproses
                @endif
            </x-kartu-kpi>
        @endif
    </div>

    {{-- Komponen Livewire: peta + rekomendasi (interaktif tanpa reload) --}}
    @livewire('peta-rekomendasi')

</div>
