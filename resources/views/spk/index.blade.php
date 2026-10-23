@extends('layouts.app')

@section('judul', 'SPK Penempatan — PetaPenempatan ASN')

@section('konten')
{{-- Halaman SPK Penempatan (Skenario 1): rekomendasi pemindahan guru yang jarak
     tugasnya melebihi ambang batas ke sekolah terdekat yang kekurangan
     formasi untuk mata pelajarannya. Daftar di bawah Livewire (tanpa reload). --}}

<x-halaman judul="🎯 SPK Penempatan">
    <x-slot:deskripsi>
        Rekomendasi pengisian formasi: guru dengan jarak tugas &gt; {{ \App\Http\Controllers\SpkController::AMBANG_KM }} km
        dipasangkan ke sekolah yang kekurangan mapelnya. Kelayakan disaring secara greedy,
        lalu alternatif diperingkat dengan skor hibrida SAW + TOPSIS.
    </x-slot:deskripsi>
    <x-slot:aksi>
        {{-- Tombol generate: khusus administrator (kabid_ptk) --}}
        @if (auth()->user()->hasRole('kabid_ptk'))
            <form method="POST" action="{{ route('spk.generate') }}"
                  onsubmit="return confirm('Jalankan SPK untuk semua guru yang jaraknya melebihi {{ \App\Http\Controllers\SpkController::AMBANG_KM }} km?')">
                @csrf
                <button type="submit" class="btn btn-primary">⚙️ Generate SPK</button>
            </form>
        @endif
    </x-slot:aksi>
</x-halaman>

<div class="card bg-base-100 shadow mb-4">
    <div class="card-body py-3 text-sm">
        <strong>🧮 Metode peringkat — Greedy + SAW–TOPSIS.</strong>
        Greedy menyaring kelayakan (jarak &gt; {{ \App\Http\Controllers\SpkController::AMBANG_KM }} km, tujuan lebih dekat,
        formasi tersedia, guru asal sekolah 3T dilindungi) dan mengalokasikan formasi tanpa over-booking.
        Alternatif yang lolos lalu dinilai SAW dan TOPSIS dengan kriteria:
        penghematan jarak <strong>40%</strong>, jarak tujuan <strong>25%</strong>, sisa formasi <strong>25%</strong>,
        prioritas daerah tujuan <strong>10%</strong>. Skor akhir = rata-rata SAW dan TOPSIS (0–100);
        rincian kriteria tersimpan pada setiap rekomendasi dan tercantum di telaahan staf.
    </div>
</div>

@livewire('tabel-spk')
@endsection
