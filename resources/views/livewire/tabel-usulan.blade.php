{{-- Tabel Usulan Penempatan (Livewire): pencarian, filter, sorting, pagination tanpa reload. --}}
<div>
    <div class="card bg-base-100 shadow-xl mb-4">
        <div class="card-body py-3">
            <div class="flex flex-wrap gap-2">
                <input type="text" wire:model.live.debounce.500ms="q"
                       placeholder="Cari nama/NIP guru…"
                       class="input flex-1 min-w-52">
                <select wire:model.live="status" class="select max-w-xs">
                    <option value="">— Semua Status —</option>
                    @foreach (\App\Models\UsulanPenempatan::STATUS as $nilai => $label)
                        <option value="{{ $nilai }}">{{ $label }}</option>
                    @endforeach
                </select>
                @if ($q !== '' || $status !== '')
                    <button wire:click="resetFilter" class="btn btn-ghost">Reset</button>
                @endif
            </div>
            @if ($q !== '' || $status !== '')
                <p class="text-sm opacity-60 mt-2">Menampilkan {{ $daftarUsulan->total() }} hasil.</p>
            @endif
        </div>
    </div>

    <div class="card bg-base-100 shadow-xl overflow-hidden">
        <div class="card-body p-0 overflow-x-auto min-w-0">
            <table class="table table-zebra">
                <thead>
                    <tr>
                        <th>Guru</th>
                        <th>Sekolah Tujuan</th>
                        <th><button wire:click="urutkan('status')" class="link">Status {{ $sort === 'status' ? ($arah === 'asc' ? '▲' : '▼') : '' }}</button></th>
                        <th>Diajukan Oleh</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($daftarUsulan as $u)
                        <tr>
                            <td>
                                <strong>{{ $u->guru->nama }}</strong><br>
                                <span class="text-sm opacity-60">{{ $u->guru->nipTersamar() }} • {{ $u->guru->mapel }}</span>
                            </td>
                            <td class="text-sm">{{ $u->sekolah->nama }}</td>
                            <td><span class="badge {{ $u->badgeStatus() }}">{{ $u->labelStatus() }}</span></td>
                            <td class="text-sm">{{ $u->pengaju->name ?? '—' }}</td>
                            <td class="text-right">
                                <a href="{{ route('usulan.show', $u) }}" class="btn btn-sm btn-info">Lihat</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center opacity-60 py-8">Tidak ada usulan yang cocok.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4 flex flex-wrap items-center justify-between gap-2">
        <p class="text-sm opacity-70">
            Menampilkan {{ $daftarUsulan->firstItem() ?? 0 }}–{{ $daftarUsulan->lastItem() ?? 0 }}
            dari {{ $daftarUsulan->total() }} data
        </p>
        {{ $daftarUsulan->links() }}
    </div>
</div>
