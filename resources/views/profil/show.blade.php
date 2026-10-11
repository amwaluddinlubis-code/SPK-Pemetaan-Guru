{{-- Halaman profil pengguna --}}
@extends('layouts.app')
@section('judul', 'Profil Saya — PetaPenempatan ASN')

@section('konten')
    <h1 class="text-2xl font-bold mb-4">👤 Profil Saya</h1>

    <div class="card bg-base-100 shadow-xl max-w-2xl">
        <div class="card-body">
            <div class="flex items-center gap-4 mb-4">
                <div class="avatar placeholder">
                    <div class="bg-primary text-primary-content rounded-full w-16">
                        <span class="text-2xl">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                    </div>
                </div>
                <div>
                    <h2 class="text-xl font-bold">{{ $user->name }}</h2>
                    <span class="badge badge-accent">{{ $user->labelRole() }}</span>
                </div>
            </div>

            <table class="table">
                <tr><th class="w-40">Email</th><td>{{ $user->email }}</td></tr>
                <tr><th>Peran</th><td>{{ $user->labelRole() }}</td></tr>
                @if ($user->role === 'guru' && $user->guru)
                    <tr><th>Data Guru</th><td>{{ $user->guru->nama }} ({{ $user->guru->nip }})</td></tr>
                @endif
                @if ($user->role === 'kepala_sekolah' && $user->sekolah)
                    <tr><th>Sekolah</th><td>{{ $user->sekolah->nama }}</td></tr>
                @endif
                @if ($user->role === 'ketua_pgri')
                    <tr><th>Kecamatan</th><td>{{ $user->kecamatan }}</td></tr>
                @endif
                <tr><th>Terdaftar</th><td>{{ $user->created_at?->format('d M Y') }}</td></tr>
            </table>

            <div class="flex gap-2 mt-4">
                <a href="{{ route('profil.edit') }}" class="btn btn-warning">✏️ Ubah Profil</a>
                <a href="{{ route('profil.password') }}" class="btn btn-outline">🔑 Ganti Password</a>
            </div>
        </div>
    </div>
@endsection
