@extends('layouts.app')

@section('judul', 'Jejak Audit — PetaPenempatan ASN')

@section('konten')
<div class="flex flex-wrap items-center justify-between gap-3 mb-4">
    <h1 class="text-2xl font-bold">Jejak Audit Operasional</h1>
    <span class="text-sm opacity-60">Append-only: catatan tidak bisa diubah/dihapus lewat aplikasi.</span>
</div>

@livewire('tabel-aktivitas')
@endsection
