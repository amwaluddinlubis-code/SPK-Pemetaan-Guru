{{-- Tabel referensi Kabupaten/Kota (Livewire): pencarian + filter provinsi. --}}
<div>
    <x-kartu class="mb-4">
        <div class="card-body py-3">
            <div class="flex flex-wrap gap-2">
                <input type="text" wire:model.live.debounce.500ms="q"
                       placeholder="Cari nama atau kode kab/kota…"
                       class="input input-bordered flex-1 min-w-52">
                <select wire:model.live="provinsi" class="select select-bordered max-w-xs">
                    <option value="">— Semua Provinsi —</option>
                    @foreach ($daftarProvinsi as $prov)
                        <option value="{{ $prov->kode }}">{{ $prov->nama }}</option>
                    @endforeach
                </select>
                @if ($q !== '' || $provinsi !== '')
                    <button wire:click="resetFilter" class="btn btn-ghost">Reset</button>
                @endif
            </div>
            @if ($q !== '' || $provinsi !== '')
                <p class="text-sm opacity-60 mt-2">Menampilkan {{ $daftar->total() }} hasil.</p>
            @endif
        </div>
    </x-kartu>

    <x-kartu class="overflow-hidden">
        <div class="card-body p-0 overflow-x-auto min-w-0">
            <table class="table table-zebra">
                <thead>
                    <tr>
                        <th><button wire:click="urutkan('kode')" class="link">Kode {{ $sort === 'kode' ? ($arah === 'asc' ? '▲' : '▼') : '' }}</button></th>
                        <th><button wire:click="urutkan('nama')" class="link">Kabupaten/Kota {{ $sort === 'nama' ? ($arah === 'asc' ? '▲' : '▼') : '' }}</button></th>
                        <th>Provinsi</th>
                        <th class="text-right">Kecamatan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($daftar as $k)
                        <tr>
                            <td><span class="badge badge-outline">{{ $k->kode }}</span></td>
                            <td>
                                <a href="{{ route('wilayah.kecamatan', ['provinsi' => $k->provinsi_kode, 'kabupaten' => $k->kode]) }}"
                                   class="link link-primary font-semibold">{{ $k->nama }}</a>
                            </td>
                            <td class="text-sm">{{ $k->provinsi->nama ?? '—' }}</td>
                            <td class="text-right">{{ $k->kecamatan_count }}</td>
                        </tr>
                    @empty
                        <x-kosong pesan="Tidak ada kabupaten/kota yang cocok." kolom="4" />
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-kartu>

    <div class="mt-4 flex flex-wrap items-center justify-between gap-2">
        <x-info-halaman :data="$daftar" />
        {{ $daftar->links() }}
    </div>
</div>
