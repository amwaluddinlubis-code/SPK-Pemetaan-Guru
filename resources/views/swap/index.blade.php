@extends('layouts.app')

@section('judul', 'Mutasi Silang — PetaPenempatan ASN')

@section('konten')
{{-- Halaman Mutasi Silang: daftar pasangan guru yang direkomendasikan untuk
     bertukar sekolah agar keduanya lebih dekat ke domisili (win-win). --}}

<div class="flex flex-wrap items-center justify-between gap-3 mb-4">
    <div>
        <h1 class="text-2xl font-bold">🔄 Mutasi Silang (Swap)</h1>
        <p class="text-sm opacity-70 mt-1">
            Rekomendasi pertukaran dua guru bermapel sama: keduanya menjadi
            lebih dekat ke domisili setelah bertukar sekolah.
        </p>
    </div>

    {{-- Tombol generate: khusus administrator (kabid_ptk) --}}
    @if (auth()->user()->hasRole('kabid_ptk'))
        <form method="POST" action="{{ route('swap.generate') }}"
              onsubmit="return confirm('Jalankan pencocokan pasangan swap untuk semua guru?')">
            @csrf
            <button type="submit" class="btn btn-primary">⚙️ Generate Rekomendasi</button>
        </form>
    @endif
</div>

{{-- Filter status --}}
<div class="flex flex-wrap gap-2 mb-4">
    <a href="{{ route('swap.index') }}"
       class="badge {{ !$statusAktif ? 'badge-primary' : 'badge-ghost' }} badge-lg">Semua</a>
    @foreach (\App\Models\RekomendasiSwap::STATUS as $kode => $label)
        <a href="{{ route('swap.index', ['status' => $kode]) }}"
           class="badge {{ $statusAktif === $kode ? 'badge-primary' : 'badge-ghost' }} badge-lg">
            {{ $label }}
        </a>
    @endforeach
</div>

<div class="overflow-x-auto bg-base-100 rounded-box shadow">
    <table class="table table-zebra">
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
                                      onsubmit="return confirm('Tolak rekomendasi swap ini?')">
                                    @csrf
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
</div>

<div class="mt-4">
    {{ $swaps->links() }}
</div>
@endsection
