{{-- Tabel Mutasi Silang (Livewire): filter status + pagination tanpa reload. --}}
<div>
<x-halaman judul="Mutasi Silang (Swap)" ikon="swap">
    <x-slot:deskripsi>
        Rekomendasi pertukaran dua guru bermapel sama: keduanya menjadi
        lebih dekat ke domisili setelah bertukar sekolah.
    </x-slot:deskripsi>
    <x-slot:aksi>
        {{-- Tombol generate: khusus administrator (kabid_ptk) --}}
        @if (auth()->user()->hasRole('kabid_ptk'))
            <form method="POST" action="{{ route('swap.generate') }}"
                  onsubmit="return confirm('Jalankan pencocokan pasangan swap untuk semua guru?')">
                @csrf
                <button type="submit" class="btn btn-primary"><x-ikon nama="swap" class="h-5 w-5" /> Generate Rekomendasi</button>
            </form>
        @endif
    </x-slot:aksi>
</x-halaman>

<div>
    {{-- Pencarian teks --}}
    <div class="flex flex-wrap gap-2 mb-4">
        <input aria-label="Cari nama guru A atau guru B…" type="text" wire:model.live.debounce.500ms="q"
               placeholder="Cari nama guru A atau guru B…"
               class="input flex-1 min-w-52">
        @if ($q !== '' || $status !== '')
            <button wire:click="resetFilter" class="btn btn-ghost">Reset</button>
        @endif
    </div>

    {{-- Filter status --}}
    <div class="flex flex-wrap gap-2 mb-4">
        <button wire:click="$set('status', '')"
           class="badge {{ $status === '' ? 'badge-primary' : 'badge-ghost' }} badge-lg">Semua</button>
        @foreach (\App\Models\RekomendasiSwap::STATUS as $kode => $label)
            <button wire:click="$set('status', '{{ $kode }}')"
               class="badge {{ $status === $kode ? 'badge-primary' : 'badge-ghost' }} badge-lg">
                {{ $label }}
            </button>
        @endforeach
    </div>

    <x-kartu class="overflow-x-auto">
        <table class="table table-zebra tabel-baku">
            <thead>
                <tr>
                    <th>Guru A</th>
                    <th>Guru B</th>
                    <th>Penghematan Jarak</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($swaps as $s)
                    <tr>
                        {{-- Guru A: nama, mapel, asal → tujuan, jarak sebelum → sesudah --}}
                        <td>
                            <div class="font-semibold">{{ $s->guruA->nama }}</div>
                            <div class="text-xs opacity-70">{{ $s->guruA->mapel }}</div>
                            <div class="text-xs mt-1">
                                {{ $s->guruA->sekolah->nama ?? '-' }}
                                →
                                <span class="font-semibold text-primary">{{ $s->sekolahTujuanA->nama }}</span>
                            </div>
                            <div class="text-xs opacity-70">
                                {{ number_format($s->jarak_a_sekarang_km, 1, ',', '.') }} km
                                →
                                {{ number_format($s->jarak_a_usulan_km, 1, ',', '.') }} km
                                <span class="opacity-60">(garis lurus)</span>
                            </div>
                        </td>

                        {{-- Guru B --}}
                        <td>
                            <div class="font-semibold">{{ $s->guruB->nama }}</div>
                            <div class="text-xs opacity-70">{{ $s->guruB->mapel }}</div>
                            <div class="text-xs mt-1">
                                {{ $s->guruB->sekolah->nama ?? '-' }}
                                →
                                <span class="font-semibold text-primary">{{ $s->sekolahTujuanB->nama }}</span>
                            </div>
                            <div class="text-xs opacity-70">
                                {{ number_format($s->jarak_b_sekarang_km, 1, ',', '.') }} km
                                →
                                {{ number_format($s->jarak_b_usulan_km, 1, ',', '.') }} km
                                <span class="opacity-60">(garis lurus)</span>
                            </div>
                        </td>

                        {{-- Total penghematan --}}
                        <td class="whitespace-nowrap">
                            <div class="font-bold text-success text-lg">
                                −{{ number_format($s->total_hemat_km, 1, ',', '.') }} km
                            </div>
                            <div class="text-xs opacity-70">
                                A: −{{ number_format($s->hemat_a_km, 1, ',', '.') }} km<br>
                                B: −{{ number_format($s->hemat_b_km, 1, ',', '.') }} km
                            </div>
                        </td>

                        {{-- Status --}}
                        <td>
                            <span class="badge {{ $s->badgeStatus() }}">{{ $s->labelStatus() }}</span>
                            @if ($s->diprosesOleh)
                                <div class="text-xs opacity-70 mt-1">oleh {{ $s->diprosesOleh->name }}</div>
                            @endif
                            @if ($s->catatan_admin)
                                <div class="text-xs opacity-70 italic">"{{ $s->catatan_admin }}"</div>
                            @endif
                        </td>

                        {{-- Aksi: hanya untuk yang masih menunggu --}}
                        <td class="whitespace-nowrap">
                            @if ($s->status === 'menunggu')
                                @if (auth()->user()->hasRole('kadis'))
                                    <form method="POST" action="{{ route('swap.setujui', $s) }}" class="inline"
                                          onsubmit="return confirm('Setujui swap ini? Kedua guru akan langsung dimutasi.')">
                                        @csrf
                                        <button type="submit" class="btn btn-success btn-sm">Setujui</button>
                                    </form>
                                @endif
                                @if (auth()->user()->hasRole('kabid_ptk') || auth()->user()->hasRole('kadis'))
                                    <form method="POST" action="{{ route('swap.tolak', $s) }}" class="inline"
                                          onsubmit="const a = prompt('Alasan penolakan (wajib diisi):'); if (!a || !a.trim()) { alert('Alasan penolakan wajib diisi.'); return false; } this.querySelector('[name=catatan_admin]').value = a.trim(); return true;">
                                        @csrf
                                        <input type="hidden" name="catatan_admin" value="">
                                        <button type="submit" class="btn btn-error btn-sm">Tolak</button>
                                    </form>
                                @endif
                            @else
                                <span class="text-xs opacity-50">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-8 opacity-60">
                            Belum ada rekomendasi swap.
                            @if (auth()->user()->hasRole('kabid_ptk'))
                                Klik <strong>Generate Rekomendasi</strong> untuk mencari pasangan.
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </x-kartu>

    <div class="mt-4 flex flex-wrap items-center justify-between gap-2">
        <x-info-halaman :data="$swaps" />
        <div class="flex flex-wrap items-center gap-3">
            {{ $swaps->links() }}
            <x-per-halaman />
        </div>
    </div>
</div>

</div>
