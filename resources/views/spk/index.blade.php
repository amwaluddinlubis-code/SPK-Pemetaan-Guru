@extends('layouts.app')

@section('judul', 'SPK Penempatan — PetaPenempatan ASN')

@section('konten')
{{-- Halaman SPK Penempatan (Skenario 1): rekomendasi pemindahan guru yang jarak
     tugasnya melebihi ambang batas ke sekolah terdekat yang kekurangan
     formasi untuk mata pelajarannya. Daftar di bawah Livewire (tanpa reload). --}}

<div class="flex flex-wrap items-center justify-between gap-3 mb-4">
    <div>
        <h1 class="text-2xl font-bold">🎯 SPK Penempatan</h1>
        <p class="text-sm opacity-70 mt-1">
            Rekomendasi pengisian formasi: guru dengan jarak tugas &gt; {{ \App\Http\Controllers\SpkController::AMBANG_KM }} km
            dipasangkan ke sekolah terdekat yang kekurangan mapelnya.
        </p>
    </div>

    {{-- Tombol generate: khusus administrator (kabid_ptk) --}}
    @if (auth()->user()->hasRole('kabid_ptk'))
        <form method="POST" action="{{ route('spk.generate') }}"
              onsubmit="return confirm('Jalankan SPK untuk semua guru yang jaraknya melebihi {{ \App\Http\Controllers\SpkController::AMBANG_KM }} km?')">
            @csrf
            <button type="submit" class="btn btn-primary">⚙️ Generate SPK</button>
        </form>
    @endif
</div>

@livewire('tabel-spk')
@endsection
