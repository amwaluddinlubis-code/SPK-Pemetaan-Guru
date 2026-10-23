@{ $no = 1; }
<div>
    <x-kartu class="mb-4">
        <div class="card-body py-3">
            <div class="flex flex-wrap gap-2">
                <input type="text" wire:model.live.debounce.500ms="q"
                       placeholder="Cari NPSN, nama, atau alamat"
                       class="input flex-1 min-w-52">
                @if (auth()->user()->hasRole('kabid_ptk'))
                    <x-searchable-select wire-model="kecamatan" :selected="$kecamatan"
                        :options="collect($daftarKecamatan)->map(fn ($k) => ['value' => $k, 'label' => $k])->all()"
                        placeholder="— Semua Kecamatan —" class="w-full max-w-xs" />
                @endif
                @if ($q !== '' || $kecamatan !== '' || $status !== '')
                    <button wire:click="resetFilter" class="btn btn-ghost">Reset</button>
                @endif
            </div>
            @if ($q !== '' || $kecamatan !== '' || $status !== '')
                <p class="text-sm opacity-60 mt-2">
                    Menampilkan {{ $daftarSekolah->total() }} hasil
                    @if ($q !== '') untuk “{{ $q }}” @endif
                    @if ($kecamatan !== '') di Kec. {{ $kecamatan }} @endif
                    @if ($status !== '') status: {{ $status }} @endif
                </p>
            @endif
        </div>
    </x-kartu>

    <x-kartu class="overflow-hidden hidden md:block" data-testid="tabel-sekolah-desktop">
        <div class="card-body p-0 overflow-x-auto min-w-0">
            <table class="table table-zebra">
                <thead>
                    <tr>
                        <th><button wire:click="urutkan('nama')" class="link">Nama Sekolah {{ $sort === 'nama' ? ($arah === 'asc' ? '▲' : '▼') : '' }}</button></th>
                        <th><span>NPSN</span></span></th>
                        <th><span>Jenjang</span></th>
                        <th><span>Status</span></th>
                        <th><span>Kebutuhan</span></th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($daftarSekolah as $s)
                        <tr>
                            <td>{{ $s->nama }}</td>
                            <td>{{ $s->npsn }}</td>
                            <td>{{ $s->jenjang }}</td>
                            <td>
                                @if ($s->status_aktif)
                                    <span class="badge badge-success">Aktif</span>
                                @else
                                    <span class="badge badge-error">Nonaktif</span>
                                @endif
                            </td>
                            <td>
                                @foreach ($s->kebutuhan as $kb)
                                    <span class="badge badge-warning text-sm">{{ $kb->mapel }}: {{ $kb->jumlah }}</span>
                                @endforeach
                                @if (count($s->kebutuhan) == 0)
                                    <span class="text-sm opacity-50">—</span>
                                @endif
                            </td>
                            <td class="text-right">
                                @if (auth()->user()->hasRole('kabid_ptk'))
                                    <x-hapus :aksi="route('sekolah.destroy', $s)" tanya="Hapus data {{$s->nama}}?" />
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center py-8 opacity-60">Tidak ada data sekolah yang cocok.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-kartu>

    <div class="grid gap-3 md:hidden" data-testid="kartu-sekolah-mobile">
        @forelse ($daftarSekolah as $s)
            <div class="card bg-base-100 shadow">
                <div class="card-body p-4">
                    <div class="flex justify-between items-start gap-2">
                        <div>
                            <h3 class="font-bold">{{ $s->nama }}</h3>
                            <p class="text-sm opacity-60">{{ $s->npsn }}</p>
                        </div>
                        <span class="badge badge-neutral badge-sm">{{ $s->jenjang }}</span>
                        @if ($s->status_aktif)
                            <span class="badge badge-success">Aktif</span>
                        @else
                            <span class="badge badge-error">Nonaktif</span>
                        @endif
                    </div>
                    <div class="flex flex-wrap gap-1 mt-2">
                        @foreach ($s->kebutuhan as $kb)
                            <span class="badge badge-warning text-sm">{{ $kb->mapel }}: {{ $kb->jumlah }}</span>
                        @endforeach
                        @if (count($s->kebutuhan) == 0)
                            <span class="text-sm opacity-50">—</span>
                        @endif
                    </div>
                    <p class="text-sm mt-2 opacity-70">Alamat: {{ $s->alamat }}</p>
                    @if (auth()->user()->hasRole('kabid_ptk'))
                        <x-hapus :aksi="route('sekolah.destroy', $s)" tanya="Hapus data {{$s->nama}}?" />
                    @endif
                </div>
            </div>
        @empty
            <x-kosong pesan="Tidak ada data sekolah yang cocok." />
        @endforelse
    </div>

    <div class="mt-4 flex flex-wrap items-center justify-between gap-2">
        <x-info-halaman :data="$daftarSekolah" />
        <div class="flex flex-wrap items-center gap-3">
            {{ $daftarSekolah->links() }}
            <x-per-halaman />
        </div>
    </div>
</div>