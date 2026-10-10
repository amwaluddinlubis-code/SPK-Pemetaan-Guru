{{-- Daftar sekolah --}}
@extends('layouts.app')
@section('judul', 'Data Sekolah — PetaPenempatan ASN')

@section('konten')
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold">🏫 Data Sekolah</h1>
        @if (auth()->user()->hasRole('kabid_ptk'))
            <a href="{{ route('sekolah.create') }}" class="btn btn-primary">+ Tambah Sekolah</a>
        @endif
    </div>

    <div class="card bg-base-100 shadow-xl">
        <div class="card-body p-0 overflow-x-auto">
            <table class="table table-zebra">
                <thead>
                    <tr>
                        <th>Nama / NPSN</th>
                        <th>Alamat</th>
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
                        <tr><td colspan="4" class="text-center opacity-60 py-8">Belum ada data sekolah.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $daftarSekolah->links() }}</div>
@endsection
