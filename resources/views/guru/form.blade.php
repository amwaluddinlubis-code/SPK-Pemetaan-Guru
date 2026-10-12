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

                <div class="grid gap-4 sm:grid-cols-2" data-testid="form-guru-berseksi">
                    <h3 class="sm:col-span-2 font-bold text-lg border-b pb-2 mt-2">Data Pribadi</h3>
                    <fieldset class="fieldset w-full">
                        <legend class="fieldset-legend font-semibold">NIP</legend>
                        <input type="text" name="nip" value="{{ old('nip', $guru->nip) }}" required
                               class="input w-full" placeholder="198501012010011001">
                    </fieldset>
                    <fieldset class="fieldset w-full">
                        <legend class="fieldset-legend font-semibold">Nama Lengkap</legend>
                        <input type="text" name="nama" value="{{ old('nama', $guru->nama) }}" required
                               class="input w-full" placeholder="Siti Aminah">
                    </fieldset>
                    <fieldset class="fieldset w-full">
                    <h3 class="sm:col-span-2 font-bold text-lg border-b pb-2 mt-4">Kepegawaian</h3>
                        <legend class="fieldset-legend font-semibold">Mata Pelajaran</legend>
                        <input type="text" name="mapel" value="{{ old('mapel', $guru->mapel) }}" required
                               class="input w-full" placeholder="Matematika">
                    </fieldset>
                    <fieldset class="fieldset w-full">
                        <legend class="fieldset-legend font-semibold">Jenjang</legend>
                        <select name="jenjang" class="select w-full" required>
                            @foreach (['SD', 'SMP', 'SMA'] as $j)
                                <option value="{{ $j }}" {{ old('jenjang', $guru->jenjang) == $j ? 'selected' : '' }}>
                                    {{ $j }}
                                </option>
                            @endforeach
                        </select>
                    </fieldset>
                    <fieldset class="fieldset w-full">
                    <h3 class="sm:col-span-2 font-bold text-lg border-b pb-2 mt-4">Domisili</h3>
                        <legend class="fieldset-legend font-semibold">Kecamatan Domisili</legend>
                        @php
                            // Nilai kecamatan saat ini (tetap ditampilkan walau tak ada di daftar)
                            $kecAktif = old('kecamatan', $guru->kecamatan);
                            $opsiKec = $daftarKecamatan->contains($kecAktif) || empty($kecAktif)
                                ? $daftarKecamatan
                                : $daftarKecamatan->concat([$kecAktif])->sort()->values();
                        @endphp
                        <select name="kecamatan" class="select w-full">
                            <option value="">— Pilih Kecamatan —</option>
                            @foreach ($opsiKec as $kec)
                                <option value="{{ $kec }}" {{ $kecAktif == $kec ? 'selected' : '' }}>
                                    {{ $kec }}
                                </option>
                            @endforeach
                        </select>
                    </fieldset>
                    <fieldset class="fieldset w-full">
                        <legend class="fieldset-legend font-semibold">Desa / Kelurahan Domisili</legend>
                        <input type="text" name="desa_kelurahan"
                               value="{{ old('desa_kelurahan', $guru->desa_kelurahan) }}"
                               class="input w-full" placeholder="cth: Panyabungan Jae" maxlength="100">
                    </fieldset>
                    <fieldset class="fieldset w-full">
                        <legend class="fieldset-legend font-semibold">Status Pernikahan</legend>
                        <select name="status_pernikahan" class="select w-full">
                            @foreach (\App\Models\Guru::STATUS_PERNIKAHAN as $kode => $label)
                                <option value="{{ $kode }}"
                                    {{ old('status_pernikahan', $guru->status_pernikahan ?? 'belum_kawin') === $kode ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </fieldset>
                    {{-- Data kepegawaian (administratif: kabid/kepala sekolah) --}}
                    <fieldset class="fieldset w-full">
                        <legend class="fieldset-legend font-semibold">Status Kepegawaian</legend>
                        <select name="status_kepegawaian" class="select w-full">
                            @foreach (\App\Models\Guru::STATUS_KEPEGAWAIAN as $kode => $label)
                                <option value="{{ $kode }}"
                                    {{ old('status_kepegawaian', $guru->status_kepegawaian ?? 'pns') === $kode ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </fieldset>
                    <fieldset class="fieldset w-full">
                        <legend class="fieldset-legend font-semibold">Sertifikasi Pendidik</legend>
                        <select name="sudah_sertifikasi" class="select w-full">
                            <option value="0" {{ old('sudah_sertifikasi', $guru->sudah_sertifikasi ?? 0) == 0 ? 'selected' : '' }}>Belum sertifikasi</option>
                            <option value="1" {{ old('sudah_sertifikasi', $guru->sudah_sertifikasi ?? 0) == 1 ? 'selected' : '' }}>Sudah sertifikasi</option>
                        </select>
                    </fieldset>
                    <fieldset class="fieldset w-full">
                        <legend class="fieldset-legend font-semibold">Mapel Sertifikat (bila sudah sertifikasi)</legend>
                        <input type="text" name="mapel_sertifikasi"
                               value="{{ old('mapel_sertifikasi', $guru->mapel_sertifikasi) }}"
                               class="input w-full" placeholder="cth: Matematika" maxlength="50">
                    </fieldset>
                    {{-- Sekolah tempat bertugas (administratif) --}}
                    @if (auth()->user()->hasRole('kabid_ptk'))
                        <fieldset class="fieldset w-full">
                            <legend class="fieldset-legend font-semibold">Sekolah Tempat Bertugas</legend>
                            <select name="sekolah_id" class="select w-full">
                                <option value="">— Belum terdata di sistem —</option>
                                @foreach ($daftarSekolah as $s)
                                    <option value="{{ $s->id }}"
                                        {{ old('sekolah_id', $guru->sekolah_id) == $s->id ? 'selected' : '' }}>
                                        {{ $s->nama }}
                                    </option>
                                @endforeach
                            </select>
                        </fieldset>
                    @else
                        {{-- Kepala sekolah & guru: tidak bisa mengubah relasi sekolah --}}
                        <input type="hidden" name="sekolah_id" value="{{ $guru->sekolah_id }}">
                    @endif
                </div>

                <div class="divider">Domisili (rumah)</div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <fieldset class="fieldset w-full sm:col-span-2">
                        <legend class="fieldset-legend font-semibold">Alamat Domisili</legend>
                        <input type="text" name="domisili_alamat"
                               value="{{ old('domisili_alamat', $guru->domisili_alamat) }}" required
                               class="input w-full" placeholder="Jl. Mawar No.10, Kec. Sukamaju">
                    </fieldset>
                    <fieldset class="fieldset w-full">
                        <legend class="fieldset-legend font-semibold">Latitude</legend>
                        <input type="text" id="domisili_lat" name="domisili_lat"
                               value="{{ old('domisili_lat', $guru->domisili_lat) }}" required
                               class="input w-full" placeholder="0.7900">
                    </fieldset>
                    <fieldset class="fieldset w-full">
                        <legend class="fieldset-legend font-semibold">Longitude</legend>
                        <input type="text" id="domisili_lng" name="domisili_lng"
                               value="{{ old('domisili_lng', $guru->domisili_lng) }}" required
                               class="input w-full" placeholder="99.4100">
                    </fieldset>
                </div>

                {{-- Peta pemilih koordinat domisili (default: tengah Kab. Madina) --}}
                <div class="mt-3">
                    @include('komponen.peta-picker', [
                        'idPeta' => 'peta-domisili',
                        'inputLat' => 'domisili_lat',
                        'inputLng' => 'domisili_lng',
                        'lat' => (float) (old('domisili_lat', $guru->domisili_lat) ?: 0.79),
                        'lng' => (float) (old('domisili_lng', $guru->domisili_lng) ?: 99.41),
                    ])
                </div>
                <p class="text-xs opacity-60 mt-1">
                    💡 Tips: buka Google Maps, klik kanan lokasi rumah → salin koordinatnya.
                </p>

                <div class="divider">Sekolah tempat bertugas saat ini</div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <fieldset class="fieldset w-full sm:col-span-2">
                        <legend class="fieldset-legend font-semibold">Nama Sekolah</legend>
                        <input type="text" name="sekolah_saat_ini"
                               value="{{ old('sekolah_saat_ini', $guru->sekolah_saat_ini) }}" required
                               class="input w-full" placeholder="SDN 5 Cijauh">
                    </fieldset>
                    <fieldset class="fieldset w-full">
                        <legend class="fieldset-legend font-semibold">Latitude Sekolah</legend>
                        <input type="text" id="sekolah_saat_ini_lat" name="sekolah_saat_ini_lat"
                               value="{{ old('sekolah_saat_ini_lat', $guru->sekolah_saat_ini_lat) }}"
                               class="input w-full" placeholder="0.7900">
                    </fieldset>
                    <fieldset class="fieldset w-full">
                        <legend class="fieldset-legend font-semibold">Longitude Sekolah</legend>
                        <input type="text" id="sekolah_saat_ini_lng" name="sekolah_saat_ini_lng"
                               value="{{ old('sekolah_saat_ini_lng', $guru->sekolah_saat_ini_lng) }}"
                               class="input w-full" placeholder="99.4100">
                    </fieldset>
                </div>

                {{-- Peta pemilih koordinat sekolah tempat bertugas --}}
                <div class="mt-3">
                    @include('komponen.peta-picker', [
                        'idPeta' => 'peta-sekolah-saat-ini',
                        'inputLat' => 'sekolah_saat_ini_lat',
                        'inputLng' => 'sekolah_saat_ini_lng',
                        'lat' => (float) (old('sekolah_saat_ini_lat', $guru->sekolah_saat_ini_lat) ?: 0.79),
                        'lng' => (float) (old('sekolah_saat_ini_lng', $guru->sekolah_saat_ini_lng) ?: 99.41),
                    ])
                </div>

                <div class="flex gap-2 mt-6">
                    <button type="submit" class="btn btn-primary">💾 Simpan</button>
                    <a href="{{ route('guru.index') }}" class="btn btn-ghost">Batal</a>
                </div>
            </form>
        </div>
    </div>
@endsection
