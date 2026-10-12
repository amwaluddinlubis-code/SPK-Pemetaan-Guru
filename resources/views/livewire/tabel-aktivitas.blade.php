{{-- Tabel Jejak Audit (Livewire): filter + pagination tanpa reload. --}}
<div>
    <div class="card bg-base-100 shadow p-4 mb-4">
        <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
            <div>
                <label class="label"><span class="label-text">Aksi</span></label>
                <select wire:model.live="aksi" class="select select-sm w-full">
                    <option value="">Semua</option>
                    @foreach ($daftarAksi as $a)
                        <option value="{{ $a }}">{{ $a }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="label"><span class="label-text">Aktor</span></label>
                <select wire:model.live="user_id" class="select select-sm w-full">
                    <option value="">Semua</option>
                    @foreach ($daftarUser as $u)
                        <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->role }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="label"><span class="label-text">Hasil</span></label>
                <select wire:model.live="hasil" class="select select-sm w-full">
                    <option value="">Semua</option>
                    <option value="sukses">Sukses</option>
                    <option value="gagal">Gagal</option>
                </select>
            </div>
            <div>
                <label class="label"><span class="label-text">Dari</span></label>
                <input type="date" wire:model.live="dari" class="input input-sm w-full">
            </div>
            <div>
                <label class="label"><span class="label-text">Sampai</span></label>
                <input type="date" wire:model.live="sampai" class="input input-sm w-full">
            </div>
        </div>
        @if ($aksi !== '' || $user_id !== '' || $hasil !== '' || $dari !== '' || $sampai !== '')
            <div class="mt-3">
                <button wire:click="resetFilter" class="btn btn-ghost btn-sm">Reset</button>
            </div>
        @endif
    </div>

    <div class="card bg-base-100 shadow overflow-x-auto">
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
                    <tr><td colspan="6" class="text-center opacity-60 py-6">Belum ada catatan audit.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $log->links() }}</div>
</div>
