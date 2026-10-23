{{-- Tabel referensi Desa/Kelurahan (Livewire): wajib pilih kecamatan dulu. --}}
<div>
    <x-kartu class="mb-4">
        <div class="card-body py-3">
            <div class="flex flex-wrap gap-2">
                <input type="text" wire:model.live.debounce.500ms="q"
                       placeholder="Cari nama atau kode desa…"
                       class="input input-bordered flex-1 min-w-52">
                <x-searchable-select wire-model="provinsi" :selected="$provinsi"
                    :options="$daftarProvinsi->map(fn ($p) => ['value' => $p->kode, 'label' => $p->nama])->all()"
                    placeholder="— Semua Provinsi —" class="w-full max-w-xs" />
                <x-searchable-select wire-model="kabupaten" :selected="$kabupaten"
                    :options="$daftarKabupaten->map(fn ($k) => ['value' => $k->kode, 'label' => $k->nama])->all()"
                    placeholder="— Semua Kab/Kota —" class="w-full max-w-xs"
                    :disabled="$provinsi === ''" />
                <x-searchable-select wire-model="kecamatan" :selected="$kecamatan"
                    :options="$daftarKecamatan->map(fn ($k) => ['value' => $k->kode, 'label' => $k->nama])->all()"
                    placeholder="— Pilih Kecamatan —" class="w-full max-w-xs"
                    :disabled="$kabupaten === ''" />
                @if ($q !== '' || $provinsi !== '' || $kabupaten !== '' || $kecamatan !== '')
                    <button wire:click="resetFilter" class="btn btn-ghost">Reset</button>
                @endif
            </div>
            @if ($kecamatan !== '')
                <p class="text-sm opacity-60 mt-2">
                    Menampilkan {{ $total }} desa/kelurahan
                    @if ($q !== '') untuk “{{ $q }}” @endif
                </p>
            @else
                <p class="text-sm opacity-60 mt-2">
                    Pilih provinsi → kabupaten → kecamatan dulu untuk melihat daftar desa.
                </p>
            @endif
        </div>
    </x-kartu>

    @if ($kecamatan !== '')
        <x-kartu class="overflow-hidden">
            <div class="card-body p-0 overflow-x-auto min-w-0">
                <table class="table table-zebra">
                    <thead>
                        <tr>
                            <th><button wire:click="urutkan('kode')" class="link">Kode {{ $sort === 'kode' ? ($arah === 'asc' ? '▲' : '▼') : '' }}</button></th>
                            <th><button wire:click="urutkan('nama')" class="link">Desa/Kelurahan {{ $sort === 'nama' ? ($arah === 'asc' ? '▲' : '▼') : '' }}</button></th>
                            <th>Kecamatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($daftar as $d)
                            <tr>
                                <td><span class="badge badge-outline">{{ $d->kode }}</span></td>
                                <td class="font-semibold">{{ $d->nama }}</td>
                                <td class="text-sm">{{ $d->kecamatan->nama ?? '—' }}</td>
                            </tr>
                        @empty
                            <x-kosong pesan="Tidak ada desa/kelurahan yang cocok." kolom="3" />
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-kartu>

        <div class="mt-4 flex flex-wrap items-center justify-between gap-2">
            <x-info-halaman :data="$daftar" />
            {{ $daftar->links() }}
        </div>
    @endif
</div>
