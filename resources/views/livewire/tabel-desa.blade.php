{{-- Tabel referensi Desa/Kelurahan (Livewire): wajib pilih kecamatan dulu. --}}
<div>
    <x-kartu class="mb-4">
        <div class="card-body py-3">
            <div class="flex flex-wrap gap-2">
                <input type="text" wire:model.live.debounce.500ms="q"
                       placeholder="Cari nama atau kode desa…"
                       class="input input-bordered flex-1 min-w-52">
                <select wire:model.live="provinsi" class="select select-bordered max-w-xs">
                    <option value="">— Semua Provinsi —</option>
                    @foreach ($daftarProvinsi as $prov)
                        <option value="{{ $prov->kode }}">{{ $prov->nama }}</option>
                    @endforeach
                </select>
                <select wire:model.live="kabupaten" class="select select-bordered max-w-xs"
                        @if ($provinsi === '') disabled @endif>
                    <option value="">— Semua Kab/Kota —</option>
                    @foreach ($daftarKabupaten as $kab)
                        <option value="{{ $kab->kode }}">{{ $kab->nama }}</option>
                    @endforeach
                </select>
                <select wire:model.live="kecamatan" class="select select-bordered max-w-xs"
                        @if ($kabupaten === '') disabled @endif>
                    <option value="">— Pilih Kecamatan —</option>
                    @foreach ($daftarKecamatan as $kec)
                        <option value="{{ $kec->kode }}">{{ $kec->nama }}</option>
                    @endforeach
                </select>
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
