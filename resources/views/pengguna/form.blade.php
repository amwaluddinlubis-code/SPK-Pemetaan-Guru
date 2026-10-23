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
                        <label class="fieldset-legend font-semibold" for="f-name">Nama</label>
                        <input id="f-name" type="text" name="name" value="{{ old('name', $user->name) }}" required
                               class="input w-full" placeholder="Nama lengkap">
                    </fieldset>
                    <fieldset class="fieldset w-full">
                        <label class="fieldset-legend font-semibold" for="f-email">Email</label>
                        <input id="f-email" type="email" name="email" value="{{ old('email', $user->email) }}" required
                               class="input w-full" placeholder="nama@contoh.id">
                    </fieldset>
                    <fieldset class="fieldset w-full">
                        <label class="fieldset-legend font-semibold" for="f-password">
                            Password {{ $user->exists ? '(kosongkan jika tidak diganti)' : '' }}
                        </label>
                        <input id="f-password" type="password" name="password" {{ $user->exists ? '' : 'required' }}
                               class="input w-full" placeholder="Minimal 6 karakter">
                    </fieldset>
                    <fieldset class="fieldset w-full">
                        <label class="fieldset-legend font-semibold" for="pilih-role">Peran</label>
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
                        <label class="fieldset-legend font-semibold" for="f-guru_id">Terhubung ke Guru</label>
                        <select id="f-guru_id" name="guru_id" class="select w-full">
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
                        <label class="fieldset-legend font-semibold" for="f-sekolah_id">Terhubung ke Sekolah</label>
                        <x-searchable-select name="sekolah_id"
                            :options="$daftarSekolah->map(fn ($s) => ['value' => $s->id, 'label' => $s->nama])->all()"
                            :selected="old('sekolah_id', $user->sekolah_id)"
                            placeholder="— Tidak ada —" class="w-full" />
                    </fieldset>

                    {{-- Hanya relevan jika peran = ketua_pgri --}}
                    <fieldset class="fieldset w-full sm:col-span-2" id="field-kecamatan">
                        <label class="fieldset-legend font-semibold" for="f-kecamatan">Kecamatan (untuk Ketua PGRI)</label>
                        @php $kecAktif = old('kecamatan', $user->kecamatan); @endphp
                        <x-searchable-select name="kecamatan"
                            :options="($kecAktif && ! $daftarKecamatan->contains($kecAktif)
                                ? $daftarKecamatan->concat([$kecAktif])->sort()->values()
                                : $daftarKecamatan)->map(fn ($k) => ['value' => $k, 'label' => $k])->all()"
                            :selected="$kecAktif"
                            placeholder="— Pilih Kecamatan —" class="w-full" />
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
