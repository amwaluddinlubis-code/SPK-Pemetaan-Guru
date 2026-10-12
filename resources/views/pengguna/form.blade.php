{{-- Form tambah/ubah pengguna --}}
@extends('layouts.app')
@section('judul', $aksi . ' Pengguna — PetaPenempatan ASN')

@section('konten')
    <h1 class="text-2xl font-bold mb-4">{{ $aksi }} Pengguna</h1>

    <div class="card bg-base-100 shadow-xl">
        <div class="card-body">
            <x-galat />

            <form method="POST"
                  action="{{ $user->exists ? route('pengguna.update', $user) : route('pengguna.store') }}">
                @csrf
                @if ($user->exists) @method('PUT') @endif

                <div class="grid gap-4 sm:grid-cols-2">
                    <fieldset class="fieldset w-full">
                        <legend class="fieldset-legend font-semibold">Nama</legend>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                               class="input w-full" placeholder="Nama lengkap">
                    </fieldset>
                    <fieldset class="fieldset w-full">
                        <legend class="fieldset-legend font-semibold">Email</legend>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                               class="input w-full" placeholder="nama@contoh.id">
                    </fieldset>
                    <fieldset class="fieldset w-full">
                        <legend class="fieldset-legend font-semibold">
                            Password {{ $user->exists ? '(kosongkan jika tidak diganti)' : '' }}
                        </legend>
                        <input type="password" name="password" {{ $user->exists ? '' : 'required' }}
                               class="input w-full" placeholder="Minimal 6 karakter">
                    </fieldset>
                    <fieldset class="fieldset w-full">
                        <legend class="fieldset-legend font-semibold">Peran</legend>
                        <select name="role" id="pilih-role" class="select w-full" required>
                            @foreach (\App\Models\User::ROLES as $nilai => $label)
                                <option value="{{ $nilai }}"
                                    {{ old('role', $user->role) == $nilai ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </fieldset>
                </div>

                <div class="divider">Scope (sesuai peran)</div>
                <p class="text-sm opacity-60 mb-4">
                    Isi sesuai peran yang dipilih. Dikosongkan = tidak dibatasi.
                </p>

                <div class="grid gap-4 sm:grid-cols-2">
                    {{-- Hanya relevan jika peran = guru --}}
                    <fieldset class="fieldset w-full" id="field-guru">
                        <legend class="fieldset-legend font-semibold">Terhubung ke Guru</legend>
                        <select name="guru_id" class="select w-full">
                            <option value="">— Tidak ada —</option>
                            @foreach ($daftarGuru as $g)
                                <option value="{{ $g->id }}"
                                    {{ old('guru_id', $user->guru_id) == $g->id ? 'selected' : '' }}>
                                    {{ $g->nama }} ({{ $g->nip }})
                                </option>
                            @endforeach
                        </select>
                    </fieldset>

                    {{-- Hanya relevan jika peran = kepala_sekolah --}}
                    <fieldset class="fieldset w-full" id="field-sekolah">
                        <legend class="fieldset-legend font-semibold">Terhubung ke Sekolah</legend>
                        <select name="sekolah_id" class="select w-full">
                            <option value="">— Tidak ada —</option>
                            @foreach ($daftarSekolah as $s)
                                <option value="{{ $s->id }}"
                                    {{ old('sekolah_id', $user->sekolah_id) == $s->id ? 'selected' : '' }}>
                                    {{ $s->nama }}
                                </option>
                            @endforeach
                        </select>
                    </fieldset>

                    {{-- Hanya relevan jika peran = ketua_pgri --}}
                    <fieldset class="fieldset w-full sm:col-span-2" id="field-kecamatan">
                        <legend class="fieldset-legend font-semibold">Kecamatan (untuk Ketua PGRI)</legend>
                        @php $kecAktif = old('kecamatan', $user->kecamatan); @endphp
                        <select name="kecamatan" class="select w-full">
                            <option value="">— Pilih Kecamatan —</option>
                            @foreach ($daftarKecamatan as $kec)
                                <option value="{{ $kec }}" {{ $kecAktif == $kec ? 'selected' : '' }}>
                                    {{ $kec }}
                                </option>
                            @endforeach
                            @if ($kecAktif && ! $daftarKecamatan->contains($kecAktif))
                                <option value="{{ $kecAktif }}" selected>{{ $kecAktif }}</option>
                            @endif
                        </select>
                    </fieldset>
                </div>

                <div class="flex gap-2 mt-6">
                    <button type="submit" class="btn btn-primary">💾 Simpan</button>
                    <a href="{{ route('pengguna.index') }}" class="btn btn-ghost">Batal</a>
                </div>
            </form>
        </div>
    </div>

    <script>
    // Tampilkan hanya field scope yang relevan dengan peran terpilih
    (function () {
        const pilih = document.getElementById('pilih-role');
        const field = {
            guru: document.getElementById('field-guru'),
            kepala_sekolah: document.getElementById('field-sekolah'),
            ketua_pgri: document.getElementById('field-kecamatan'),
        };
        function sesuaikan() {
            const peran = pilih.value;
            field.guru.style.display = peran === 'guru' ? '' : 'none';
            field.kepala_sekolah.style.display = peran === 'kepala_sekolah' ? '' : 'none';
            field.ketua_pgri.style.display = peran === 'ketua_pgri' ? '' : 'none';
        }
        pilih.addEventListener('change', sesuaikan);
        sesuaikan();
    })();
    </script>
@endsection
