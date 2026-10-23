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

                <h2 class="text-base font-bold">Identitas Sekolah</h2>
                <p class="text-sm opacity-70 mb-3">Data pokok sekolah sesuai Dapodik. NPSN menjadi kunci pencocokan saat impor &amp; sinkronisasi.</p>
                <div class="grid gap-4 sm:grid-cols-2">
                    <fieldset class="fieldset w-full">
                        <label class="fieldset-legend font-semibold" for="f-npsn">NPSN</label>
                        <input id="f-npsn" type="text" name="npsn" value="{{ old('npsn', $sekolah->npsn) }}" required
                               class="input w-full" placeholder="20100001">
                    @error('npsn')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    </fieldset>
                    <fieldset class="fieldset w-full">
                        <label class="fieldset-legend font-semibold" for="f-nama">Nama Sekolah</label>
                        <input id="f-nama" type="text" name="nama" value="{{ old('nama', $sekolah->nama) }}" required
                               class="input w-full" placeholder="SDN 1 Sukamaju">
                    @error('nama')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    </fieldset>
                    <fieldset class="fieldset w-full">
                        <label class="fieldset-legend font-semibold" for="f-jenjang">Jenjang</label>
                        <select id="f-jenjang" name="jenjang" class="select w-full">
                            <option value="">— Otomatis dari nama —</option>
                            @foreach (['SD', 'SMP'] as $j)
                                <option value="{{ $j }}"
                                    {{ old('jenjang', $sekolah->jenjang) === $j ? 'selected' : '' }}>{{ $j }}</option>
                            @endforeach
                        </select>
                    @error('jenjang')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    </fieldset>
                    <fieldset class="fieldset w-full">
                        <label class="fieldset-legend font-semibold" for="f-rombel">Jumlah Rombel</label>
                        <input id="f-rombel" type="number" name="rombel" value="{{ old('rombel', $sekolah->rombel) }}" min="0" max="300"
                               class="input w-full" placeholder="mis. 6">
                        <legend class="fieldset-legend-alt">Dari Dapodik (kolom Rombel di CSV ekspor kecamatan) — bahan hitung analisis kebutuhan.</legend>
                    @error('rombel')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    </fieldset>
                </div>

                <h2 class="text-base font-bold mt-6">Lokasi &amp; Koordinat</h2>
                <p class="text-sm opacity-70 mb-3">Alamat dan titik koordinat sekolah — dasar perhitungan jarak domisili guru. Tentukan titik lewat peta di bawah, lalu verifikasi agar sekolah dapat menjadi tujuan rekomendasi.</p>
                <div class="grid gap-4 sm:grid-cols-2">
                    <fieldset class="fieldset w-full sm:col-span-2">
                        <label class="fieldset-legend font-semibold" for="f-alamat">Alamat</label>
                        <input id="f-alamat" type="text" name="alamat" value="{{ old('alamat', $sekolah->alamat) }}" required
                               class="input w-full" placeholder="Jl. Pendidikan No.1">
                    @error('alamat')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
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
                         x-on:searchable-select-change="if ($event.detail.name === 'kecamatan') { muatDesa($event.detail.value); petaTerbangKeWilayah('peta-sekolah', { kecamatan: petaKodeKecamatan[($event.detail.value || '').toUpperCase().trim()] ?? '' }, 12); }"
                         x-on:desa-terpilih="petaTerbangKeDesa('peta-sekolah', $event.detail)">
                    <fieldset class="fieldset w-full sm:col-span-2">
                        <label class="fieldset-legend font-semibold" for="f-kecamatan">Kecamatan</label>
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
                    @error('kecamatan')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    </fieldset>
                    <fieldset class="fieldset w-full">
                        <label class="fieldset-legend font-semibold" for="f-desa_kelurahan">Desa / Kelurahan</label>
                        <x-desa-select name="desa_kelurahan"
                               :selected="old('desa_kelurahan', $sekolah->desa_kelurahan)" />
                    @error('desa_kelurahan')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    </fieldset>
                    </div>
                    <fieldset class="fieldset w-full">
                        <label class="fieldset-legend font-semibold" for="f-kategori_daerah">Kategori Daerah</label>
                        <select id="f-kategori_daerah" name="kategori_daerah" class="select w-full">
                            @foreach (\App\Models\Sekolah::KATEGORI_DAERAH as $kode => $label)
                                <option value="{{ $kode }}"
                                    {{ old('kategori_daerah', $sekolah->kategori_daerah ?? 'biasa') === $kode ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        <legend class="fieldset-legend-alt">Guru di sekolah Terpencil/3T dilindungi dari mutasi keluar lewat SPK (rem pemerataan).</legend>
                    @error('kategori_daerah')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    </fieldset>
                    <fieldset class="fieldset w-full">
                        <label class="fieldset-legend font-semibold" for="lat">Lintang (Latitude)</label>
                        <input type="text" id="lat" name="lat" value="{{ old('lat', $sekolah->lat) }}" required
                               class="input w-full" placeholder="0.7900">
                    @error('lat')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    </fieldset>
                    <fieldset class="fieldset w-full">
                        <label class="fieldset-legend font-semibold" for="lng">Bujur (Longitude)</label>
                        <input type="text" id="lng" name="lng" value="{{ old('lng', $sekolah->lng) }}" required
                               class="input w-full" placeholder="99.4100">
                    @error('lng')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
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

                @if ($sekolah->exists)
                    {{-- Status verifikasi koordinat + formulir verifikasi.
                         Otorisasi ditegakkan controller; tampilan hanya
                         menyesuaikan peran yang berwenang. --}}
                    @php
                        $bolehVerifikasi = auth()->user()->hasRole('kabid_ptk', 'kadis')
                            || (auth()->user()->hasRole('kepala_sekolah') && (int) auth()->user()->sekolah_id === (int) $sekolah->id);
                    @endphp
                    <div class="alert {{ $sekolah->koordinatTerverifikasi() ? 'alert-success' : 'alert-warning' }} mt-3">
                        <div class="w-full">
                            <p class="font-semibold flex items-center gap-1.5"><x-ikon nama="lokasi" class="h-4 w-4 shrink-0" /> {{ $sekolah->labelStatusKoordinat() }}</p>
                            @if ($sekolah->koordinatTerverifikasi())
                                <p class="text-sm">Diverifikasi {{ $sekolah->koordinat_terverifikasi_pada?->format('d/m/Y H:i') }}
                                    oleh {{ $sekolah->verifikatorKoordinat?->name ?? 'petugas' }}
                                    (sumber: {{ \App\Models\Sekolah::SUMBER_KOORDINAT[$sekolah->koordinat_sumber] ?? $sekolah->koordinat_sumber }}).
                                    @if ($sekolah->koordinat_verifikasi_catatan) Catatan: {{ $sekolah->koordinat_verifikasi_catatan }} @endif
                                </p>
                                <p class="text-sm">Mengubah titik koordinat akan membatalkan status terverifikasi ini.</p>
                            @else
                                <p class="text-sm">Sekolah dengan koordinat belum terverifikasi tidak dapat menjadi tujuan rekomendasi SPK maupun Peta Rekomendasi.</p>
                                @if ($bolehVerifikasi && $sekolah->lat !== null && $sekolah->lng !== null)
                                    <form method="POST" action="{{ route('sekolah.verifikasi-koordinat', $sekolah) }}" class="mt-2 flex flex-wrap gap-2 items-end">
                                        @csrf
                                        <fieldset class="fieldset">
                                            <label class="fieldset-legend" for="koordinat_sumber">Sumber verifikasi</label>
                                            <select id="koordinat_sumber" name="koordinat_sumber" class="select" required>
                                                @foreach (\App\Models\Sekolah::SUMBER_KOORDINAT as $kode => $label)
                                                    @continue($kode === 'konfirmasi_massal')
                                                    <option value="{{ $kode }}">{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </fieldset>
                                        <fieldset class="fieldset flex-1 min-w-52">
                                            <label class="fieldset-legend" for="koordinat_catatan">Catatan (opsional)</label>
                                            <input type="text" id="koordinat_catatan" name="koordinat_catatan" class="input w-full" maxlength="255" placeholder="mis. Dicek bersama operator sekolah">
                                        </fieldset>
                                        <button type="submit" class="btn btn-success"><x-ikon nama="centang" class="h-4 w-4" /> Tandai Terverifikasi</button>
                                    </form>
                                @endif
                            @endif
                        </div>
                    </div>
                @endif

                <h2 class="text-base font-bold mt-6">Kebutuhan Guru per Mata Pelajaran</h2>
                <p class="text-sm opacity-70 mb-3">Formasi yang dibutuhkan sekolah — menjadi tujuan rekomendasi SPK &amp; Peta Rekomendasi. Kosongkan bila sudah memakai analisis massal berbasis rombel.</p>
                @if ($errors->has('kebutuhan.*'))
                    <p class="text-error text-xs mb-2">{{ $errors->first('kebutuhan.*') }}</p>
                @endif

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
                                   aria-label="Mata pelajaran kebutuhan"
                                   placeholder="Mata pelajaran (mis. Matematika)"
                                   class="input flex-1">
                            <input type="number" :name="'kebutuhan[' + i + '][jumlah]'" x-model="b.jumlah"
                                   aria-label="Jumlah kebutuhan"
                                   min="1" placeholder="Jumlah" class="input w-28">
                            <button type="button" @click="baris.splice(i, 1)"
                                    class="btn btn-error btn-outline" title="Hapus baris" aria-label="Hapus baris"><x-ikon nama="silang" class="h-4 w-4" /></button>
                        </div>
                    </template>
                    <button type="button" @click="baris.push({mapel: '', jumlah: 1})"
                            class="btn btn-sm btn-secondary mt-1"><x-ikon nama="tambah" class="h-4 w-4" /> Tambah Mapel</button>
                </div>

                <div class="flex gap-2 mt-6">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <a href="{{ route('sekolah.index') }}" class="btn btn-ghost">Batal</a>
                </div>
            </form>
        </div>
    </div>
@endsection
