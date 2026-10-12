@extends('layouts.app')

@section('judul', 'Jejak Audit — PetaPenempatan ASN')

@section('konten')
<x-halaman judul="Jejak Audit Operasional">
    <x-slot:deskripsi>Append-only: catatan tidak bisa diubah/dihapus lewat aplikasi.</x-slot:deskripsi>
</x-halaman>

@livewire('tabel-aktivitas')
@endsection
