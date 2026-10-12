@extends('layouts.app')

@section('judul', 'Profil Saya')

@section('konten')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="card bg-base-100 shadow">
        <div class="card-body">
            <h2 class="card-title">Profil Saya</h2>
            <table class="table table-sm">
                <tr><th class="w-40">Nama</th><td>{{ $pengguna->name }}</td></tr>
                <tr><th>Email</th><td>{{ $pengguna->email }}</td></tr>
                <tr><th>Peran</th><td><span class="badge badge-primary">{{ \App\Models\User::ROLES[$pengguna->role] ?? $pengguna->role }}</span></td></tr>
            </table>
        </div>
    </div>

    <div class="card bg-base-100 shadow">
        <div class="card-body">
            <h2 class="card-title">Ganti Password</h2>
            <p class="text-sm opacity-70">Password minimal 8 karakter. Setelah diganti, gunakan password baru saat login berikutnya.</p>
            <form method="POST" action="{{ route('profil.password') }}" class="space-y-4 mt-2">
                @csrf
                @method('PUT')
                <fieldset class="fieldset w-full">
                    <legend class="fieldset-legend font-semibold">Password Lama</legend>
                    <input type="password" name="password_lama" required class="input w-full" autocomplete="current-password">
                    @error('password_lama')<span class="text-error text-sm">{{ $message }}</span>@enderror
                </fieldset>
                <fieldset class="fieldset w-full">
                    <legend class="fieldset-legend font-semibold">Password Baru</legend>
                    <input type="password" name="password" required class="input w-full" autocomplete="new-password">
                    @error('password')<span class="text-error text-sm">{{ $message }}</span>@enderror
                </fieldset>
                <fieldset class="fieldset w-full">
                    <legend class="fieldset-legend font-semibold">Ulangi Password Baru</legend>
                    <input type="password" name="password_confirmation" required class="input w-full" autocomplete="new-password">
                </fieldset>
                <button type="submit" class="btn btn-primary">Simpan Password Baru</button>
            </form>
        </div>
    </div>
</div>
@endsection
