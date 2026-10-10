{{-- Form tambah/ubah guru (dipakai dua-duanya) --}}
@extends('layouts.app')
@section('judul', $aksi . ' Guru — PetaPenempatan ASN')

@section('konten')
    <h1 class="text-2xl font-bold mb-4">{{ $aksi }} Data Guru</h1>

    <div class="card bg-base-100 shadow-xl">
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

            {{-- Jika $guru->exists: mode ubah (PUT), jika tidak: mode tambah (POST) --}}
            <form method="POST"
                  action="{{ $guru->exists ? route('guru.update', $guru) : route('guru.store') }}">
                @csrf
                @if ($guru->exists) @method('PUT') @endif

                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="form-control w-full">
                        <span class="label-text font-semibold">NIP</span>
                        <input type="text" name="nip" value="{{ old('nip', $guru->nip) }}" required
                               class="input input-bordered w-full" placeholder="198501012010011001">
                    </label>
                    <label class="form-control w-full">
                        <span class="label-text font-semibold">Nama Lengkap</span>
                        <input type="text" name="nama" value="{{ old('nama', $guru->nama) }}" required
                               class="input input-bordered w-full" placeholder="Siti Aminah">
                    </label>
                    <label class="form-control w-full">
                        <span class="label-text font-semibold">Mata Pelajaran</span>
                        <input type="text" name="mapel" value="{{ old('mapel', $guru->mapel) }}" required
                               class="input input-bordered w-full" placeholder="Matematika">
                    </label>
                    <label class="form-control w-full">
                        <span class="label-text font-semibold">Jenjang</span>
                        <select name="jenjang" class="select select-bordered w-full" required>
                            @foreach (['SD', 'SMP', 'SMA'] as $j)
                                <option value="{{ $j }}" {{ old('jenjang', $guru->jenjang) == $j ? 'selected' : '' }}>
                                    {{ $j }}
                                </option>
                            @endforeach
                        </select>
                    </label>
                    <label class="form-control w-full">
                        <span class="label-text font-semibold">Kecamatan Domisili</span>
                        <input type="text" name="kecamatan" value="{{ old('kecamatan', $guru->kecamatan) }}"
                               class="input input-bordered w-full" placeholder="mis. Sukamaju">
                    </label>
                    {{-- Sekolah tempat bertugas (administratif) --}}
                    @if (auth()->user()->hasRole('kabid_ptk'))
                        <label class="form-control w-full">
                            <span class="label-text font-semibold">Sekolah Tempat Bertugas</span>
                            <select name="sekolah_id" class="select select-bordered w-full">
                                <option value="">— Belum terdata di sistem —</option>
                                @foreach ($daftarSekolah as $s)
                                    <option value="{{ $s->id }}"
                                        {{ old('sekolah_id', $guru->sekolah_id) == $s->id ? 'selected' : '' }}>
                                        {{ $s->nama }}
                                    </option>
                                @endforeach
                            </select>
                        </label>
                    @else
                        {{-- Kepala sekolah & guru: tidak bisa mengubah relasi sekolah --}}
                        <input type="hidden" name="sekolah_id" value="{{ $guru->sekolah_id }}">
                    @endif
                </div>

                <div class="divider">Domisili (rumah)</div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="form-control w-full sm:col-span-2">
                        <span class="label-text font-semibold">Alamat Domisili</span>
                        <input type="text" name="domisili_alamat"
                               value="{{ old('domisili_alamat', $guru->domisili_alamat) }}" required
                               class="input input-bordered w-full" placeholder="Jl. Mawar No.10, Kec. Sukamaju">
                    </label>
                    <label class="form-control w-full">
                        <span class="label-text font-semibold">Latitude</span>
                        <input type="text" name="domisili_lat"
                               value="{{ old('domisili_lat', $guru->domisili_lat) }}" required
                               class="input input-bordered w-full" placeholder="-6.9180">
                    </label>
                    <label class="form-control w-full">
                        <span class="label-text font-semibold">Longitude</span>
                        <input type="text" name="domisili_lng"
                               value="{{ old('domisili_lng', $guru->domisili_lng) }}" required
                               class="input input-bordered w-full" placeholder="107.6200">
                    </label>
                </div>
                <p class="text-xs opacity-60 mt-1">
                    💡 Tips: buka Google Maps, klik kanan lokasi rumah → salin koordinatnya.
                </p>

                <div class="divider">Sekolah tempat bertugas saat ini</div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="form-control w-full sm:col-span-2">
                        <span class="label-text font-semibold">Nama Sekolah</span>
                        <input type="text" name="sekolah_saat_ini"
                               value="{{ old('sekolah_saat_ini', $guru->sekolah_saat_ini) }}" required
                               class="input input-bordered w-full" placeholder="SDN 5 Cijauh">
                    </label>
                    <label class="form-control w-full">
                        <span class="label-text font-semibold">Latitude Sekolah</span>
                        <input type="text" name="sekolah_saat_ini_lat"
                               value="{{ old('sekolah_saat_ini_lat', $guru->sekolah_saat_ini_lat) }}"
                               class="input input-bordered w-full" placeholder="-6.9800">
                    </label>
                    <label class="form-control w-full">
                        <span class="label-text font-semibold">Longitude Sekolah</span>
                        <input type="text" name="sekolah_saat_ini_lng"
                               value="{{ old('sekolah_saat_ini_lng', $guru->sekolah_saat_ini_lng) }}"
                               class="input input-bordered w-full" placeholder="107.7000">
                    </label>
                </div>

                <div class="flex gap-2 mt-6">
                    <button type="submit" class="btn btn-primary">💾 Simpan</button>
                    <a href="{{ route('guru.index') }}" class="btn btn-ghost">Batal</a>
                </div>
            </form>
        </div>
    </div>
@endsection
