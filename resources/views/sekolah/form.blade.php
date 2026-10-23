{{-- Form tambah/ubah sekolah + kebutuhan guru (baris dinamis dengan Alpine.js) --}}
@extends('layouts.app')
@section('judul', $aksi . ' Sekolah — PetaPenempatan ASN')

@section('konten')
    <h1 class="text-2xl font-bold mb-4">{{ $aksi }} Data Sekolah</h1>

    <div class="card bg-base-100 shadow-xl">
        <div class="card-body">
            <x-galat />

            <form method="POST"
                  action="{{ $sekolah->exists ? route('sekolah.update', $sekolah) : route('sekolah.store') }}">
                @csrf
                @if ($sekolah->exists) @method('PUT') @endif

                <div class="grid gap-4 sm:grid-cols-2">
                    <fieldset class="fieldset w-full">
                        <legend class="fieldset-legend font-semibold">NPSN</legend>
                        <input type="text" name="npsn" value="{{ old('npsn', $sekolah->npsn) }}" required
                               class="input w-full" placeholder="20100001">
                    </fieldset>
                    <fieldset class="fieldset w-full">
                        <legend class="fieldset-legend font-semibold">Nama Sekolah</legend>
                        <input type="text" name="nama" value="{{ old('nama', $sekolah->nama) }}" required
                               class="input w-full" placeholder="SDN 1 Sukamaju">
                    </fieldset>
                    <fieldset class="fieldset w-full">
                        <legend class="fieldset-legend font-semibold">Jenjang</legend>
                        <select name="jenjang" class="select w-full">
                            <option value="">— Otomatis dari nama —</option>
                            @foreach (['SD', 'SMP'] as $j)
                                <option value="{{ $j }}"
                                    {{ old('jenjang', $sekolah->jenjang) === $j ? 'selected' : '' }}>{{ $j }}</option>
                            @endforeach
                        </select>
                    </fieldset>
                    <fieldset class="fieldset w-full">
                        <legend class="fieldset-legend font-semibold">Jumlah Rombel</legend>
                        <input type="number" name="rombel" value="{{ old('rombel', $sekolah->rombel) }}" min="0" max="300"
                               class="input w-full" placeholder="mis. 6">
                        <legend class="fieldset-legend-alt">Dari Dapodik (kolom Rombel di CSV ekspor kecamatan) — bahan hitung analisis kebutuhan.</legend>
                    </fieldset>
                    <fieldset class="fieldset w-full sm:col-span-2">
                        <legend class="fieldset-legend font-semibold">Alamat</legend>
                        <input type="text" name="alamat" value="{{ old('alamat', $sekolah->alamat) }}" required
                               class="input w-full" placeholder="Jl. Pendidikan No.1">
                    </fieldset>
                    <div class="contents"
                         x-data="{
                            petaKodeKecamatan: @js($petaKodeKecamatan ?? []),
                            opsiDesa: [],
                            async muatDesa(nama) {
                                const kode = this.petaKodeKecamatan[(nama || '').toUpperCase().trim()] ?? null;
                                this.opsiDesa = [];
                                if (!kode) return;
                                try {
                                    const r = await fetch(@js(route('wilayah.api.desa')) + '?kecamatan=' + encodeURIComponent(kode), { headers: { 'Accept': 'application/json' } });
                                    if (r.ok) this.opsiDesa = await r.json();
                                } catch (e) { /* daftar desa gagal dimuat: tetap bisa ketik bebas */ }
                            },
                         }"
                         x-init="muatDesa(@js(old('kecamatan', $sekolah->kecamatan)))"
                         x-on:searchable-select-change="if ($event.detail.name === 'kecamatan') muatDesa($event.detail.value)">
                    <fieldset class="fieldset w-full sm:col-span-2">
                        <legend class="fieldset-legend font-semibold">Kecamatan</legend>
                        @php
                            $kecAktif = old('kecamatan', $sekolah->kecamatan);
                            $opsiKec = $daftarKecamatan->contains($kecAktif) || empty($kecAktif)
                                ? $daftarKecamatan
                                : $daftarKecamatan->concat([$kecAktif])->sort()->values();
                        @endphp
                        <x-searchable-select name="kecamatan"
                            :options="$opsiKec->map(fn ($k) => ['value' => $k, 'label' => $k])->all()"
                            :selected="$kecAktif"
                            placeholder="— Pilih Kecamatan —" class="w-full" />
                    </fieldset>
                    <fieldset class="fieldset w-full">
                        <legend class="fieldset-legend font-semibold">Desa / Kelurahan</legend>
                        <x-desa-select name="desa_kelurahan"
                               :selected="old('desa_kelurahan', $sekolah->desa_kelurahan)" />
                    </fieldset>
                    </div>
                    <fieldset class="fieldset w-full">
                        <legend class="fieldset-legend font-semibold">Kategori Daerah</legend>
                        <select name="kategori_daerah" class="select w-full">
                            @foreach (\App\Models\Sekolah::KATEGORI_DAERAH as $kode => $label)
                                <option value="{{ $kode }}"
                                    {{ old('kategori_daerah', $sekolah->kategori_daerah ?? 'biasa') === $kode ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        <legend class="fieldset-legend-alt">Guru di sekolah Terpencil/3T dilindungi dari mutasi keluar lewat SPK (rem pemerataan).</legend>
                    </fieldset>
                    <fieldset class="fieldset w-full">
                        <legend class="fieldset-legend font-semibold">Latitude</legend>
                        <input type="text" id="lat" name="lat" value="{{ old('lat', $sekolah->lat) }}" required
                               class="input w-full" placeholder="0.7900">
                    </fieldset>
                    <fieldset class="fieldset w-full">
                        <legend class="fieldset-legend font-semibold">Longitude</legend>
                        <input type="text" id="lng" name="lng" value="{{ old('lng', $sekolah->lng) }}" required
                               class="input w-full" placeholder="99.4100">
                    </fieldset>
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
                                   class="input flex-1">
                            <input type="number" :name="'kebutuhan[' + i + '][jumlah]'" x-model="b.jumlah"
                                   min="1" placeholder="Jumlah" class="input w-28">
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
