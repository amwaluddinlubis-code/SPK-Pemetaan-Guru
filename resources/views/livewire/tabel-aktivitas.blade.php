{{-- Tabel Jejak Audit (Livewire): filter + pagination tanpa reload. --}}
<div>
<x-halaman judul="Jejak Audit Operasional" ikon="audit">
    <x-slot:deskripsi>Append-only: catatan tidak bisa diubah/dihapus lewat aplikasi.</x-slot:deskripsi>
</x-halaman>

<div>
    <x-kartu class="mb-4 p-4">
        <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
            <div>
                <label class="label"><span class="label-text">Aksi</span></label>
                <select aria-label="Semua" wire:model.live="aksi" class="select select-sm w-full">
                    <option value="">Semua</option>
                    @foreach ($daftarAksi as $a)
                        <option value="{{ $a }}">{{ $a }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="label"><span class="label-text">Aktor</span></label>
                <select aria-label="Semua" wire:model.live="user_id" class="select select-sm w-full">
                    <option value="">Semua</option>
                    @foreach ($daftarUser as $u)
                        <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->role }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="label"><span class="label-text">Hasil</span></label>
                <select aria-label="Semua" wire:model.live="hasil" class="select select-sm w-full">
                    <option value="">Semua</option>
                    <option value="sukses">Sukses</option>
                    <option value="gagal">Gagal</option>
                </select>
            </div>
            <div>
                <label class="label"><span class="label-text">Dari</span></label>
                <input aria-label="Dari" type="date" wire:model.live="dari" class="input input-sm w-full">
            </div>
            <div>
                <label class="label"><span class="label-text">Sampai</span></label>
                <input aria-label="Sampai" type="date" wire:model.live="sampai" class="input input-sm w-full">
            </div>
        </div>
        @if ($aksi !== '' || $user_id !== '' || $hasil !== '' || $dari !== '' || $sampai !== '')
            <div class="mt-3">
                <button wire:click="resetFilter" class="btn btn-ghost btn-sm">Reset</button>
            </div>
        @endif
    </x-kartu>

    <x-kartu class="overflow-x-auto">
        <table class="table table-zebra table-sm">
            <thead>
                <tr><th>Waktu</th><th>Aktor</th><th>Aksi</th><th>Entitas</th><th>Hasil</th><th>Detail</th></tr>
            </thead>
            <tbody>
                @forelse ($log as $l)
                    <tr>
                        <td class="whitespace-nowrap">{{ $l->waktu->format('d/m/Y H:i') }}</td>
                        <td>{{ $l->user?->name ?? '—' }}</td>
                        <td><span class="badge badge-outline">{{ $l->aksi }}</span></td>
                        <td>{{ $l->entitas_tipe }}{{ $l->entitas_id ? ' #'.$l->entitas_id : '' }}</td>
                        <td>
                            <span class="badge {{ $l->hasil === 'sukses' ? 'badge-success' : 'badge-error' }}">{{ $l->hasil }}</span>
                        </td>
                        <td class="text-xs">
                            @if ($l->perubahan)
                                @foreach ($l->perubahan as $k => $v)
                                    <div><span class="font-semibold">{{ $k }}:</span> {{ is_array($v) ? json_encode($v) : $v }}</div>
                                @endforeach
                            @else
                                <span class="opacity-40">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <x-kosong pesan="Belum ada catatan audit." kolom="6" />
                @endforelse
            </tbody>
        </table>
    </x-kartu>

    <div class="mt-4 flex flex-wrap items-center justify-between gap-2">
        <x-info-halaman :data="$log" />
        <div class="flex flex-wrap items-center gap-3">
            {{ $log->links() }}
            <x-per-halaman />
        </div>
    </div>
</div>

</div>
