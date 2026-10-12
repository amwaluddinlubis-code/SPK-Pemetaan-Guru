{{-- Halaman analisis kebutuhan guru per sekolah.
     Hitung otomatis: rombel (Dapodik) × JP Permendikdasmen 13/2025 ÷ 24 JP.
     SD: guru kelas (= rombel) + PJOK + Agama. SMP: per mapel. --}}
@extends('layouts.app')
@section('judul', 'Analisis Kebutuhan Guru — PetaPenempatan ASN')

@section('konten')
    <h1 class="text-2xl font-bold mb-1">Analisis Kebutuhan Guru</h1>
    <p class="opacity-70 mb-4">{{ $sekolah->nama }} · NPSN {{ $sekolah->npsn }} · {{ $sekolah->kecamatan }}</p>

    <x-galat />

    @if ($rombel <= 0)
        <x-alert tipe="warning" class="mb-4">
            Jumlah rombel sekolah ini belum diisi, jadi kebutuhan belum bisa dihitung.
            <a href="{{ route('sekolah.edit', $sekolah) }}" class="link font-semibold">Isi rombel di form ubah sekolah</a>
            (angka Rombel tersedia di CSV ekspor Dapodik per kecamatan).
        </x-alert>
    @else
        <div class="card bg-base-100 shadow-xl mb-4">
            <div class="card-body">
                <div class="flex flex-wrap gap-2 mb-4">
                    <span class="badge badge-info">Jenjang: {{ $jenjang }}</span>
                    <span class="badge badge-info">Rombel: {{ $rombel }}</span>
                    <span class="badge badge-ghost">Acuan beban mengajar: {{ $bebanMengajar }} JP/minggu</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="table table-zebra">
                        <thead>
                            <tr>
                                <th>Jenis / Mata Pelajaran</th>
                                <th class="text-right">JP/Minggu</th>
                                <th class="text-right">Kebutuhan (hitung)</th>
                                <th class="text-right">Guru Ada</th>
                                <th class="text-right">Selisih</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($baris as $b)
                                <tr>
                                    <td class="font-semibold">{{ $b['label'] }}</td>
                                    <td class="text-right">{{ $b['jp_per_minggu'] ?? '—' }}</td>
                                    <td class="text-right">{{ $b['kebutuhan'] }}</td>
                                    <td class="text-right">{{ $b['eksisting'] }}</td>
                                    <td class="text-right">
                                        @if ($b['selisih'] < 0)
                                            <span class="badge badge-error">kurang {{ abs($b['selisih']) }}</span>
                                        @elseif ($b['selisih'] > 0)
                                            <span class="badge badge-warning">lebih {{ $b['selisih'] }}</span>
                                        @else
                                            <span class="badge badge-success">pas</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <p class="text-sm opacity-70 mt-4">
                    Rumus: <code>kebutuhan = ceil(rombel × JP/minggu ÷ {{ $bebanMengajar }})</code>;
                    guru kelas SD = jumlah rombel. JP/minggu mengikuti Permendikdasmen No. 13/2025
                    (intrakurikuler saja). Selisih negatif berarti kekurangan guru.
                </p>

                @if (auth()->user()->hasRole('kepala_sekolah', 'kabid_ptk'))
                    <form method="POST" action="{{ route('sekolah.analisis.terapkan', $sekolah) }}" class="mt-4"
                          onsubmit="return confirm('Terapkan hasil hitungan ini sebagai kebutuhan guru resmi? Baris kebutuhan lama akan diganti.')">
                        @csrf
                        <button type="submit" class="btn btn-primary">✓ Terapkan sebagai Kebutuhan Guru</button>
                        <a href="{{ route('sekolah.index') }}" class="btn btn-ghost">Kembali</a>
                    </form>
                @else
                    <a href="{{ route('sekolah.index') }}" class="btn btn-ghost mt-4">Kembali</a>
                @endif
            </div>
        </div>
    @endif
@endsection
