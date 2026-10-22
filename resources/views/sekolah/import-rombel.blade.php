{{-- Import jumlah rombel dari CSV ekspor Dapodik per kecamatan (khusus kabid_ptk) --}}
@extends('layouts.app')
@section('judul', 'Import Rombel — PetaPenempatan ASN')

@section('konten')
    <h1 class="text-2xl font-bold mb-4">📊 Import Rombel dari Dapodik</h1>

    <div class="grid gap-4 lg:grid-cols-2">
        {{-- Form unggah --}}
        <div class="card bg-base-100 shadow-xl">
            <div class="card-body">
                <h2 class="card-title">Unggah File CSV</h2>
                <x-galat />
                <form method="POST" action="{{ route('sekolah.import-rombel.proses') }}" enctype="multipart/form-data">
                    @csrf
                    <fieldset class="fieldset w-full">
                        <legend class="fieldset-legend font-semibold">File CSV (maks. 5 MB)</legend>
                        <input type="file" name="berkas" accept=".csv,.txt" required
                               class="file-input w-full">
                    </fieldset>
                    <div class="flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary">⬆️ Proses Import</button>
                        <a href="{{ route('sekolah.index') }}" class="btn btn-ghost">Batal</a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Petunjuk --}}
        <div class="card bg-base-100 shadow-xl">
            <div class="card-body">
                <h2 class="card-title">Cara Mendapatkan File</h2>
                <ol class="list-decimal list-inside text-sm space-y-2 mt-2">
                    <li>Buka halaman progres Dapodik per kecamatan, mis.<br>
                        <code class="text-xs">dapo.kemendikdasmen.go.id/progres/070000/071500/071505</code></li>
                    <li>Klik tombol <strong>Ekspor CSV</strong> (tanpa login).</li>
                    <li>Unggah file <code class="text-xs">sekolah-Kec_*.csv</code> di form ini. Ulangi per kecamatan.</li>
                </ol>
                <div class="divider">Aturan import</div>
                <ul class="list-disc list-inside text-sm space-y-1 opacity-80">
                    <li>Kolom yang dibaca: <code>NPSN</code> (pencocokan) dan <code>Rombel</code>.</li>
                    <li>NPSN yang tidak dikenal di database dilaporkan, <strong>tidak</strong> dibuatkan sekolah baru.</li>
                    <li>Baris kosong dilewati; rombel harus angka 0–300.</li>
                </ul>
            </div>
        </div>
    </div>
@endsection
