@extends('layouts.app')

@section('judul', 'Profil Saya — PetaPenempatan ASN')

@section('konten')
<h1 class="text-2xl font-bold mb-4">👤 Profil Saya</h1>
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
                    <label class="fieldset-legend font-semibold" for="f-password_lama">Password Lama</label>
                    <input id="f-password_lama" type="password" name="password_lama" required class="input w-full" autocomplete="current-password">
                    @error('password_lama')<span class="text-error text-sm">{{ $message }}</span>@enderror
                </fieldset>
                <fieldset class="fieldset w-full">
                    <label class="fieldset-legend font-semibold" for="f-password">Password Baru</label>
                    <input id="f-password" type="password" name="password" required class="input w-full" autocomplete="new-password">
                    @error('password')<span class="text-error text-sm">{{ $message }}</span>@enderror
                </fieldset>
                <fieldset class="fieldset w-full">
                    <label class="fieldset-legend font-semibold" for="f-password_confirmation">Ulangi Password Baru</label>
                    <input id="f-password_confirmation" type="password" name="password_confirmation" required class="input w-full" autocomplete="new-password">
                </fieldset>
                <button type="submit" class="btn btn-primary">Simpan Password Baru</button>
            </form>
        </div>
    </div>
</div>
@endsection
