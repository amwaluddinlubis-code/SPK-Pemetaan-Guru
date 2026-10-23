<div>
    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
        <div>
            <h1 class="text-2xl font-bold">Pendaftaran Guru</h1>
            <p class="text-sm opacity-60">Pendaftaran akun mandiri guru — wajib persetujuan operator dinas sebelum akun aktif.</p>
        </div>
        @if ($jumlahMenunggu > 0)
            <span class="badge badge-warning badge-lg">{{ $jumlahMenunggu }} menunggu</span>
        @endif
    </div>

    @if (session('sukses'))
        <div class="alert alert-success text-sm mb-4">{{ session('sukses') }}</div>
    @endif

    <div role="tablist" class="tabs tabs-box mb-4 w-fit">
        <button role="tab" wire:click="$set('tab', 'menunggu')"
                class="tab {{ $tab === 'menunggu' ? 'tab-active' : '' }}">Menunggu Persetujuan</button>
        <button role="tab" wire:click="$set('tab', 'riwayat')"
                class="tab {{ $tab === 'riwayat' ? 'tab-active' : '' }}">Riwayat</button>
    </div>

    <div class="card bg-base-100 shadow">
        <div class="card-body p-0">
            <div class="overflow-x-auto">
                <table class="table w-full">
                    <thead>
                        <tr>
                            <th>Pendaftar</th>
                            <th>NIP</th>
                            <th>Mapel / Jenjang</th>
                            <th>Sekolah</th>
                            <th>No. HP</th>
                            @if ($tab === 'menunggu')
                                <th>Catatan penolakan</th>
                                <th class="text-right">Aksi</th>
                            @else
                                <th>Status</th>
                                <th>Diproses oleh</th>
                                <th>Catatan operator</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($daftar as $p)
                            <tr>
                                <td>
                                    <div class="font-semibold">{{ $p->nama }}</div>
                                    <div class="text-xs opacity-60">{{ $p->created_at?->format('d M Y H:i') }}</div>
                                </td>
                                <td>{{ $p->nip ?: '— (belum ada)' }}</td>
                                <td>{{ $p->mapel ?: '—' }} / {{ $p->jenjang ?: '—' }}<br>
                                    <span class="text-xs opacity-60">{{ $p->status_kepegawaian ? (\App\Models\Guru::STATUS_KEPEGAWAIAN[$p->status_kepegawaian] ?? $p->status_kepegawaian) : '—' }}</span>
                                </td>
                                <td>{{ $p->namaSekolah() ?: '—' }}</td>
                                <td>{{ $p->no_hp ?: '—' }}</td>
                                @if ($tab === 'menunggu')
                                    <td>
                                        <input type="text" wire:model="catatan.{{ $p->id }}"
                                               class="input input-sm w-44" placeholder="Alasan bila ditolak">
                                    </td>
                                    <td class="text-right whitespace-nowrap">
                                        <button wire:click="setujui({{ $p->id }})"
                                                wire:confirm="Setujui pendaftaran {{ $p->nama }}? Data guru & akun (username = NIP) akan dibuat."
                                                class="btn btn-success btn-sm">✓ Setujui</button>
                                        <button wire:click="tolak({{ $p->id }})"
                                                wire:confirm="Tolak pendaftaran {{ $p->nama }}?"
                                                class="btn btn-error btn-sm btn-outline">Tolak</button>
                                    </td>
                                @else
                                    <td>
                                        <span class="badge {{ $p->status === 'disetujui' ? 'badge-success' : 'badge-error' }}">
                                            {{ \App\Models\PendaftaranGuru::STATUS[$p->status] ?? $p->status }}
                                        </span>
                                    </td>
                                    <td>{{ $p->penilai?->name ?: '—' }}</td>
                                    <td class="text-sm">{{ $p->catatan_operator ?: '—' }}</td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center opacity-60 py-8">
                                    {{ $tab === 'menunggu' ? 'Tidak ada pendaftaran yang menunggu.' : 'Belum ada riwayat pemrosesan.' }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4">
                {{ $daftar->links() }}
            </div>
        </div>
    </div>
</div>
