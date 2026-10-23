<div>
    <x-kartu class="mb-4">
        <div class="card-body py-3">
            <div class="flex flex-wrap gap-2">
                <select wire:model="status" class="select w-full">
                    <option value="">Semua status</option>
                    <option value="diajukan" @if($status === 'diajukan') selected @endif>Diajukan</option>
                    <option value="ditolak" @if($status === 'ditolak') selected @endif>Ditolak</option>
                    <option value="diverifikasi" @if($status === 'diverifikasi') selected @endif>Diverifikasi</option>
                    <option value="rekomendasi" @if($status === 'rekomendasi') selected @endif>Rekomendasi</option>
                    <option value="menunggu" @if($status === 'menunggu') selected @endif>Menunggu</option>
                    <option value="disetujui" @if($status === 'disetujui') selected @endif>Disetujui</option>
                </select>
                @if ($status !== '')
                    <button wire:click="resetFilter" class="btn btn-ghost ms-2">Reset</button>
                @endif
            </div>
            @if ($status !== '')
                <p class="text-sm opacity-60 mt-2">Menampilkan usulan status: {{ ucfirst($status) }}</p>
            @endif
        </div>
    </x-kartu>

    <x-kartu class="overflow-hidden hidden md:block">
        <div class="card-body p-0 overflow-x-auto min-w-0">
            <table class="table table-zebra">
                <thead>
                    <tr>
                        <th>Guru</th>
                        <th>Sekolah</th>
                        <th>Alasan</th>
                        <th>Status</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse (UsulanPenempatan::where('status', $status ?? null)->latest('created_at')->paginate(20) as $u)
                        <tr>
                            <td>{{ $u->guru->nama }}</td>
                            <td>{{ $u->sekolah->nama }}</td>
                            <td>{{ substr($u->alasan, 0, 50) . (strlen($u->alasan) > 50 ? '...' : '') }}</td>
                            <td>
                                <span class="badge {{ match($u->status, 'diajukan') => 'badge-warning', 'ditolak' => 'badge-error', 'disetujui' => 'badge-success', 'rekomendasi' => 'badge-warning', 'menunggu' => 'badge-warning', 'diverifikasi' => 'badge-info' }}">
                                    {{ ucfirst($u->status) }}
                                </span>
                            </td>
                            <td class="text-right">
                                @if (auth()->user()->hasRole('kabid_ptk'))
                                    <x-hapus :aksi="route('usulan.tolak', $u)" tanya="Tolak usulan pemempatan {{ $u->guru->nama }}?" />
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center py-8 opacity-60">Tidak ada usulan sesuai filter.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-kartu>

    <div class="grid gap-3 md:hidden">
        @forelse (UsulanPenempatan::where('status', $status ?? null)->latest('created_at')->paginate(20) as $u)
            <div class="card bg-base-100 shadow">
                <div class="card-body p-4">
                    <p class="font-bold">{{ $u->guru->nama }} di {{ $u->sekolah->nama }}</p>
                    <p class="text-sm opacity-60">Alasan: {{ substr($u->alasan, 0, 60) . (strlen($u->alasan) > 60 ? '...' : '') }}</p>
                    <span class="badge {{ match($u->status, 'diajukan') => 'badge-warning', 'ditolak' => 'badge-error', 'disetujui' => 'badge-success', 'rekomendasi' => 'badge-warning', 'menunggu' => 'badge-warning', 'diverifikasi' => 'badge-info' }}">
                        {{ ucfirst($u->status) }}
                    </span>
                    @if (auth()->user()->hasRole('kabid_ptk'))
                        <x-hapus :aksi="route('usulan.tolak', $u)" tanya="Tolak usulan pemempatan {{ $u->guru->nama }}?" />
                    @endif
                </div>
            </div>
        @empty
            <x-kosong pesan="Tidak ada usulan sesuai filter." />
        @endforelse
    </div>

    <div class="mt-4 flex flex-wrap items-center justify-between gap-2">
        <div>{{ UsulanPenempatan::where('status', $status ?? null)->count() }} usulan</div>
        <div></div>
    </div>
</div>