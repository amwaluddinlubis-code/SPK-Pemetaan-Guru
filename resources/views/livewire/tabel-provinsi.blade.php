{{-- Tabel referensi Provinsi (Livewire): pencarian, sortir, pagination tanpa reload. --}}
<div>
<x-halaman judul="Referensi Provinsi" ikon="peta">
    <x-slot:deskripsi>37 provinsi se-Indonesia (Kepmendagri 100.1.1-6117/2022). Klik nama untuk melihat kabupaten/kota.</x-slot:deskripsi>
</x-halaman>

<div>
    <x-kartu class="mb-4">
        <div class="card-body py-3">
            <div class="flex flex-wrap gap-2">
                <input aria-label="Cari nama atau kode provinsi…" type="text" wire:model.live.debounce.500ms="q"
                       placeholder="Cari nama atau kode provinsi…"
                       class="input input-bordered flex-1 min-w-52">
                @if ($q !== '')
                    <button wire:click="resetFilter" class="btn btn-ghost">Reset</button>
                @endif
            </div>
            @if ($q !== '')
                <p class="text-sm opacity-60 mt-2">
                    Menampilkan {{ $daftar->total() }} hasil untuk “{{ $q }}”
                </p>
            @endif
        </div>
    </x-kartu>

    <x-kartu class="overflow-hidden">
        <div class="card-body p-0 overflow-x-auto min-w-0">
            <table class="table table-zebra">
                <thead>
                    <tr>
                        <th><button wire:click="urutkan('kode')" class="link py-1">Kode {{ $sort === 'kode' ? ($arah === 'asc' ? '▲' : '▼') : '' }}</button></th>
                        <th><button wire:click="urutkan('nama')" class="link py-1">Provinsi {{ $sort === 'nama' ? ($arah === 'asc' ? '▲' : '▼') : '' }}</button></th>
                        <th class="text-right">Kab/Kota</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($daftar as $p)
                        <tr>
                            <td><span class="badge badge-outline">{{ $p->kode }}</span></td>
                            <td>
                                <a href="{{ route('wilayah.kabupaten', ['provinsi' => $p->kode]) }}"
                                   class="link link-primary font-semibold">{{ $p->nama }}</a>
                            </td>
                            <td class="text-right">{{ $p->kabupaten_count }}</td>
                        </tr>
                    @empty
                        <x-kosong pesan="Tidak ada provinsi yang cocok." kolom="3" />
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-kartu>

    <div class="mt-4 flex flex-wrap items-center justify-between gap-2">
        <x-info-halaman :data="$daftar" />
        <div class="flex flex-wrap items-center gap-3">
            {{ $daftar->links() }}
            <x-per-halaman />
        </div>
    </div>
</div>

</div>
