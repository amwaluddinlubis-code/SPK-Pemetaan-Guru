{{-- Dashboard Eksekutif Pemda: ringkasan se-kabupaten, read-only --}}
@extends('layouts.app')

@section('judul', 'Eksekutif Pemda — PetaPenempatan ASN')

@section('konten')
    <h1 class="text-2xl font-bold mb-1"><x-ikon nama="distribusi" class="h-7 w-7 inline" /> Dashboard Eksekutif Pemda</h1>
    <p class="text-sm opacity-60 mb-4">Ringkasan penempatan Guru ASN se-Kabupaten — hanya lihat, tanpa ubah data.</p>

    {{-- Kartu ringkasan: metrik aksi (menunggu) paling menonjol --}}
    <div class="stats stats-vertical sm:stats-horizontal shadow w-full mb-6" data-testid="ringkasan-eksekutif">
        <div class="stat">
            <div class="stat-title">Total Guru</div>
            <div class="stat-value text-primary">{{ $totalGuru }}</div>
            <div class="stat-desc">terdata di sistem</div>
        </div>
        <div class="stat">
            <div class="stat-title">Total Sekolah</div>
            <div class="stat-value text-secondary">{{ $totalSekolah }}</div>
            <div class="stat-desc">terdata di sistem</div>
        </div>
        <div class="stat">
            <div class="stat-title">Kebutuhan Guru</div>
            <div class="stat-value text-warning">{{ $totalKebutuhan }}</div>
            <div class="stat-desc">formasi belum terpenuhi</div>
        </div>
        <div class="stat bg-error/10 border-l-4 border-error">
            <div class="stat-title font-bold text-error">Menunggu Keputusan</div>
            <div class="stat-value text-error">{{ $totalMenunggu }}</div>
            <div class="stat-desc">{{ $menungguUsulan }} usulan · {{ $menungguSpk }} SPK · {{ $menungguSwap }} swap</div>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        {{-- Distribusi per kecamatan --}}
        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <h2 class="card-title text-lg">📍 Distribusi per Kecamatan</h2>
                <div class="overflow-x-auto">
                    <table class="table table-zebra">
                        <thead>
                            <tr>
                                <th>Kecamatan</th>
                                <th class="text-right">Guru</th>
                                <th class="text-right">Sekolah</th>
                                <th class="text-right">Kebutuhan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($perKecamatan as $r)
                                <tr>
                                    <td class="text-sm font-semibold">{{ $r['kecamatan'] }}</td>
                                    <td class="text-sm text-right">{{ $r['guru'] }}</td>
                                    <td class="text-sm text-right">{{ $r['sekolah'] }}</td>
                                    <td class="text-sm text-right">
                                        @if ($r['kebutuhan'] > 0)
                                            <span class="badge badge-warning">{{ $r['kebutuhan'] }}</span>
                                        @else
                                            <span class="text-sm opacity-50">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-sm opacity-50">Belum ada data kecamatan.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Distribusi per jenjang --}}
        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <h2 class="card-title text-lg">🎓 Distribusi per Jenjang</h2>
                <div class="overflow-x-auto">
                    <table class="table table-zebra">
                        <thead>
                            <tr>
                                <th>Jenjang</th>
                                <th class="text-right">Guru</th>
                                <th class="text-right">Sekolah</th>
                                <th class="text-right">Kebutuhan</th>
                                <th class="text-right">Defisit</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($perJenjang as $r)
                                <tr>
                                    <td><span class="badge badge-outline">{{ $r['jenjang'] }}</span></td>
                                    <td class="text-sm text-right">{{ $r['guru'] }}</td>
                                    <td class="text-sm text-right">{{ $r['sekolah'] }}</td>
                                    <td class="text-sm text-right">{{ $r['kebutuhan'] }}</td>
                                    <td class="text-sm text-right">
                                        @if ($r['defisit'] > 0)
                                            <span class="badge badge-error">{{ $r['defisit'] }}</span>
                                        @else
                                            <span class="text-sm opacity-50">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <p class="text-xs opacity-50 mt-2">Jenjang sekolah disimpulkan dari awalan nama sekolah.</p>
            </div>
        </div>
    </div>

    {{-- Rekap status --}}
    @php
        $warna = [
            'diajukan' => 'badge-warning', 'diverifikasi' => 'badge-info',
            'rekomendasi' => 'badge-warning', 'menunggu' => 'badge-warning',
            'disetujui' => 'badge-success', 'ditolak' => 'badge-error',
            'batal_karena_konflik' => 'badge-ghost',
        ];
    @endphp
    <div class="grid gap-6 lg:grid-cols-3 mt-6">
        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <h2 class="card-title text-lg">📝 Usulan Penempatan</h2>
                @forelse ($rekapUsulan as $status => $jml)
                    <div class="flex justify-between items-center py-1">
                        <span class="badge {{ $warna[$status] ?? 'badge-ghost' }}">{{ \App\Models\UsulanPenempatan::STATUS[$status] ?? $status }}</span>
                        <span class="font-bold">{{ $jml }}</span>
                    </div>
                @empty
                    <p class="text-sm opacity-50">Belum ada usulan.</p>
                @endforelse
            </div>
        </div>
        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <h2 class="card-title text-lg">🎯 SPK Penempatan</h2>
                @forelse ($rekapSpk as $status => $jml)
                    <div class="flex justify-between items-center py-1">
                        <span class="badge {{ $warna[$status] ?? 'badge-ghost' }}">{{ \App\Models\HasilSpk::STATUS[$status] ?? $status }}</span>
                        <span class="font-bold">{{ $jml }}</span>
                    </div>
                @empty
                    <p class="text-sm opacity-50">Belum ada hasil SPK.</p>
                @endforelse
            </div>
        </div>
        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <h2 class="card-title text-lg">🔄 Mutasi Silang</h2>
                @forelse ($rekapSwap as $status => $jml)
                    <div class="flex justify-between items-center py-1">
                        <span class="badge {{ $warna[$status] ?? 'badge-ghost' }}">{{ \App\Models\RekomendasiSwap::STATUS[$status] ?? $status }}</span>
                        <span class="font-bold">{{ $jml }}</span>
                    </div>
                @empty
                    <p class="text-sm opacity-50">Belum ada rekomendasi swap.</p>
                @endforelse
            </div>
        </div>
    </div>
@endsection
