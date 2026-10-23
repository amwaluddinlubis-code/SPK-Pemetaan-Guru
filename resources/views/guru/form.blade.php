{{-- Form tambah/ubah guru (dipakai dua-duanya) — 3 tab: Data Pribadi, Kepegawaian, Domisili & Penempatan.
     Tab aktif awal otomatis pindah ke tab yang punya error validasi. --}}
@extends('layouts.app')
@section('judul', $aksi . ' Guru — PetaPenempatan ASN')

@php
    // Peta field -> tab, untuk menentukan tab awal saat ada error validasi
    $petaTab = [
        'nip' => 'pribadi', 'nama' => 'pribadi', 'status_pernikahan' => 'pribadi',
        'mapel' => 'kepegawaian', 'jenjang' => 'kepegawaian',
        'status_kepegawaian' => 'kepegawaian', 'sudah_sertifikasi' => 'kepegawaian',
        'sekolah_id' => 'kepegawaian', 'pendidikan_terakhir' => 'kepegawaian',
        'bidang_studi_serdik' => 'kepegawaian', 'nuptk' => 'pribadi',
        'tempat_lahir' => 'pribadi', 'tanggal_lahir' => 'pribadi',
        'kecamatan' => 'domisili', 'desa_kelurahan' => 'domisili',
        'domisili_alamat' => 'domisili', 'domisili_lat' => 'domisili', 'domisili_lng' => 'domisili',
    ];
    $tabAktif = 'pribadi';
    $peranSaya = auth()->user()->role;
    $nipTerkunci = in_array($peranSaya, ['guru', 'kepala_sekolah'], true);
    $pribadiTerkunci = $peranSaya === 'guru';
    $kepegawaianTerkunci = $peranSaya === 'guru';
    $hitungError = ['pribadi' => 0, 'kepegawaian' => 0, 'domisili' => 0];
    foreach ($errors->keys() as $f) {
        $t = $petaTab[$f] ?? null;
        if ($t) {
            $hitungError[$t]++;
            // Tab awal = tab dari error validasi pertama
            if ($tabAktif === 'pribadi' && array_sum($hitungError) === 1) {
                $tabAktif = $t;
            }
        }
    }
@endphp

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
                  action="{{ $guru->exists ? route('guru.update', $guru) : route('guru.store') }}"
                  x-data="{ tab: '{{ $tabAktif }}' }"
                  data-testid="form-guru-tab">
                @csrf
                @if ($guru->exists) @method('PUT') @endif

                {{-- Navigasi tab --}}
                <div class="tabs tabs-box mb-4" role="tablist" aria-label="Bagian form guru">
                    <button type="button" role="tab" :aria-selected="tab === 'pribadi'"
                            class="tab" :class="{ 'tab-active': tab === 'pribadi' }"
                            @click="tab = 'pribadi'">
                        1. Data Pribadi
                        @if ($hitungError['pribadi'] > 0)
                            <span class="badge badge-error badge-xs ml-1">{{ $hitungError['pribadi'] }}</span>
                        @endif
                    </button>
                    <button type="button" role="tab" :aria-selected="tab === 'kepegawaian'"
                            class="tab" :class="{ 'tab-active': tab === 'kepegawaian' }"
                            @click="tab = 'kepegawaian'">
                        2. Kepegawaian
                        @if ($hitungError['kepegawaian'] > 0)
                            <span class="badge badge-error badge-xs ml-1">{{ $hitungError['kepegawaian'] }}</span>
                        @endif
                    </button>
                    <button type="button" role="tab" :aria-selected="tab === 'domisili'"
                            class="tab" :class="{ 'tab-active': tab === 'domisili' }"
                            @click="tab = 'domisili'; $nextTick(() => Object.values(window.petaPickers || {}).forEach(m => m.invalidateSize()))">
                        3. Domisili & Penempatan
                        @if ($hitungError['domisili'] > 0)
                            <span class="badge badge-error badge-xs ml-1">{{ $hitungError['domisili'] }}</span>
                        @endif
                    </button>
                </div>

                {{-- TAB 1: Data Pribadi --}}
                <div x-show="tab === 'pribadi'" x-transition role="tabpanel">
                    <p class="text-sm opacity-70 mb-4">Identitas guru sesuai dokumen kepegawaian. NIP menjadi username &amp; kata sandi awal akun guru.</p>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <fieldset class="fieldset w-full">
                            <label class="fieldset-legend font-semibold" for="f-nip">NIP</label>
                            <input id="f-nip" type="text" name="nip" value="{{ old('nip', $guru->nip) }}"
                                   class="input w-full" placeholder="198501012010011001" @if ($nipTerkunci) disabled @endif>
                            <p class="text-xs opacity-70 mt-1">Boleh dikosongkan bila guru belum punya NIP (menyusul). NIP menjadi username &amp; password awal akun guru — tidak dapat diubah oleh guru.</p>
                        @error('nip')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                        </fieldset>
                        <fieldset class="fieldset w-full">
                            <label class="fieldset-legend font-semibold" for="f-nuptk">NUPTK</label>
                            <input id="f-nuptk" type="text" name="nuptk" value="{{ old('nuptk', $guru->nuptk) }}"
                                   class="input w-full" placeholder="Nomor Unik Pendidik dan Tenaga Kependidikan" @if ($pribadiTerkunci) disabled @endif>
                            <p class="text-xs opacity-70 mt-1">Syarat pendukung Tambahan Penghasilan bagi guru yang belum bersertifikat.</p>
                        @error('nuptk')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                        </fieldset>
                        <fieldset class="fieldset w-full">
                            <label class="fieldset-legend font-semibold" for="f-nama">Nama Lengkap</label>
                            <input id="f-nama" type="text" name="nama" value="{{ old('nama', $guru->nama) }}" required
                                   class="input w-full" placeholder="Siti Aminah">
                        @error('nama')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                        </fieldset>
                        <fieldset class="fieldset w-full">
                            <label class="fieldset-legend font-semibold" for="f-tempat_lahir">Tempat Lahir</label>
                            <input id="f-tempat_lahir" type="text" name="tempat_lahir" value="{{ old('tempat_lahir', $guru->tempat_lahir) }}"
                                   class="input w-full" placeholder="Mandailing Natal" @if ($pribadiTerkunci) disabled @endif>
                        @error('tempat_lahir')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                        </fieldset>
                        <fieldset class="fieldset w-full">
                            <label class="fieldset-legend font-semibold" for="f-tanggal_lahir">Tanggal Lahir</label>
                            <input id="f-tanggal_lahir" type="date" name="tanggal_lahir" @if ($pribadiTerkunci) disabled @endif
                                   value="{{ old('tanggal_lahir', $guru->tanggal_lahir ? \Illuminate\Support\Carbon::parse($guru->tanggal_lahir)->format('Y-m-d') : '') }}"
                                   class="input w-full">
                        @error('tanggal_lahir')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                        </fieldset>
                        <fieldset class="fieldset w-full">
                            <label class="fieldset-legend font-semibold" for="f-status_pernikahan">Status Pernikahan</label>
                            <select id="f-status_pernikahan" name="status_pernikahan" class="select w-full">
                                @foreach (\App\Models\Guru::STATUS_PERNIKAHAN as $kode => $label)
                                    <option value="{{ $kode }}"
                                        {{ old('status_pernikahan', $guru->status_pernikahan ?? 'belum_kawin') === $kode ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        @error('status_pernikahan')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                        </fieldset>
                    </div>
                    <div class="flex justify-end mt-4">
                        <button type="button" class="btn btn-primary" @click="tab = 'kepegawaian'">Lanjut →</button>
                    </div>
                </div>

                {{-- TAB 2: Kepegawaian --}}
                <div x-show="tab === 'kepegawaian'" x-transition role="tabpanel" x-cloak>
                    <p class="text-sm opacity-70 mb-4">Mata pelajaran, jenjang, status kepegawaian, dan sertifikasi — dasar penilaian linieritas penempatan (Kepmen 222/O/2025).</p>
                    @if ($kepegawaianTerkunci)
                        <div class="alert alert-info text-sm mb-4">Data kepegawaian hanya dapat diubah oleh Kabid PTK atau Kepala Sekolah — bagian ini ditampilkan untuk dibaca saja.</div>
                    @endif
                    <fieldset @if ($kepegawaianTerkunci) disabled @endif class="contents">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <fieldset class="fieldset w-full">
                            <label class="fieldset-legend font-semibold" for="f-mapel">Mata Pelajaran</label>
                            @php
                                $mapelAktif = old('mapel', $guru->mapel);
                                $opsiMapel = collect($daftarMapel);
                                if ($mapelAktif && ! $opsiMapel->contains($mapelAktif)) {
                                    $opsiMapel = $opsiMapel->push($mapelAktif);
                                }
                            @endphp
                            <x-searchable-select name="mapel"
                                :options="$opsiMapel->map(fn ($m) => ['value' => $m, 'label' => $m])->all()"
                                :selected="$mapelAktif"
                                placeholder="— Pilih Mata Pelajaran —" class="w-full" />
                        @error('mapel')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                        </fieldset>
                        <fieldset class="fieldset w-full">
                            <label class="fieldset-legend font-semibold" for="f-jenjang">Jenjang</label>
                            <select id="f-jenjang" name="jenjang" class="select w-full" required>
                                @foreach (['TK', 'SD', 'SMP'] as $j)
                                    <option value="{{ $j }}" {{ old('jenjang', $guru->jenjang) == $j ? 'selected' : '' }}>
                                        {{ $j }}
                                    </option>
                                @endforeach
                                @if ($guru->jenjang && ! in_array($guru->jenjang, ['TK', 'SD', 'SMP']))
                                    <option value="{{ $guru->jenjang }}" selected>{{ $guru->jenjang }} (data lama)</option>
                                @endif
                            </select>
                        @error('jenjang')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                        </fieldset>
                        <fieldset class="fieldset w-full">
                            <label class="fieldset-legend font-semibold" for="f-status_kepegawaian">Status Kepegawaian</label>
                            <select id="f-status_kepegawaian" name="status_kepegawaian" class="select w-full">
                                @foreach (\App\Models\Guru::STATUS_KEPEGAWAIAN as $kode => $label)
                                    <option value="{{ $kode }}"
                                        {{ old('status_kepegawaian', $guru->status_kepegawaian ?? 'pns') === $kode ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        @error('status_kepegawaian')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                        </fieldset>
                        <fieldset class="fieldset w-full">
                            <label class="fieldset-legend font-semibold" for="f-pendidikan_terakhir">Pendidikan Terakhir</label>
                            <input id="f-pendidikan_terakhir" type="text" name="pendidikan_terakhir"
                                   value="{{ old('pendidikan_terakhir', $guru->pendidikan_terakhir) }}"
                                   class="input w-full" list="daftar-pendidikan" placeholder="S1 - Informatika">
                            <datalist id="daftar-pendidikan">
                                @foreach (['S1 - PGSD', 'S1 - PAUD', 'S1 - Informatika', 'S1 - Bahasa Inggris', 'S1 - Bahasa Indonesia', 'S1 - Matematika', 'S1 - IPA', 'S1 - IPS', 'S1 - Pendidikan Agama Islam', 'S1 - PJOK', 'S2 - Pendidikan', 'D-IV', 'D-III', 'SMA/SMK sederajat'] as $pd)
                                    <option value="{{ $pd }}"></option>
                                @endforeach
                            </datalist>
                            <p class="text-xs opacity-70 mt-1">Format: Jenjang - Bidang (cth: S1 - Bahasa Inggris). Tambahan Penghasilan mensyaratkan paling rendah S-1/D-IV.</p>
                        @error('pendidikan_terakhir')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                        </fieldset>
                        <fieldset class="fieldset w-full">
                            <label class="fieldset-legend font-semibold" for="f-sudah_sertifikasi">Sertifikasi Pendidik</label>
                            <select id="f-sudah_sertifikasi" name="sudah_sertifikasi" class="select w-full">
                                <option value="0" {{ old('sudah_sertifikasi', $guru->sudah_sertifikasi ?? 0) == 0 ? 'selected' : '' }}>Belum sertifikasi</option>
                                <option value="1" {{ old('sudah_sertifikasi', $guru->sudah_sertifikasi ?? 0) == 1 ? 'selected' : '' }}>Sudah sertifikasi</option>
                            </select>
                        @error('sudah_sertifikasi')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                        </fieldset>
                        
                        <fieldset class="fieldset w-full">
                            <label class="fieldset-legend font-semibold" for="f-bidang_studi_serdik">Bidang Studi Sertifikat Pendidik</label>
                            @php
                                $bidangAktif = old('bidang_studi_serdik', $guru->bidang_studi_serdik);
                                $opsiBidang = collect($daftarBidangStudi)
                                    ->map(fn ($b) => ['value' => $b['nama'], 'label' => $b['nama'].' ('.$b['kode'].')']);
                                if ($bidangAktif && ! $opsiBidang->contains('value', $bidangAktif)) {
                                    $opsiBidang = $opsiBidang->push(['value' => $bidangAktif, 'label' => $bidangAktif]);
                                }
                            @endphp
                            <x-searchable-select name="bidang_studi_serdik"
                                :options="$opsiBidang->all()"
                                :selected="$bidangAktif"
                                placeholder="— Pilih Bidang Studi Serdik —" class="w-full" />
                            <p class="text-xs opacity-60 mt-1">
                                Sesuai nama &amp; kode pada sertifikat pendidik (Kepmen 222/O/2025) —
                                dasar penilaian linieritas penempatan. Kode terisi otomatis saat disimpan.
                            </p>
                        @error('bidang_studi_serdik')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                        </fieldset>
                        {{-- Sekolah tempat bertugas (administratif: kabid saja) --}}
                        @if (auth()->user()->hasRole('kabid_ptk'))
                            <fieldset class="fieldset w-full">
                                <label class="fieldset-legend font-semibold" for="f-sekolah_id">Sekolah Tempat Bertugas</label>
                                <x-searchable-select name="sekolah_id"
                                    :options="$daftarSekolah->map(fn ($s) => ['value' => $s->id, 'label' => $s->nama])->all()"
                                    :selected="old('sekolah_id', $guru->sekolah_id)"
                                    placeholder="— Belum terdata di sistem —" class="w-full" />
                            @error('sekolah_id')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                            </fieldset>
                        @else
                            {{-- Kepala sekolah & guru: tidak bisa mengubah relasi sekolah --}}
                            <input type="hidden" name="sekolah_id" value="{{ $guru->sekolah_id }}">
                        @endif
                    </div>
                    </fieldset>
                    @if ($kepegawaianTerkunci)
                        <input type="hidden" name="mapel" value="{{ old('mapel', $guru->mapel) }}">
                        <input type="hidden" name="jenjang" value="{{ old('jenjang', $guru->jenjang) }}">
                    @endif
                    <div class="flex justify-between mt-4">
                        <button type="button" class="btn btn-ghost" @click="tab = 'pribadi'">← Kembali</button>
                        <button type="button" class="btn btn-primary" @click="tab = 'domisili'; $nextTick(() => Object.values(window.petaPickers || {}).forEach(m => m.invalidateSize()))">Lanjut →</button>
                    </div>
                </div>

                {{-- TAB 3: Domisili & Penempatan --}}
                <div x-show="tab === 'domisili'" x-transition role="tabpanel" x-cloak>
                    <p class="text-sm opacity-70 mb-4">Alamat rumah dan titik koordinat domisili — dasar perhitungan jarak ke sekolah. Tentukan lewat peta atau GPS agar akurat.</p>
                    <div class="grid gap-4 sm:grid-cols-2"
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
                         x-init="muatDesa(@js(old('kecamatan', $guru->kecamatan)))"
                         x-on:searchable-select-change="if ($event.detail.name === 'kecamatan') { muatDesa($event.detail.value); petaTerbangKeWilayah('peta-domisili', { kecamatan: petaKodeKecamatan[($event.detail.value || '').toUpperCase().trim()] ?? '' }, 12); }"
                         x-on:desa-terpilih="petaTerbangKeDesa('peta-domisili', $event.detail)">
                        <fieldset class="fieldset w-full">
                            <label class="fieldset-legend font-semibold" for="f-kecamatan">Kecamatan Domisili</label>
                            @php
                                $kecAktif = old('kecamatan', $guru->kecamatan);
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
                            <label class="fieldset-legend font-semibold" for="f-desa_kelurahan">Desa / Kelurahan Domisili</label>
                            <x-desa-select name="desa_kelurahan"
                                   :selected="old('desa_kelurahan', $guru->desa_kelurahan)" />
                        @error('desa_kelurahan')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                        </fieldset>
                        <fieldset class="fieldset w-full sm:col-span-2">
                            <label class="fieldset-legend font-semibold" for="f-domisili_alamat">Alamat Domisili</label>
                            <input id="f-domisili_alamat" type="text" name="domisili_alamat"
                                   value="{{ old('domisili_alamat', $guru->domisili_alamat) }}" required
                                   class="input w-full" placeholder="Jl. Mawar No.10, Kec. Sukamaju">
                        @error('domisili_alamat')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                        </fieldset>
                        <fieldset class="fieldset w-full">
                            <label class="fieldset-legend font-semibold" for="domisili_lat">Latitude</label>
                            <input type="text" id="domisili_lat" name="domisili_lat"
                                   value="{{ old('domisili_lat', $guru->domisili_lat) }}" required
                                   class="input w-full" placeholder="0.7900">
                        @error('domisili_lat')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                        </fieldset>
                        <fieldset class="fieldset w-full">
                            <label class="fieldset-legend font-semibold" for="domisili_lng">Longitude</label>
                            <input type="text" id="domisili_lng" name="domisili_lng"
                                   value="{{ old('domisili_lng', $guru->domisili_lng) }}" required
                                   class="input w-full" placeholder="99.4100">
                        @error('domisili_lng')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                        </fieldset>
                    </div>

                    {{-- Peta pemilih koordinat domisili --}}
                    <div class="mt-3">
                        @include('komponen.peta-picker', [
                            'idPeta' => 'peta-domisili',
                            'inputLat' => 'domisili_lat',
                            'inputLng' => 'domisili_lng',
                            'gps' => true,
                            'lat' => (float) (old('domisili_lat', $guru->domisili_lat) ?: 0.79),
                            'lng' => (float) (old('domisili_lng', $guru->domisili_lng) ?: 99.41),
                        ])
                    </div>
                    <p class="text-xs opacity-60 mt-1 flex items-center gap-1">
                        <x-ikon nama="info" class="h-3.5 w-3.5 shrink-0" /> Tips: buka Google Maps, klik kanan lokasi rumah → salin koordinatnya.
                    </p>

                    <p class="text-xs opacity-60 mt-2 flex items-center gap-1">
                        <x-ikon nama="info" class="h-3.5 w-3.5 shrink-0" /> Sekolah tempat bertugas diatur lewat "Sekolah Tempat Bertugas" di tab Kepegawaian — nama dan jarak mengikuti data sekolah tersebut.
                    </p>

                    <div class="flex justify-between mt-4">
                        <button type="button" class="btn btn-ghost" @click="tab = 'kepegawaian'">← Kembali</button>
                    </div>
                </div>

                <div class="flex gap-2 mt-6">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <a href="{{ route('guru.index') }}" class="btn btn-ghost">Batal</a>
                </div>
            </form>
        </div>
    </div>
@endsection
