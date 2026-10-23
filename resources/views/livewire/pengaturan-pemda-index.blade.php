{{-- Pengaturan Pemda (komponen Livewire penuh): pilih wilayah kerja resmi aplikasi (konteks ketat). --}}
<div>
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
            <button type="button" class="btn btn-sm btn-outline"
                    wire:click="lepas"
                    wire:confirm="Lepas konteks Pemda? Aplikasi akan kembali menampilkan semua wilayah.">Lepas Konteks</button>
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

            <form wire:submit="simpan">
                <div class="grid gap-4 sm:grid-cols-2">
                    <fieldset class="fieldset w-full">
                        <legend class="fieldset-legend font-semibold">Level Pemda</legend>
                        <label class="label cursor-pointer justify-start gap-2">
                            <input type="radio" wire:model.live="level" value="provinsi" class="radio">
                            <span class="label-text">Provinsi</span>
                        </label>
                        <label class="label cursor-pointer justify-start gap-2">
                            <input type="radio" wire:model.live="level" value="kabupaten" class="radio">
                            <span class="label-text">Kabupaten / Kota</span>
                        </label>
                        <p class="text-xs opacity-60 mt-1">
                            Level provinsi cocok untuk dinas provinsi (wilayah kerja SMA/SMK);
                            level kabupaten/kota untuk dinas kabupaten/kota (SD/SMP).
                        </p>
                    </fieldset>

                    <fieldset class="fieldset w-full">
                        <label class="fieldset-legend font-semibold" for="f-kodeProvinsi">Provinsi</label>
                        <x-searchable-select wire-model="kodeProvinsi"
                            :options="$daftarProvinsi->map(fn ($p) => ['value' => $p->kode, 'label' => $p->nama])->all()"
                            :selected="$kodeProvinsi"
                            placeholder="— Pilih Provinsi —" class="w-full" />
                        @error('kodeProvinsi')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    </fieldset>

                    @if ($level === 'kabupaten')
                        <fieldset class="fieldset w-full">
                            <label class="fieldset-legend font-semibold" for="f-kodeKabupaten">Kabupaten / Kota</label>
                            <x-searchable-select wire-model="kodeKabupaten"
                                :options="$opsiKabupaten"
                                :selected="$kodeKabupaten"
                                placeholder="— Pilih Kabupaten/Kota —" class="w-full" />
                            @error('kodeKabupaten')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                        </fieldset>
                    @endif
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
</div>
