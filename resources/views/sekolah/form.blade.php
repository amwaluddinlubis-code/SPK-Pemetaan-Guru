{{-- Form tambah/ubah sekolah + kebutuhan guru (baris dinamis dengan Alpine.js) --}}
@extends('layouts.app')
@section('judul', $aksi . ' Sekolah — PetaPenempatan ASN')

@section('konten')
    <h1 class="text-2xl font-bold mb-4">{{ $aksi }} Data Sekolah</h1>

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

            <form method="POST"
                  action="{{ $sekolah->exists ? route('sekolah.update', $sekolah) : route('sekolah.store') }}">
                @csrf
                @if ($sekolah->exists) @method('PUT') @endif

                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="form-control w-full">
                        <span class="label-text font-semibold">NPSN</span>
                        <input type="text" name="npsn" value="{{ old('npsn', $sekolah->npsn) }}" required
                               class="input input-bordered w-full" placeholder="20100001">
                    </label>
                    <label class="form-control w-full">
                        <span class="label-text font-semibold">Nama Sekolah</span>
                        <input type="text" name="nama" value="{{ old('nama', $sekolah->nama) }}" required
                               class="input input-bordered w-full" placeholder="SDN 1 Sukamaju">
                    </label>
                    <label class="form-control w-full sm:col-span-2">
                        <span class="label-text font-semibold">Alamat</span>
                        <input type="text" name="alamat" value="{{ old('alamat', $sekolah->alamat) }}" required
                               class="input input-bordered w-full" placeholder="Jl. Pendidikan No.1">
                    </label>
                    <label class="form-control w-full sm:col-span-2">
                        <span class="label-text font-semibold">Kecamatan</span>
                        @php
                            $kecAktif = old('kecamatan', $sekolah->kecamatan);
                            $opsiKec = $daftarKecamatan->contains($kecAktif) || empty($kecAktif)
                                ? $daftarKecamatan
                                : $daftarKecamatan->concat([$kecAktif])->sort()->values();
                        @endphp
                        <select name="kecamatan" class="select select-bordered w-full">
                            <option value="">— Pilih Kecamatan —</option>
                            @foreach ($opsiKec as $kec)
                                <option value="{{ $kec }}" {{ $kecAktif == $kec ? 'selected' : '' }}>
                                    {{ $kec }}
                                </option>
                            @endforeach
                        </select>
                    </label>
                    <label class="form-control w-full">
                        <span class="label-text font-semibold">Latitude</span>
                        <input type="text" id="lat" name="lat" value="{{ old('lat', $sekolah->lat) }}" required
                               class="input input-bordered w-full" placeholder="0.7900">
                    </label>
                    <label class="form-control w-full">
                        <span class="label-text font-semibold">Longitude</span>
                        <input type="text" id="lng" name="lng" value="{{ old('lng', $sekolah->lng) }}" required
                               class="input input-bordered w-full" placeholder="99.4100">
                    </label>
                </div>

                {{-- Peta pemilih koordinat sekolah (default: tengah Kab. Madina) --}}
                <div class="mt-3">
                    @include('komponen.peta-picker', [
                        'idPeta' => 'peta-sekolah',
                        'inputLat' => 'lat',
                        'inputLng' => 'lng',
                        'lat' => (float) (old('lat', $sekolah->lat) ?: 0.79),
                        'lng' => (float) (old('lng', $sekolah->lng) ?: 99.41),
                    ])
                </div>

                <div class="divider">Kebutuhan Guru per Mata Pelajaran</div>

                {{-- Baris kebutuhan dinamis: Alpine.js untuk tambah/hapus baris --}}
                @php
                    // Data awal: dari input lama (jika validasi gagal) atau dari database
                    $awal = old('kebutuhan', $sekolah->kebutuhan->map(fn ($k) => [
                        'mapel' => $k->mapel, 'jumlah' => $k->jumlah,
                    ])->toArray());
                    if (empty($awal)) $awal = [['mapel' => '', 'jumlah' => 1]];
                @endphp
                <div x-data='{ "baris": @json(array_values($awal)) }'>
                    <template x-for="(b, i) in baris" :key="i">
                        <div class="flex gap-2 mb-2">
                            <input type="text" :name="'kebutuhan[' + i + '][mapel]'" x-model="b.mapel"
                                   placeholder="Mata pelajaran (mis. Matematika)"
                                   class="input input-bordered flex-1">
                            <input type="number" :name="'kebutuhan[' + i + '][jumlah]'" x-model="b.jumlah"
                                   min="1" placeholder="Jumlah" class="input input-bordered w-28">
                            <button type="button" @click="baris.splice(i, 1)"
                                    class="btn btn-error btn-outline" title="Hapus baris">✕</button>
                        </div>
                    </template>
                    <button type="button" @click="baris.push({mapel: '', jumlah: 1})"
                            class="btn btn-sm btn-secondary mt-1">+ Tambah Mapel</button>
                </div>

                <div class="flex gap-2 mt-6">
                    <button type="submit" class="btn btn-primary">💾 Simpan</button>
                    <a href="{{ route('sekolah.index') }}" class="btn btn-ghost">Batal</a>
                </div>
            </form>
        </div>
    </div>
@endsection
