<div>
    <x-kartu class="mb-4">
        <div class="card-body py-3">
            <div class="flex flex-wrap gap-2">
                <select wire:model="status" class="select w-full">
                    <option value="">Semua status</option>
                    <option value="rekomendasi" @if($status === 'rekomendasi') selected @endif>Rekomendasi</option>
                    <option value="disetujui" @if($status === 'disetujui') selected @endif>Disetujui</option>
                    <option value="ditolak" @if($status === 'ditolak') selected @endif>Ditolak</option>
                </select>
                @if ($status !== '')
                    <button wire:click="resetFilter" class="btn btn-ghost ms-2">Reset</button>
                @endif
            </div>
            @if ($status !== '')
                <p class="text-sm opacity-60 mt-2">Menampilkan hasil SPK status: {{ ucfirst($status) }}</p>
            @endif
        </div>
    </x-kartu>

    <x-kartu class="overflow-hidden hidden md:block">
        <div class="card-body p-0 overflow-x-auto min-w-0">
            <table class="table table-zebra">
                <thead>
                    <tr>
                        <th>Guru</th>
                        <th>Sekolah Asal</th>
                        <th>Sekolah Tujuan</th>
                        <th>Status</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse (HasilSpk::where('status', $status ?? null)->latest('created_at')->paginate(20) as $s)
                        <tr>
                            <td>{{ $s->guru->nama }}</td>
                            <td>{{ $s->sekolahAsal->nama }}</td>
                            <td>{{ $s->sekolahTujuan->nama }}</td>
                            <td>
                                <span class="badge {{ match($s->status, 'rekomendasi') => 'badge-warning', 'disetujui' => 'badge-success', 'ditolak' => 'badge-error' }}">
                                    {{ ucfirst($s->status) }}
                                </span>
                            </td>
                            <td class="text-right">
                                @if (auth()->user()->hasRole('kabid_ptk'))
                                    <x-hapus :aksi="route('spk.tolak', $s)" tanya="Tolak rekomendasi SPK {{ $s->guru->nama }}?" />
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center py-8 opacity-60">Tidak ada hasil SPK sesuai filter.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-kartu>

    <div class="grid gap-3 md:hidden">
        @forelse (HasilSpk::where('status', $status ?? null)->latest('created_at')->paginate(20) as $s)
            <div class="card bg-base-100 shadow">
                <div class="card-body p-4">
                    <p class="font-bold">{{ $s->guru->nama }} - {{ $s->sekolahAsal->nama }} → {{ $s->sekolahTujuan->nama }}</p>
                    <span class="badge {{ match($s->status, 'rekomendasi') => 'badge-warning', 'disetujui' => 'badge-success', 'ditolak' => 'badge-error' }}">
                        {{ ucfirst($s->status) }}
                    </span>
                    @if (auth()->user()->hasRole('kabid_ptk'))
                        <x-hapus :aksi="route('spk.tolak', $s)" tanya="Tolak rekomendasi SPK {{ $s->guru->nama }}?" />
                    @endif
                </div>
            </div>
        @empty
            <x-kosong pesan="Tidak ada hasil SPK sesuai filter." />
        @endforelse
    </div>

    <div class="mt-4 flex flex-wrap items-center justify-between gap-2">
        <div>{{ HasilSpk::where('status', $status ?? null)->count() }} hasil SPK</div>
        <div></div>
    </div>
</div>