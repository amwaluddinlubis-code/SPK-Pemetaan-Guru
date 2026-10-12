@extends('layouts.app')

@section('judul', 'Mutasi Silang — PetaPenempatan ASN')

@section('konten')
{{-- Halaman Mutasi Silang: daftar pasangan guru yang direkomendasikan untuk
     bertukar sekolah agar keduanya lebih dekat ke domisili (win-win).
     Daftar di bawah Livewire (tanpa reload). --}}

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

@livewire('tabel-swap')
@endsection
