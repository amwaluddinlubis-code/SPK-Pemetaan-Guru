{{-- Tabel Data Sekolah (Livewire): pencarian, filter, sorting, pagination tanpa reload. --}}
<div>
    <div class="card bg-base-100 shadow-xl mb-4">
        <div class="card-body py-3">
            <div class="flex flex-wrap gap-2">
                <input type="text" wire:model.live.debounce.500ms="q"
                       placeholder="Cari nama, NPSN, alamat…"
                       class="input flex-1 min-w-52">
                @if (! auth()->user()->hasRole('kepala_sekolah', 'ketua_pgri'))
                    <select wire:model.live="kecamatan" class="select max-w-xs">
                        <option value="">— Semua Kecamatan —</option>
                        @foreach ($daftarKecamatan as $kec)
                            <option value="{{ $kec }}">{{ $kec }}</option>
                        @endforeach
                    </select>
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
    </div>

    <div class="card bg-base-100 shadow-xl overflow-hidden">
        <div class="card-body p-0 overflow-x-auto min-w-0">
            <table class="table table-zebra">
                <thead>
                    <tr>
                        <th><button wire:click="urutkan('nama')" class="link">Nama / NPSN {{ $sort === 'nama' ? ($arah === 'asc' ? '▲' : '▼') : '' }}</button></th>
                        <th>Alamat</th>
                        <th><button wire:click="urutkan('kecamatan')" class="link">Kecamatan {{ $sort === 'kecamatan' ? ($arah === 'asc' ? '▲' : '▼') : '' }}</button></th>
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
                                @if (auth()->user()->hasRole('kepala_sekolah', 'kabid_ptk'))
                                    <a href="{{ route('sekolah.edit', $s) }}" class="btn btn-sm btn-warning">Ubah</a>
                                @endif
                                @if (auth()->user()->hasRole('kabid_ptk'))
                                    <form method="POST" action="{{ route('sekolah.destroy', $s) }}" class="inline"
                                          onsubmit="return confirm('Hapus {{ $s->nama }} beserta kebutuhan gurunya?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-error">Hapus</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center opacity-60 py-8">Tidak ada data sekolah yang cocok.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4 flex flex-wrap items-center justify-between gap-2">
        <p class="text-sm opacity-70">
            Menampilkan {{ $daftarSekolah->firstItem() ?? 0 }}–{{ $daftarSekolah->lastItem() ?? 0 }}
            dari {{ $daftarSekolah->total() }} data
        </p>
        {{ $daftarSekolah->links() }}
    </div>
</div>
