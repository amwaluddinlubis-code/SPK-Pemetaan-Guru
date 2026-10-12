{{-- Daftar sekolah --}}
@extends('layouts.app')
@section('judul', 'Data Sekolah — PetaPenempatan ASN')

@section('konten')
    <div class="flex flex-wrap justify-between items-center gap-2 mb-4">
        <h1 class="text-2xl font-bold">🏫 Data Sekolah</h1>
        @if (auth()->user()->hasRole('kabid_ptk'))
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('sekolah.import') }}" class="btn btn-secondary">📥 Import Excel</a>
                <a href="{{ route('sekolah.export', request()->only(['q', 'kecamatan'])) }}" class="btn btn-accent">📊 Export Excel</a>
                <a href="{{ route('sekolah.cetak', request()->only(['q', 'kecamatan'])) }}" target="_blank" class="btn btn-neutral">🖨️ Cetak/PDF</a>
                <a href="{{ route('sekolah.create') }}" class="btn btn-primary">+ Tambah Sekolah</a>
            </div>
        @endif
    </div>

    <div class="card bg-base-100 shadow-xl mb-4">
        <div class="card-body py-3">
            {{-- Rincian baris yang gagal saat import terakhir --}}
            @if (session('import_gagal'))
                <div class="alert alert-warning mb-3">
                    <div class="text-sm">
                        <strong>⚠️ Baris yang gagal diimpor:</strong>
                        <ul class="list-disc list-inside mt-1">
                            @foreach (session('import_gagal') as $pesan)
                                <li>{{ $pesan }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif
            {{-- Pencarian + filter kecamatan (GET, dibawa saat pindah halaman) --}}
            <form method="GET" action="{{ route('sekolah.index') }}" class="flex flex-wrap gap-2">
                <input type="text" name="q" value="{{ request('q') }}"
                       placeholder="Cari nama, NPSN, alamat…"
                       class="input input-bordered flex-1 min-w-52">
                @if (! auth()->user()->hasRole('kepala_sekolah', 'ketua_pgri'))
                    <select name="kecamatan" class="select select-bordered max-w-xs">
                        <option value="">— Semua Kecamatan —</option>
                        @foreach ($daftarKecamatan as $kec)
                            <option value="{{ $kec }}" {{ request('kecamatan') == $kec ? 'selected' : '' }}>
                                {{ $kec }}
                            </option>
                        @endforeach
                    </select>
                @endif
                <button type="submit" class="btn btn-primary">🔍 Cari</button>
                @if (request('q') || request('kecamatan'))
                    <a href="{{ route('sekolah.index') }}" class="btn btn-ghost">Reset</a>
                @endif
            </form>
            @if (request('q') || request('kecamatan'))
                <p class="text-sm opacity-60 mt-2">
                    Menampilkan {{ $daftarSekolah->total() }} hasil
                    @if (request('q')) untuk “{{ request('q') }}” @endif
                    @if (request('kecamatan')) di Kec. {{ request('kecamatan') }} @endif
                </p>
            @endif
        </div>
    </div>

    <div class="card bg-base-100 shadow-xl overflow-hidden">
        <div class="card-body p-0 overflow-x-auto min-w-0">
            <table class="table table-zebra">
                <thead>
                    <tr>
                        <th><a href="{{ route('sekolah.index', array_merge(request()->except(['sort', 'arah', 'page']), ['sort' => 'nama', 'arah' => ($sortir === 'nama' && $arah === 'asc') ? 'desc' : 'asc'])) }}" class="link">Nama / NPSN {{ $sortir === 'nama' ? ($arah === 'asc' ? '▲' : '▼') : '' }}</a></th>
                        <th>Alamat</th>
                        <th><a href="{{ route('sekolah.index', array_merge(request()->except(['sort', 'arah', 'page']), ['sort' => 'kecamatan', 'arah' => ($sortir === 'kecamatan' && $arah === 'asc') ? 'desc' : 'asc'])) }}" class="link">Kecamatan {{ $sortir === 'kecamatan' ? ($arah === 'asc' ? '▲' : '▼') : '' }}</a></th>
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
                        <tr><td colspan="5" class="text-center opacity-60 py-8">Tidak ada data sekolah yang cocok.</td></tr>
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
@endsection
