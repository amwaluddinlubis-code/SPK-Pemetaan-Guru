{{-- Form ganti password sendiri --}}
@extends('layouts.app')
@section('judul', 'Ganti Password — PetaPenempatan ASN')

@section('konten')
    <h1 class="text-2xl font-bold mb-4">🔑 Ganti Password</h1>

    <div class="card bg-base-100 shadow-xl max-w-2xl">
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-error">
                    <ul class="list-disc list-inside text-sm">
                        @foreach ($errors->all() as $pesan)
                            <li>{{ $pesan }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('profil.password.update') }}">
                @csrf @method('PUT')

                <label class="form-control w-full mb-4">
                    <span class="label-text font-semibold">Password Lama</span>
                    <input type="password" name="password_lama" required
                           class="input input-bordered w-full" placeholder="••••••••">
                </label>

                <label class="form-control w-full mb-4">
                    <span class="label-text font-semibold">Password Baru (min. 6 karakter)</span>
                    <input type="password" name="password" required
                           class="input input-bordered w-full" placeholder="••••••••">
                </label>

                <label class="form-control w-full mb-4">
                    <span class="label-text font-semibold">Ulangi Password Baru</span>
                    <input type="password" name="password_confirmation" required
                           class="input input-bordered w-full" placeholder="••••••••">
                </label>

                <div class="flex gap-2 mt-2">
                    <button type="submit" class="btn btn-primary">💾 Ganti Password</button>
                    <a href="{{ route('profil.show') }}" class="btn btn-ghost">Batal</a>
                </div>
            </form>
        </div>
    </div>
@endsection
