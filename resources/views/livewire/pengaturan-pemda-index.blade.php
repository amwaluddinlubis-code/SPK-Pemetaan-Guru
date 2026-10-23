{{-- Pengaturan Pemda (komponen Livewire penuh): pilih wilayah kerja resmi aplikasi (konteks ketat). --}}
<div>
    <x-halaman judul="Pengaturan Pemda" ikon="peta" />

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
            <button type="button" class="btn btn-sm btn-outline" data-pesan="Lepas konteks Pemda? Aplikasi akan kembali menampilkan semua wilayah."
                    x-on:click="konfirmasiDariData($el, {judul: 'Lepas Konteks Pemda', tombol: 'Lepas Konteks', warna: 'btn-warning'}).then(function (v) { if (v) $wire.lepas(); })">Lepas Konteks</button>
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

    {{-- ===== Pengaturan SPK: rekomendasi lintas jenjang ===== --}}
    <div class="card bg-base-100 shadow mt-6">
        <div class="card-body">
            <h2 class="card-title text-lg">Pengaturan SPK — Rekomendasi Lintas Jenjang</h2>
            <p class="text-sm opacity-70">
                Lintas jenjang berarti guru direkomendasikan ke sekolah jenjang lain
                (mis. guru SMP ke sekolah SD). Dinas dapat memilih perilakunya:
            </p>
            <form wire:submit="simpanLintasJenjang">
                <div class="space-y-3 mt-2">
                    <label class="flex items-start gap-3 cursor-pointer">
                        <input type="radio" wire:model="lintasJenjang" value="larang" class="radio radio-primary mt-1">
                        <span>
                            <strong>Larang lintas jenjang (Opsi A)</strong>
                            <span class="block text-sm opacity-70">Mesin SPK hanya merekomendasikan guru ke sekolah sejenjangnya. Penempatan lintas jenjang menjadi keputusan khusus dinas di luar rekomendasi otomatis.</span>
                        </span>
                    </label>
                    <label class="flex items-start gap-3 cursor-pointer">
                        <input type="radio" wire:model="lintasJenjang" value="boleh" class="radio radio-primary mt-1">
                        <span>
                            <strong>Izinkan lintas jenjang (Opsi B)</strong> — bawaan.
                            <span class="block text-sm opacity-70">Rekomendasi lintas jenjang boleh dibuat; khusus rekomendasi tingkat 2 (linier kelompok), lencana linieritas dinilai dari jenjang guru — konsisten dengan cara mesin menurunkan mapel liniernya.</span>
                        </span>
                    </label>
                </div>
                @error('lintasJenjang')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Simpan Pengaturan SPK</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ===== Branding aplikasi: warna aksen & logo pemda (Redesign R0) ===== --}}
    <div class="card bg-base-100 shadow mt-6">
        <div class="card-body">
            <h2 class="card-title text-lg">Branding — Warna Aksen & Logo Pemda</h2>
            <p class="text-sm opacity-70">
                Warna aksen menjadi warna utama seluruh aplikasi (bilah atas, tombol,
                menu aktif, fokus) dan logo tampil di bilah sisi, bilah atas, serta
                halaman masuk. Perubahan berlaku seketika tanpa mengubah kode.
            </p>
            <form wire:submit="simpanBranding" class="mt-2 space-y-4">
                <div class="flex flex-wrap items-end gap-4">
                    <div class="form-control">
                        <label class="label" for="warna-akksen"><span class="label-text font-semibold">Warna aksen</span></label>
                        <div class="flex items-center gap-2">
                            <input id="warna-akksen" type="color" wire:model="warnaAksen" class="h-11 w-16 cursor-pointer rounded-lg border border-base-300 bg-base-100 p-1">
                            <input type="text" wire:model="warnaAksen" class="input input-bordered w-32 font-mono" placeholder="#2563eb" maxlength="7">
                        </div>
                        @error('warnaAksen')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="flex items-center gap-2 pb-1">
                        <span class="btn btn-primary btn-sm pointer-events-none">Contoh Tombol</span>
                        <span class="badge badge-secondary">Contoh Lencana</span>
                    </div>
                </div>
                <div class="form-control">
                    <label class="label" for="logo-pemda"><span class="label-text font-semibold">Logo pemda (PNG/JPG/SVG, maks 1 MB)</span></label>
                    <div class="flex flex-wrap items-center gap-4">
                        @if (\App\Services\Branding::logoUrl())
                            <span class="inline-flex items-center justify-center rounded-box bg-primary p-1.5">
                                <img src="{{ \App\Services\Branding::logoUrl() }}" alt="Logo pemda saat ini" class="h-12 w-12 rounded-lg bg-white object-contain p-0.5">
                            </span>
                            <button type="button" data-pesan="Hapus logo pemda? Aplikasi kembali memakai ikon bawaan." x-on:click="konfirmasiDariData($el, {judul: 'Hapus Logo Pemda', tombol: 'Hapus Logo', warna: 'btn-error'}).then(function (v) { if (v) $wire.hapusLogo(); })" class="btn btn-outline btn-error btn-sm">Hapus Logo</button>
                        @endif
                        <input id="logo-pemda" type="file" wire:model="logoBaru" accept=".png,.jpg,.jpeg,.svg" class="file-input file-input-bordered w-full max-w-sm">
                    </div>
                    @error('logoBaru')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                    <p class="text-xs opacity-60 mt-1" wire:loading wire:target="logoBaru">Mengunggah logo…</p>
                </div>
                <div>
                    <button type="submit" class="btn btn-primary">Simpan Branding</button>
                </div>
            </form>
        </div>
    </div>
</div>
