{{-- Daftar guru --}}
@extends('layouts.app')
@section('judul', 'Data Guru — PetaPenempatan ASN')

@section('konten')
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold">👩‍🏫 Data Guru</h1>
        @if (auth()->user()->hasRole('kabid_ptk', 'kepala_sekolah'))
            <div class="flex gap-2">
                @if (auth()->user()->hasRole('kabid_ptk'))
                    <a href="{{ route('guru.import') }}" class="btn btn-secondary">📥 Import Excel</a>
                @endif
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

    <div class="card bg-base-100 shadow-xl">
        <div class="card-body p-0 overflow-x-auto">
            <table class="table table-zebra">
                <thead>
                    <tr>
                        <th>Nama / NIP</th>
                        <th>Mapel</th>
                        <th>Domisili</th>
                        <th>Kecamatan</th>
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

    <div class="mt-4">{{ $daftarGuru->links() }}</div>
@endsection
