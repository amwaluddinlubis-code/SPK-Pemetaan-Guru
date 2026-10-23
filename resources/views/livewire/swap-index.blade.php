<div>
    <x-kartu class="mb-4">
        <div class="card-body py-3">
            <div class="flex flex-wrap gap-2">
                <select wire:model="status" class="select w-full">
                    <option value="">Semua status</option>
                    <option value="menunggu" @if($status === 'menunggu') selected @endif>Menunggu</option>
                    <option value="ditolak" @if($status === 'ditolak') selected @endif>Ditolak</option>
                </select>
                @if ($status !== '')
                    <button wire:click="resetFilter" class="btn btn-ghost ms-2">Reset</button>
                @endif
            </div>
            @if ($status !== '')
                <p class="text-sm opacity-60 mt-2">Menampilkan swap status: {{ ucfirst($status) }}</p>
            @endif
        </div>
    </x-kartu>

    <x-kartu class="overflow-hidden hidden md:block">
        <div class="card-body p-0 overflow-x-auto min-w-0">
            <table class="table table-zebra">
                <thead>
                    <tr>
                        <th>Guru A</th>
                        <th>Guru B</th>
                        <th>Sekolah Tujuan A</th>
                        <th>Sekolah Tujuan B</th>
                        <th>Status</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse (RekomendasiSwap::where('status', $status ?? null)->latest('created_at')->paginate(20) as $s)
                        <tr>
                            <td>{{ $s->guru_a->nama }}</td>
                            <td>{{ $s->guru_b->nama }}</td>
                            <td>{{ $s->sekolah_tujuan_a->nama }}</td>
                            <td>{{ $s->sekolah_tujuan_b->nama }}</td>
                            <td>
                                <span class="badge {{ match($s->status, 'menunggu') => 'badge-warning', 'ditolak' => 'badge-error' }}">
                                    {{ ucfirst($s->status) }}
                                </span>
                            </td>
                            <td class="text-right">
                                @if (auth()->user()->hasRole('kabid_ptk'))
                                    <x-hapus :aksi="route('swap.tolak', $s)" tanya="Tolak swap antara {{ $s->guru_a->nama }} dan {{ $s->guru_b->nama }}?" />
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center py-8 opacity-60">Tidak ada swap sesuai filter.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-kartu>

    <div class="grid gap-3 md:hidden">
        @forelse (RekomendasiSwap::where('status', $status ?? null)->latest('created_at')->paginate(20) as $s)
            <div class="card bg-base-100 shadow">
                <div class="card-body p-4">
                    <p class="font-bold">Swap: {{ $s->guru_a->nama }} & {{ $s->guru_b->nama }}</p>
                    <p class="text-sm opacity-60">Tujuan: {{ $s->sekolah_tujuan_a->nama }} & {{ $s->sekolah_tujuan_b->nama }}</p>
                    <span class="badge {{ match($s->status, 'menunggu') => 'badge-warning', 'ditolak' => 'badge-error' }}">
                        {{ ucfirst($s->status) }}
                    </span>
                    @if (auth()->user()->hasRole('kabid_ptk'))
                        <x-hapus :aksi="route('swap.tolak', $s)" tanya="Tolak swap antara {{ $s->guru_a->nama }} dan {{ $s->guru_b->nama }}?" />
                    @endif
                </div>
            </div>
        @empty
            <x-kosong pesan="Tidak ada swap sesuai filter." />
        @endforelse
    </div>

    <div class="mt-4 flex flex-wrap items-center justify-between gap-2">
        <div>{{ RekomendasiSwap::where('status', $status ?? null)->count() }} rekomendasi swap</div>
        <div></div>
    </div>
</div>