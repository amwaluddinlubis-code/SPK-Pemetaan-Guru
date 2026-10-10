@extends('layouts.app')

@section('judul', 'SPK Penempatan')

@section('konten')
{{-- Halaman SPK Penempatan (Skenario 1): rekomendasi pemindahan guru yang jarak
     tugasnya melebihi ambang batas ke sekolah terdekat yang kekurangan
     formasi untuk mata pelajarannya. --}}

<div class="flex flex-wrap items-center justify-between gap-3 mb-4">
    <div>
        <h1 class="text-2xl font-bold">🎯 SPK Penempatan</h1>
        <p class="text-sm opacity-70 mt-1">
            Rekomendasi pengisian formasi: guru dengan jarak tugas &gt; {{ $ambangKm }} km
            dipasangkan ke sekolah terdekat yang kekurangan mapelnya.
        </p>
    </div>

    {{-- Tombol generate: khusus administrator (kabid_ptk) --}}
    @if (auth()->user()->hasRole('kabid_ptk'))
        <form method="POST" action="{{ route('spk.generate') }}"
              onsubmit="return confirm('Jalankan SPK untuk semua guru yang jaraknya melebihi {{ $ambangKm }} km?')">
            @csrf
            <button type="submit" class="btn btn-primary">⚙️ Generate SPK</button>
        </form>
    @endif
</div>

{{-- Filter status --}}
<div class="flex flex-wrap gap-2 mb-4">
    <a href="{{ route('spk.index') }}"
       class="badge {{ !$statusAktif ? 'badge-primary' : 'badge-ghost' }} badge-lg">Semua</a>
    @foreach (\App\Models\HasilSpk::STATUS as $kode => $label)
        <a href="{{ route('spk.index', ['status' => $kode]) }}"
           class="badge {{ $statusAktif === $kode ? 'badge-primary' : 'badge-ghost' }} badge-lg">
            {{ $label }}
        </a>
    @endforeach
</div>

<div class="overflow-x-auto bg-base-100 rounded-box shadow">
    <table class="table table-zebra">
        <thead>
            <tr>
                <th>Guru</th>
                <th>Sekolah Tujuan</th>
                <th>Jarak</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($spks as $s)
                <tr>
                    {{-- Guru: nama, mapel, sekolah asal + jarak saat ini --}}
                    <td>
                        <div class="font-semibold">{{ $s->guru->nama }}</div>
                        <div class="text-xs opacity-70">{{ $s->guru->mapel }}</div>
                        <div class="text-xs mt-1">
                            Asal: {{ $s->guru->sekolah->nama ?? '-' }}
                        </div>
                        <div class="text-xs opacity-70">
                            Jarak sekarang: {{ number_format($s->jarak_sekarang_km, 1, ',', '.') }} km
                        </div>
                    </td>

                    {{-- Sekolah tujuan + jarak usulan --}}
                    <td>
                        <div class="font-semibold text-primary">{{ $s->sekolahTujuan->nama }}</div>
                        <div class="text-xs opacity-70">{{ $s->sekolahTujuan->kecamatan }}</div>
                        <div class="text-xs mt-1">
                            Jarak usulan: {{ number_format($s->jarak_km, 1, ',', '.') }} km
                        </div>
                    </td>

                    {{-- Penghematan --}}
                    <td class="whitespace-nowrap">
                        <div class="font-bold text-success text-lg">
                            −{{ number_format($s->hematKm(), 1, ',', '.') }} km
                        </div>
                        <div class="text-xs opacity-70">lebih dekat</div>
                    </td>

                    {{-- Status --}}
                    <td>
                        @php
                            $warna = [
                                'rekomendasi' => 'badge-warning',
                                'disetujui' => 'badge-success',
                                'ditolak' => 'badge-error',
                            ][$s->status] ?? 'badge-ghost';
                        @endphp
                        <span class="badge {{ $warna }}">{{ $s->labelStatus() }}</span>
                        @if ($s->diprosesOleh)
                            <div class="text-xs opacity-70 mt-1">oleh {{ $s->diprosesOleh->name }}</div>
                        @endif
                        @if ($s->catatan_admin)
                            <div class="text-xs opacity-70 italic">"{{ $s->catatan_admin }}"</div>
                        @endif
                    </td>

                    {{-- Aksi: hanya untuk yang masih berstatus rekomendasi --}}
                    <td class="whitespace-nowrap">
                        @if ($s->status === 'rekomendasi')
                            @if (auth()->user()->hasRole('kadis'))
                                <form method="POST" action="{{ route('spk.setujui', $s) }}" class="inline"
                                      onsubmit="return confirm('Setujui penempatan ini? Guru akan langsung dimutasi.')">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-sm">Setujui</button>
                                </form>
                            @endif
                            @if (auth()->user()->hasRole('kabid_ptk') || auth()->user()->hasRole('kadis'))
                                <form method="POST" action="{{ route('spk.tolak', $s) }}" class="inline"
                                      onsubmit="return confirm('Tolak rekomendasi penempatan ini?')">
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
                        Belum ada rekomendasi SPK.
                        @if (auth()->user()->hasRole('kabid_ptk'))
                            Klik <strong>Generate SPK</strong> untuk mencari penempatan.
                        @endif
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">
    {{ $spks->links() }}
</div>
@endsection
