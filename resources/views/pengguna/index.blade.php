{{-- Daftar akun pengguna (khusus kadis & kabid_ptk) --}}
@extends('layouts.app')
@section('judul', 'Kelola Pengguna — PetaPenempatan ASN')

@section('konten')
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold">👥 Kelola Pengguna</h1>
        <a href="{{ route('pengguna.create') }}" class="btn btn-primary">+ Tambah Pengguna</a>
    </div>

    @if ($errors->any())
        <div class="alert alert-error mb-4">
            <ul class="list-disc list-inside text-sm">
                @foreach ($errors->all() as $pesan)
                    <li>{{ $pesan }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card bg-base-100 shadow-xl">
        <div class="card-body p-0 overflow-x-auto">
            <table class="table table-zebra">
                <thead>
                    <tr>
                        <th>Nama / Email</th>
                        <th>Peran</th>
                        <th>Scope</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($daftarUser as $u)
                        <tr>
                            <td>
                                <strong>{{ $u->name }}</strong><br>
                                <span class="text-sm opacity-60">{{ $u->email }}</span>
                            </td>
                            <td><span class="badge badge-primary">{{ $u->labelRole() }}</span></td>
                            <td class="text-sm">
                                @if ($u->role === 'guru' && $u->guru)
                                    {{ $u->guru->nama }}
                                @elseif ($u->role === 'kepala_sekolah' && $u->sekolah)
                                    {{ $u->sekolah->nama }}
                                @elseif ($u->role === 'ketua_pgri')
                                    Kec. {{ $u->kecamatan }}
                                @else
                                    <span class="opacity-50">—</span>
                                @endif
                            </td>
                            <td class="text-right whitespace-nowrap">
                                <a href="{{ route('pengguna.edit', $u) }}" class="btn btn-sm btn-warning">Ubah</a>
                                <form method="POST" action="{{ route('pengguna.destroy', $u) }}" class="inline"
                                      onsubmit="return confirm('Hapus akun {{ $u->name }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-error">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center opacity-60 py-8">Belum ada pengguna.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $daftarUser->links() }}</div>
@endsection
