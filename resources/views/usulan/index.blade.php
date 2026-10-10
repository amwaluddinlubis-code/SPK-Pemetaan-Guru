{{-- Daftar usulan penempatan guru --}}
@extends('layouts.app')
@section('judul', 'Usulan Penempatan — PetaPenempatan ASN')

@section('konten')
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold">📝 Usulan Penempatan</h1>
        @if (auth()->user()->hasRole('guru', 'kepala_sekolah', 'kabid_ptk'))
            <a href="{{ route('usulan.create') }}" class="btn btn-primary">+ Ajukan Usulan</a>
        @endif
    </div>

    <div class="card bg-base-100 shadow-xl mb-4">
        <div class="card-body py-3">
            {{-- Pencarian + filter status (GET, dibawa saat pindah halaman) --}}
            <form method="GET" action="{{ route('usulan.index') }}" class="flex flex-wrap gap-2">
                <input type="text" name="q" value="{{ request('q') }}"
                       placeholder="Cari nama/NIP guru…"
                       class="input input-bordered flex-1 min-w-52">
                <select name="status" class="select select-bordered max-w-xs">
                    <option value="">— Semua Status —</option>
                    @foreach (\App\Models\UsulanPenempatan::STATUS as $nilai => $label)
                        <option value="{{ $nilai }}" {{ request('status') == $nilai ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-primary">🔍 Cari</button>
                @if (request('q') || request('status'))
                    <a href="{{ route('usulan.index') }}" class="btn btn-ghost">Reset</a>
                @endif
            </form>
            @if (request('q') || request('status'))
                <p class="text-sm opacity-60 mt-2">Menampilkan {{ $daftarUsulan->total() }} hasil.</p>
            @endif
        </div>
    </div>

    <div class="card bg-base-100 shadow-xl">
        <div class="card-body p-0 overflow-x-auto">
            <table class="table table-zebra">
                <thead>
                    <tr>
                        <th>Guru</th>
                        <th>Sekolah Tujuan</th>
                        <th>Status</th>
                        <th>Diajukan Oleh</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($daftarUsulan as $u)
                        <tr>
                            <td>
                                <strong>{{ $u->guru->nama }}</strong><br>
                                <span class="text-sm opacity-60">{{ $u->guru->nip }} • {{ $u->guru->mapel }}</span>
                            </td>
                            <td class="text-sm">{{ $u->sekolah->nama }}</td>
                            <td><span class="badge {{ $u->badgeStatus() }}">{{ $u->labelStatus() }}</span></td>
                            <td class="text-sm">{{ $u->pengaju->name ?? '—' }}</td>
                            <td class="text-right">
                                <a href="{{ route('usulan.show', $u) }}" class="btn btn-sm btn-info">Lihat</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center opacity-60 py-8">Tidak ada usulan yang cocok.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $daftarUsulan->links() }}</div>
@endsection
