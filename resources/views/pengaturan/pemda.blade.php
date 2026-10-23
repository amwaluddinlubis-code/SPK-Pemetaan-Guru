{{-- Pengaturan Pemda: pilih wilayah kerja resmi aplikasi (konteks ketat). --}}
@extends('layouts.app')
@section('judul', 'Pengaturan Pemda — PetaPenempatan ASN')

@section('konten')
    <h1 class="text-2xl font-bold mb-4">Pengaturan Pemda</h1>

    @if ($konteksAktif)
        <div class="alert alert-success mb-4">
            <div>
                <p class="font-semibold">Konteks aktif: {{ $namaPemda }}</p>
                <p class="text-sm">
                    Cakupan: {{ $jumlahKecamatan }} kecamatan —
                    {{ $sekolahDalam }} dari {{ $sekolahTotal }} sekolah dan
                    {{ $guruDalam }} dari {{ $guruTotal }} guru tampil dalam konteks ini.
                    Data di luar wilayah disembunyikan dari semua modul.
                </p>
            </div>
            <form method="POST" action="{{ route('pengaturan.pemda.destroy') }}"
                  onsubmit="return confirm('Lepas konteks Pemda? Aplikasi akan kembali menampilkan semua wilayah.')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-outline">Lepas Konteks</button>
            </form>
        </div>
    @else
        <div class="alert alert-warning mb-4">
            <p class="text-sm">
                Belum ada konteks Pemda. Aplikasi menampilkan data semua wilayah.
                Pilih wilayah kerja di bawah agar aplikasi berkonteks satu Pemda.
            </p>
        </div>
    @endif

    <div class="card bg-base-100 shadow-xl">
        <div class="card-body">
            <x-galat />

            <form method="POST" action="{{ route('pengaturan.pemda.update') }}"
                  x-data="{
                    level: @js($level ?? 'kabupaten'),
                    opsiKabupaten: [],
                    kodeKab: @js($kodeKabupaten ?? ''),
                    labelKab: '',
                    bukaKab: false,
                    cariKab: '',
                    get kabTersaring() {
                        const q = this.cariKab.trim().toLowerCase();
                        return (q === '' ? this.opsiKabupaten : this.opsiKabupaten.filter(k => k.nama.toLowerCase().includes(q))).slice(0, 100);
                    },
                    async muatKabupaten(kodeProv, jagaPilihan = false) {
                        if (!jagaPilihan) { this.kodeKab = ''; this.labelKab = ''; }
                        this.opsiKabupaten = [];
                        if (!kodeProv) return;
                        try {
                            const r = await fetch(@js(route('wilayah.api.kabupaten')) + '?provinsi=' + encodeURIComponent(kodeProv), { headers: { 'Accept': 'application/json' } });
                            if (r.ok) {
                                this.opsiKabupaten = await r.json();
                                const t = this.opsiKabupaten.find(k => k.kode === this.kodeKab);
                                if (t) this.labelKab = t.nama;
                            }
                        } catch (e) { /* daftar kabupaten gagal dimuat */ }
                    },
                    pilihKab(k) { this.kodeKab = k.kode; this.labelKab = k.nama; this.bukaKab = false; },
                  }"
                  x-init="muatKabupaten(@js($kodeProvinsi ?? ''), true)"
                  x-on:searchable-select-change="if ($event.detail.name === 'kode_provinsi') muatKabupaten($event.detail.value)">
                @csrf
                @method('PUT')

                <div class="grid gap-4 sm:grid-cols-2">
                    <fieldset class="fieldset w-full">
                        <legend class="fieldset-legend font-semibold">Level Pemda</legend>
                        <label class="label cursor-pointer justify-start gap-2">
                            <input type="radio" name="level" value="provinsi" class="radio"
                                   x-model="level" @checked($level === 'provinsi')>
                            <span class="label-text">Provinsi</span>
                        </label>
                        <label class="label cursor-pointer justify-start gap-2">
                            <input type="radio" name="level" value="kabupaten" class="radio"
                                   x-model="level" @checked($level === 'kabupaten' || empty($level))>
                            <span class="label-text">Kabupaten / Kota</span>
                        </label>
                        <p class="text-xs opacity-60 mt-1">
                            Level provinsi cocok untuk dinas provinsi (wilayah kerja SMA/SMK);
                            level kabupaten/kota untuk dinas kabupaten/kota (SD/SMP).
                        </p>
                    </fieldset>

                    <fieldset class="fieldset w-full">
                        <label class="fieldset-legend font-semibold" for="f-kode_provinsi">Provinsi</label>
                        <x-searchable-select name="kode_provinsi"
                            :options="$daftarProvinsi->map(fn ($p) => ['value' => $p->kode, 'label' => $p->nama])->all()"
                            :selected="$kodeProvinsi"
                            placeholder="— Pilih Provinsi —" class="w-full" />
                    </fieldset>

                    <fieldset class="fieldset w-full" x-show="level === 'kabupaten'">
                        <label class="fieldset-legend font-semibold" for="f-kode_kabupaten">Kabupaten / Kota</label>
                        <input type="hidden" name="kode_kabupaten" x-bind:value="kodeKab">
                        <div class="relative" x-on:click.outside="bukaKab = false">
                            <input type="text" id="f-kode_kabupaten" aria-label="Kabupaten/Kota" class="input w-full pr-8" role="combobox"
                                   x-bind:value="bukaKab ? cariKab : labelKab"
                                   x-on:input="cariKab = $event.target.value"
                                   x-on:focus="bukaKab = true; cariKab = ''"
                                   placeholder="— Pilih Kabupaten/Kota —" autocomplete="off">
                            <span class="pointer-events-none absolute inset-y-0 right-2 flex items-center opacity-50 text-xs">▾</span>
                            <ul x-show="bukaKab" style="display: none;"
                                class="absolute z-40 mt-1 w-full max-h-64 overflow-auto rounded-box border border-base-300 bg-base-100 py-1 shadow-lg">
                                <template x-for="k in kabTersaring" :key="k.kode">
                                    <li>
                                        <button type="button" class="block w-full px-3 py-2 text-left hover:bg-base-200"
                                                x-on:click="pilihKab(k)" x-text="k.nama"></button>
                                    </li>
                                </template>
                                <li x-show="kabTersaring.length === 0" class="px-3 py-2 opacity-60">
                                    Pilih provinsi dulu / tidak ada yang cocok.
                                </li>
                            </ul>
                        </div>
                    </fieldset>
                </div>

                <div class="mt-6 flex items-center gap-3">
                    <button type="submit" class="btn btn-primary">Simpan Konteks Pemda</button>
                    <p class="text-xs opacity-60">
                        Mengubah konteks langsung menyembunyikan data di luar wilayah baru
                        dari daftar sekolah, guru, kebutuhan, usulan, dashboard, dan laporan.
                        Data tidak dihapus dan kembali tampil bila konteks dilepas.
                    </p>
                </div>
            </form>
        </div>
    </div>
@endsection
