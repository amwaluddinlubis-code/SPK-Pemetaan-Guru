{{-- Daftar guru --}}
@extends('layouts.app')
@section('judul', 'Data Guru — PetaPenempatan ASN')

@section('konten')
    <div class="flex flex-wrap justify-between items-center gap-2 mb-4">
        <h1 class="text-2xl font-bold">👩‍🏫 Data Guru</h1>
        @if (auth()->user()->hasRole('kabid_ptk', 'kepala_sekolah'))
            <div class="flex flex-wrap gap-2">
                @if (auth()->user()->hasRole('kabid_ptk'))
                    <a href="{{ route('guru.import') }}" class="btn btn-secondary">📥 Import Excel</a>
                @endif
                <a href="{{ route('guru.export', request()->only(['q', 'kecamatan'])) }}" class="btn btn-accent">📊 Export Excel</a>
                <a href="{{ route('guru.cetak', request()->only(['q', 'kecamatan'])) }}" target="_blank" class="btn btn-neutral">🖨️ Cetak/PDF</a>
                <a href="{{ route('guru.create') }}" class="btn btn-primary">+ Tambah Guru</a>
            </div>
        @endif
    </div>

    {{-- Rincian baris yang gagal saat import terakhir --}}
    @if (session('import_gagal'))
        <div class="alert alert-warning mb-4">
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

    <div class="card bg-base-100 shadow-xl mb-4">
        <div class="card-body py-3">
            {{-- Pencarian + filter kecamatan (GET, dibawa saat pindah halaman) --}}
            <form method="GET" action="{{ route('guru.index') }}" class="flex flex-wrap gap-2">
                <input type="text" name="q" value="{{ request('q') }}"
                       placeholder="Cari nama, NIP, mapel, domisili, sekolah…"
                       class="input input-bordered flex-1 min-w-52">
                @if (auth()->user()->hasRole('kabid_ptk', 'kadis'))
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
                    <a href="{{ route('guru.index') }}" class="btn btn-ghost">Reset</a>
                @endif
            </form>
            @if (request('q') || request('kecamatan'))
                <p class="text-sm opacity-60 mt-2">
                    Menampilkan {{ $daftarGuru->total() }} hasil
                    @if (request('q')) untuk “{{ request('q') }}” @endif
                    @if (request('kecamatan')) di Kec. {{ request('kecamatan') }} @endif
                </p>
            @endif
        </div>
    </div>

    <div class="card bg-base-100 shadow-xl overflow-hidden hidden md:block" data-testid="tabel-guru-desktop">
        <div class="card-body p-0 overflow-x-auto min-w-0">
            <table class="table table-zebra">
                <thead>
                    <tr>
                        <th><a href="{{ route('guru.index', array_merge(request()->except(['sort', 'arah', 'page']), ['sort' => 'nama', 'arah' => ($sortir === 'nama' && $arah === 'asc') ? 'desc' : 'asc'])) }}" class="link">Nama / NIP {{ $sortir === 'nama' ? ($arah === 'asc' ? '▲' : '▼') : '' }}</a></th>
                        <th><a href="{{ route('guru.index', array_merge(request()->except(['sort', 'arah', 'page']), ['sort' => 'mapel', 'arah' => ($sortir === 'mapel' && $arah === 'asc') ? 'desc' : 'asc'])) }}" class="link">Mapel {{ $sortir === 'mapel' ? ($arah === 'asc' ? '▲' : '▼') : '' }}</a></th>
                        <th>Status</th>
                        <th>Domisili</th>
                        <th><a href="{{ route('guru.index', array_merge(request()->except(['sort', 'arah', 'page']), ['sort' => 'kecamatan', 'arah' => ($sortir === 'kecamatan' && $arah === 'asc') ? 'desc' : 'asc'])) }}" class="link">Kecamatan {{ $sortir === 'kecamatan' ? ($arah === 'asc' ? '▲' : '▼') : '' }}</a></th>
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
                                <span class="text-sm opacity-60">{{ $g->nip }}</span>
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
                                    <form method="POST" action="{{ route('guru.destroy', $g) }}" class="inline"
                                          onsubmit="return confirm('Hapus data {{ $g->nama }}?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-error">Hapus</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center opacity-60 py-8">Tidak ada data guru yang cocok.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Tampilan kartu untuk mobile (pengganti tabel yang terlalu lebar) --}}
    <div class="grid gap-3 md:hidden" data-testid="kartu-guru-mobile">
        @forelse ($daftarGuru as $g)
            <div class="card bg-base-100 shadow">
                <div class="card-body p-4">
                    <div class="flex justify-between items-start gap-2">
                        <div>
                            <h3 class="font-bold">{{ $g->nama }}</h3>
                            <p class="text-sm opacity-60">{{ $g->nip }}</p>
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
                            <form method="POST" action="{{ route('guru.destroy', $g) }}" class="inline"
                                  onsubmit="return confirm('Hapus data {{ $g->nama }}?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-error">Hapus</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <p class="text-center opacity-60 py-8">Tidak ada data guru yang cocok.</p>
        @endforelse
    </div>

    <div class="mt-4 flex flex-wrap items-center justify-between gap-2">
        <p class="text-sm opacity-70">
            Menampilkan {{ $daftarGuru->firstItem() ?? 0 }}–{{ $daftarGuru->lastItem() ?? 0 }}
            dari {{ $daftarGuru->total() }} data
        </p>
        {{ $daftarGuru->links() }}
    </div>
@endsection
