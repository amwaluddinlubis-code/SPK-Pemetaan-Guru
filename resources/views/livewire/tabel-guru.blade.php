{{-- Tabel Data Guru (Livewire): pencarian, filter, sorting, pagination tanpa reload. --}}
<div>
<x-halaman judul="Data Guru" ikon="guru">
    <x-slot:aksi>
        @if (auth()->user()->hasRole('kabid_ptk', 'kepala_sekolah', 'kadis'))
            @if (auth()->user()->hasRole('kabid_ptk'))
                <a href="{{ route('guru.import') }}" class="btn btn-secondary">📥 Import Excel</a>
            @endif
            <a href="{{ route('guru.export', request()->only(['q', 'kecamatan'])) }}" class="btn btn-accent">📊 Export Excel</a>
            <a href="{{ route('guru.cetak', request()->only(['q', 'kecamatan'])) }}" target="_blank" class="btn btn-neutral">🖨️ Cetak/PDF</a>
            @if (auth()->user()->hasRole('kabid_ptk', 'kepala_sekolah'))
                <a href="{{ route('guru.create') }}" class="btn btn-primary">+ Tambah Guru</a>
            @endif
        @endif
    </x-slot:aksi>
</x-halaman>

{{-- Rincian baris yang gagal saat import terakhir --}}
@if (session('import_gagal'))
    <x-alert tipe="warning" class="mb-4">
        <div class="text-sm">
            <strong>⚠️ Baris yang gagal diimpor:</strong>
            <ul class="list-disc list-inside mt-1">
                @foreach (session('import_gagal') as $pesan)
                    <li>{{ $pesan }}</li>
                @endforeach
            </ul>
        </div>
    </x-alert>
@endif

<x-chip-konteks jenis="guru" />

<div>
    <x-kartu class="mb-4">
        <div class="card-body py-3">
            <div class="flex flex-wrap gap-2">
                <input aria-label="Cari nama, NIP, mapel, domisili, sekolah…" type="text" wire:model.live.debounce.500ms="q"
                       placeholder="Cari nama, NIP, mapel, domisili, sekolah…"
                       class="input flex-1 min-w-52">
                @if (auth()->user()->hasRole('kabid_ptk', 'kadis'))
                    <x-searchable-select wire-model="kecamatan" :selected="$kecamatan"
                        :options="collect($daftarKecamatan)->map(fn ($k) => ['value' => $k, 'label' => $k])->all()"
                        placeholder="— Semua Kecamatan —" class="w-full max-w-xs" />
                @endif
                @if ($q !== '' || $kecamatan !== '')
                    <button wire:click="resetFilter" class="btn btn-ghost">Reset</button>
                @endif
            </div>
            @if ($q !== '' || $kecamatan !== '')
                <p class="text-sm opacity-60 mt-2">
                    Menampilkan {{ $daftarGuru->total() }} hasil
                    @if ($q !== '') untuk “{{ $q }}” @endif
                    @if ($kecamatan !== '') di Kec. {{ $kecamatan }} @endif
                </p>
            @endif
        </div>
    </x-kartu>

    <x-kartu class="overflow-hidden hidden md:block" data-testid="tabel-guru-desktop">
        <div class="card-body p-0 overflow-x-auto min-w-0">
            <table class="table table-zebra">
                <thead>
                    <tr>
                        <th><button wire:click="urutkan('nama')" class="link py-1">Nama / NIP {{ $sort === 'nama' ? ($arah === 'asc' ? '▲' : '▼') : '' }}</button></th>
                        <th><button wire:click="urutkan('mapel')" class="link py-1">Mapel {{ $sort === 'mapel' ? ($arah === 'asc' ? '▲' : '▼') : '' }}</button></th>
                        <th>Status</th>
                        <th>Domisili</th>
                        <th><button wire:click="urutkan('kecamatan')" class="link py-1">Kecamatan {{ $sort === 'kecamatan' ? ($arah === 'asc' ? '▲' : '▼') : '' }}</button></th>
                        <th>Desa/Kelurahan</th>
                        <th>Sekolah Saat Ini</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($daftarGuru as $g)
                        <tr>
                            <td>
                                <strong>{{ $g->nama }}</strong><br>
                                <span class="text-sm opacity-60">{{ $g->nipTersamar() }}</span>
                            </td>
                            <td>
                                <span class="badge badge-outline">{{ $g->mapel }}</span>
                                <span class="text-sm opacity-60">{{ $g->jenjang }}</span>
                                @if ($g->sudah_sertifikasi)
                                    <span class="badge badge-success badge-sm" title="Sertifikasi: {{ $g->mapel_sertifikasi ?? '—' }}{{ $g->linear() ? '' : ' — TIDAK LINEAR' }}">✓ Serdik</span>
                                @endif
                                @if (! $g->linear())
                                    <span class="badge badge-error badge-sm" title="Mapel diampu ({{ $g->mapel }}) tidak sesuai sertifikat ({{ $g->mapel_sertifikasi }})">Tidak linear</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-neutral badge-sm">{{ $g->labelStatusKepegawaian() }}</span>
                                @if ($g->berkeluarga())
                                    <span class="badge badge-info" title="Prioritas keluarga dalam rekomendasi">👨‍👩‍👧 {{ $g->labelStatusPernikahan() }}</span>
                                @else
                                    <span class="badge badge-ghost">{{ $g->labelStatusPernikahan() }}</span>
                                @endif
                            </td>
                            <td class="text-sm">{{ $g->domisili_alamat }}</td>
                            <td class="text-sm">{{ $g->kecamatan ?? '—' }}</td>
                            <td class="text-sm">{{ $g->desa_kelurahan ?? '—' }}</td>
                            <td class="text-sm">{{ $g->sekolah_saat_ini }}</td>
                            <td class="text-right whitespace-nowrap">
                                @if (auth()->user()->hasRole('guru', 'kabid_ptk', 'kepala_sekolah'))
                                    <a href="{{ route('guru.edit', $g) }}" class="btn btn-sm btn-warning">Ubah</a>
                                @endif
                                @if (auth()->user()->hasRole('kabid_ptk', 'kepala_sekolah'))
                                    <x-hapus :aksi="route('guru.destroy', $g)" tanya="Hapus data {{ $g->nama }}?" />
                                @endif
                            </td>
                        </tr>
                    @empty
                        @if ($konteksKosong)
                            <x-kosong pesan="Belum ada data guru di wilayah {{ $namaPemdaKonteks }}. Data di luar konteks Pemda disembunyikan — atur konteks lewat menu Sistem → Pengaturan Pemda." kolom="8" />
                        @else
                            <x-kosong pesan="Tidak ada data guru yang cocok." kolom="8" />
                        @endif
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-kartu>

    {{-- Tampilan kartu untuk mobile (pengganti tabel yang terlalu lebar) --}}
    <div class="grid gap-3 md:hidden" data-testid="kartu-guru-mobile">
        @forelse ($daftarGuru as $g)
            <div class="card bg-base-100 shadow">
                <div class="card-body p-4">
                    <div class="flex justify-between items-start gap-2">
                        <div>
                            <h3 class="font-bold">{{ $g->nama }}</h3>
                            <p class="text-sm opacity-60">{{ $g->nipTersamar() }}</p>
                        </div>
                        <span class="badge badge-neutral badge-sm">{{ $g->labelStatusKepegawaian() }}</span>
                    </div>
                    <div class="flex flex-wrap gap-1 mt-2">
                        <span class="badge badge-outline badge-sm">{{ $g->mapel }} ({{ $g->jenjang }})</span>
                        @if ($g->sudah_sertifikasi)
                            <span class="badge badge-success badge-sm">✓ Serdik</span>
                        @endif
                        @if (! $g->linear())
                            <span class="badge badge-error badge-sm">Tidak linear</span>
                        @endif
                        @if ($g->berkeluarga())
                            <span class="badge badge-info badge-sm">{{ $g->labelStatusPernikahan() }}</span>
                        @endif
                    </div>
                    <p class="text-sm mt-2 opacity-70">{{ $g->sekolah_saat_ini }}</p>
                    <p class="text-sm opacity-60">{{ $g->kecamatan ?? '—' }}{{ $g->desa_kelurahan ? ', '.$g->desa_kelurahan : '' }}</p>
                    <div class="card-actions justify-end mt-2">
                        @if (auth()->user()->hasRole('guru', 'kabid_ptk', 'kepala_sekolah'))
                            <a href="{{ route('guru.edit', $g) }}" class="btn btn-sm btn-warning">Ubah</a>
                        @endif
                        @if (auth()->user()->hasRole('kabid_ptk', 'kepala_sekolah'))
                            <x-hapus :aksi="route('guru.destroy', $g)" tanya="Hapus data {{ $g->nama }}?" />
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <x-kosong pesan="Tidak ada data guru yang cocok." />
        @endforelse
    </div>

    <div class="mt-4 flex flex-wrap items-center justify-between gap-2">
        <x-info-halaman :data="$daftarGuru" />
        <div class="flex flex-wrap items-center gap-3">
            {{ $daftarGuru->links() }}
            <x-per-halaman />
        </div>
    </div>
</div>

</div>
