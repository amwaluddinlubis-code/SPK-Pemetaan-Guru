{{-- Daftar guru --}}
@extends('layouts.app')
@section('judul', 'Data Guru — PetaPenempatan ASN')

@section('konten')
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold">👩‍🏫 Data Guru</h1>
        @if (auth()->user()->hasRole('kabid_ptk', 'kepala_sekolah'))
            <a href="{{ route('guru.create') }}" class="btn btn-primary">+ Tambah Guru</a>
        @endif
    </div>

    <div class="card bg-base-100 shadow-xl">
        <div class="card-body p-0 overflow-x-auto">
            <table class="table table-zebra">
                <thead>
                    <tr>
                        <th>Nama / NIP</th>
                        <th>Mapel</th>
                        <th>Domisili</th>
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
                        <tr><td colspan="5" class="text-center opacity-60 py-8">Belum ada data guru.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $daftarGuru->links() }}</div>
@endsection
