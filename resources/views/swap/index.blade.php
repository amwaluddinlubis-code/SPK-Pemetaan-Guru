@extends('layouts.app')

@section('judul', 'Mutasi Silang — PetaPenempatan ASN')

@section('konten')
{{-- Halaman Mutasi Silang: daftar pasangan guru yang direkomendasikan untuk
     bertukar sekolah agar keduanya lebih dekat ke domisili (win-win).
     Daftar di bawah Livewire (tanpa reload). --}}

<x-halaman judul="🔄 Mutasi Silang (Swap)">
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
                <button type="submit" class="btn btn-primary">⚙️ Generate Rekomendasi</button>
            </form>
        @endif
    </x-slot:aksi>
</x-halaman>

@livewire('tabel-swap')
@endsection
