{{-- Form ubah profil (nama & email) --}}
@extends('layouts.app')
@section('judul', 'Ubah Profil — PetaPenempatan ASN')

@section('konten')
    <h1 class="text-2xl font-bold mb-4">✏️ Ubah Profil</h1>

    <div class="card bg-base-100 shadow-xl max-w-2xl">
        <div class="card-body">
            <x-galat />

            <form method="POST" action="{{ route('profil.update') }}">
                @csrf @method('PUT')

                <label class="form-control w-full mb-4">
                    <span class="label-text font-semibold">Nama</span>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}"
                           required class="input input-bordered w-full">
                </label>

                <label class="form-control w-full mb-4">
                    <span class="label-text font-semibold">Email</span>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}"
                           required class="input input-bordered w-full">
                </label>

                <div class="flex gap-2 mt-2">
                    <button type="submit" class="btn btn-primary">💾 Simpan</button>
                    <a href="{{ route('profil.show') }}" class="btn btn-ghost">Batal</a>
                </div>
            </form>
        </div>
    </div>
@endsection
