{{-- Tabel Usulan Penempatan (Livewire): pencarian, filter, sorting, pagination tanpa reload. --}}
<div>
<x-halaman judul="Usulan Penempatan" ikon="usulan">
    <x-slot:aksi>
        @if (auth()->user()->hasRole('kabid_ptk', 'kadis'))
            <a href="{{ route('usulan.export', request()->only(['q', 'status'])) }}" class="btn btn-accent">📊 Export Excel</a>
            <a href="{{ route('usulan.cetak', request()->only(['q', 'status'])) }}" target="_blank" class="btn btn-neutral">🖨️ Cetak/PDF</a>
        @endif
        @if (auth()->user()->hasRole('guru', 'kepala_sekolah', 'kabid_ptk'))
            <a href="{{ route('usulan.create') }}" class="btn btn-primary">+ Ajukan Usulan</a>
        @endif
    </x-slot:aksi>
</x-halaman>

<div>
    <x-kartu class="mb-4">
        <div class="card-body py-3">
            <div class="flex flex-wrap gap-2">
                <input aria-label="Cari nama/NIP guru…" type="text" wire:model.live.debounce.500ms="q"
                       placeholder="Cari nama/NIP guru…"
                       class="input flex-1 min-w-52">
                <select aria-label="Status" wire:model.live="status" class="select max-w-xs">
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
    </x-kartu>

    <x-kartu class="overflow-hidden">
        <div class="card-body p-0 overflow-x-auto min-w-0">
            <table class="table table-zebra">
                <thead>
                    <tr>
                        <th>Guru</th>
                        <th>Sekolah Tujuan</th>
                        <th><button wire:click="urutkan('status')" class="link py-1">Status {{ $sort === 'status' ? ($arah === 'asc' ? '▲' : '▼') : '' }}</button></th>
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
                                @if ($u->guru->sudah_sertifikasi)
                                    <div class="mt-1"><x-lencana-linieritas :guru="$u->guru" :jenjang="$u->sekolah->jenjang" :mapel="$u->guru->mapel" /></div>
                                @endif
                            </td>
                            <td class="text-sm">{{ $u->sekolah->nama }}</td>
                            <td><span class="badge {{ $u->badgeStatus() }}">{{ $u->labelStatus() }}</span></td>
                            <td class="text-sm">{{ $u->pengaju->name ?? '—' }}</td>
                            <td class="text-right">
                                <a href="{{ route('usulan.show', $u) }}" class="btn btn-sm btn-info">Lihat</a>
                            </td>
                        </tr>
                    @empty
                        <x-kosong pesan="Tidak ada usulan yang cocok." kolom="5" />
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-kartu>

    <div class="mt-4 flex flex-wrap items-center justify-between gap-2">
        <x-info-halaman :data="$daftarUsulan" />
        <div class="flex flex-wrap items-center gap-3">
            {{ $daftarUsulan->links() }}
            <x-per-halaman />
        </div>
    </div>
</div>

</div>
