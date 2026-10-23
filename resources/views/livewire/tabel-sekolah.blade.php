{{-- Tabel Data Sekolah (Livewire): pencarian, filter, sorting, pagination tanpa reload. --}}
<div>
    <x-kartu class="mb-4">
        <div class="card-body py-3">
            <div class="flex flex-wrap gap-2">
                <input aria-label="Cari nama, NPSN, alamat…" type="text" wire:model.live.debounce.500ms="q"
                       placeholder="Cari nama, NPSN, alamat…"
                       class="input flex-1 min-w-52">
                @if (! auth()->user()->hasRole('kepala_sekolah', 'ketua_pgri'))
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
                    Menampilkan {{ $daftarSekolah->total() }} hasil
                    @if ($q !== '') untuk “{{ $q }}” @endif
                    @if ($kecamatan !== '') di Kec. {{ $kecamatan }} @endif
                </p>
            @endif
        </div>
    </x-kartu>

    <x-kartu class="overflow-hidden">
        <div class="card-body p-0 overflow-x-auto min-w-0">
            <table class="table table-zebra">
                <thead>
                    <tr>
                        <th><button wire:click="urutkan('nama')" class="link py-1">Nama / NPSN {{ $sort === 'nama' ? ($arah === 'asc' ? '▲' : '▼') : '' }}</button></th>
                        <th>Alamat</th>
                        <th><button wire:click="urutkan('kecamatan')" class="link py-1">Kecamatan {{ $sort === 'kecamatan' ? ($arah === 'asc' ? '▲' : '▼') : '' }}</button></th>
                        <th>Desa/Kelurahan</th>
                        <th>Kategori Daerah</th>
                        <th>Kebutuhan Guru</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($daftarSekolah as $s)
                        <tr>
                            <td>
                                <strong>{{ $s->nama }}</strong><br>
                                <span class="text-sm opacity-60">NPSN: {{ $s->npsn }}</span>
                            </td>
                            <td class="text-sm">{{ $s->alamat }}</td>
                            <td class="text-sm">{{ $s->kecamatan ?? '—' }}</td>
                            <td class="text-sm">{{ $s->desa_kelurahan ?? '—' }}</td>
                            <td>
                                @if ($s->isDaerah3T())
                                    <span class="badge badge-warning badge-sm" title="Rem pemerataan: guru di sekolah ini dilindungi dari mutasi keluar lewat SPK">{{ $s->labelKategoriDaerah() }}</span>
                                @else
                                    <span class="text-sm opacity-60">{{ $s->labelKategoriDaerah() }}</span>
                                @endif
                            </td>
                            <td>
                                @forelse ($s->kebutuhan as $k)
                                    <span class="badge badge-outline mr-1 mb-1">{{ $k->mapel }} ({{ $k->jumlah }})</span>
                                @empty
                                    <span class="text-sm opacity-50">—</span>
                                @endforelse
                            </td>
                            <td class="text-right whitespace-nowrap">
                                <a href="{{ route('sekolah.analisis', $s) }}" class="btn btn-sm btn-info">Analisis</a>
                                @if (auth()->user()->hasRole('kepala_sekolah', 'kabid_ptk'))
                                    <a href="{{ route('sekolah.edit', $s) }}" class="btn btn-sm btn-warning">Ubah</a>
                                @endif
                                @if (auth()->user()->hasRole('kabid_ptk'))
                                    <x-hapus :aksi="route('sekolah.destroy', $s)" tanya="Hapus {{ $s->nama }} beserta kebutuhan gurunya?" />
                                @endif
                            </td>
                        </tr>
                    @empty
                        @if ($konteksKosong)
                            <x-kosong pesan="Belum ada data sekolah di wilayah {{ $namaPemdaKonteks }}. Data di luar konteks Pemda disembunyikan — atur konteks lewat menu Sistem → Pengaturan Pemda." kolom="7" />
                        @else
                            <x-kosong pesan="Tidak ada data sekolah yang cocok." kolom="7" />
                        @endif
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-kartu>

    <div class="mt-4 flex flex-wrap items-center justify-between gap-2">
        <x-info-halaman :data="$daftarSekolah" />
        <div class="flex flex-wrap items-center gap-3">
            {{ $daftarSekolah->links() }}
            <x-per-halaman />
        </div>
    </div>
</div>
