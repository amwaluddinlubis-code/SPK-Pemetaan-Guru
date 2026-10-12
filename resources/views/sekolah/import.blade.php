{{-- Import data sekolah dari Excel/CSV (khusus kabid_ptk) --}}
@extends('layouts.app')
@section('judul', 'Import Sekolah — PetaPenempatan ASN')

@section('konten')
    <h1 class="text-2xl font-bold mb-4">📥 Import Data Sekolah</h1>

    <div class="grid gap-4 lg:grid-cols-2">
        {{-- Form unggah --}}
        <div class="card bg-base-100 shadow-xl">
            <div class="card-body">
                <h2 class="card-title">Unggah File</h2>
                <x-galat />
                <form method="POST" action="{{ route('sekolah.import.proses') }}" enctype="multipart/form-data">
                    @csrf
                    <fieldset class="fieldset w-full">
                        <legend class="fieldset-legend font-semibold">File Excel/CSV (maks. 5 MB)</legend>
                        <input type="file" name="berkas" accept=".xlsx,.xls,.csv" required
                               class="file-input w-full">
                    </fieldset>
                    <div class="flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary">⬆️ Proses Import</button>
                        <a href="{{ route('sekolah.index') }}" class="btn btn-ghost">Batal</a>
                    </div>
                </form>
                <div class="divider">Belum punya file?</div>
                <a href="{{ route('sekolah.template') }}" class="btn btn-secondary btn-sm">
                    ⬇️ Unduh Template Excel
                </a>
            </div>
        </div>

        {{-- Petunjuk kolom --}}
        <div class="card bg-base-100 shadow-xl">
            <div class="card-body">
                <h2 class="card-title">Format Kolom</h2>
                <p class="text-sm opacity-70">Header harus sama persis (urutan boleh tetap). Baris kosong dilewati.</p>
                <ol class="list-decimal list-inside text-sm space-y-1 mt-2">
                    @foreach ($kolom as $k)
                        <li><code>{{ $k }}</code></li>
                    @endforeach
                </ol>
                <p class="text-sm mt-2 opacity-70">
                    <code>kategori_daerah</code>: biasa / terpencil / sangat_terpencil (boleh kosong = biasa).
                    <code>lat</code>/<code>lng</code>: angka koordinat.
                </p>
            </div>
        </div>
    </div>
@endsection
