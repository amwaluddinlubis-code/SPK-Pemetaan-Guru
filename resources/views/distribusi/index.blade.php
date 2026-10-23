@extends('layouts.app')

@section('judul', 'Distribusi Defisit & Surplus — PetaPenempatan ASN')

@section('konten')
<div class="mb-4">
    <h1 class="text-2xl font-bold">Distribusi Defisit & Surplus Guru</h1>
    <p class="text-sm opacity-70 mt-1">
        Rumus: <code>defisit = max(kebutuhan − tersedia, 0)</code>,
        <code>surplus = max(tersedia − kebutuhan, 0)</code>.
        Guru dalam proses mutasi (rekomendasi aktif) dihitung sekali di sekolah asal.
        Hanya data agregat — tanpa identitas guru.
    </p>
</div>

@livewire('tabel-distribusi')
@endsection
