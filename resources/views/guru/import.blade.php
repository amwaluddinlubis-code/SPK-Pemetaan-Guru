{{-- Import data guru dari Excel/CSV (khusus kabid_ptk) --}}
@extends('layouts.app')
@section('judul', 'Import Guru — PetaPenempatan ASN')

@section('konten')
    <h1 class="text-2xl font-bold mb-4">📥 Import Data Guru</h1>

    <div class="grid gap-4 lg:grid-cols-2">
        {{-- Form unggah --}}
        <div class="card bg-base-100 shadow-xl">
            <div class="card-body">
                <h2 class="card-title">Unggah File</h2>
                <x-galat />
                <form method="POST" action="{{ route('guru.import.proses') }}" enctype="multipart/form-data">
                    @csrf
                    <fieldset class="fieldset w-full">
                        <legend class="fieldset-legend font-semibold">File Excel/CSV (maks. 5 MB)</legend>
                        <input type="file" name="berkas" accept=".xlsx,.xls,.csv" required
                               class="file-input w-full">
                    </fieldset>
                    <div class="flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary">⬆️ Proses Import</button>
                        <a href="{{ route('guru.index') }}" class="btn btn-ghost">Batal</a>
                    </div>
                </form>
                <div class="divider">Belum punya file?</div>
                <a href="{{ route('guru.template') }}" class="btn btn-secondary btn-sm">
                    ⬇️ Unduh Template Excel
                </a>
            </div>
        </div>

        {{-- Petunjuk format --}}
        <div class="card bg-base-100 shadow-xl">
            <div class="card-body">
                <h2 class="card-title">Format Kolom</h2>
                <p class="text-sm opacity-70">
                    Baris pertama <strong>wajib persis</strong> seperti template.
                    Kolom bertanda <span class="badge badge-error badge-sm">*</span> wajib diisi.
                    Baris valid langsung tersimpan; baris gagal dilaporkan tanpa menggagalkan yang lain.
                </p>
                <div class="overflow-x-auto">
                    <table class="table table-sm">
                        <thead><tr><th>Kolom</th><th>Keterangan</th></tr></thead>
                        <tbody>
                            <tr><td><code>nip</code> *</td><td>Unik, maks. 30 karakter</td></tr>
                            <tr><td><code>nama</code> *</td><td>Maks. 100 karakter</td></tr>
                            <tr><td><code>mapel</code> *</td><td>Mis. Matematika, IPA</td></tr>
                            <tr><td><code>jenjang</code> *</td><td>SD / SMP / SMA</td></tr>
                            <tr><td><code>domisili_alamat</code> *</td><td>Alamat rumah</td></tr>
                            <tr><td><code>kecamatan</code></td><td>Nama kecamatan Madina (mis. Panyabungan)</td></tr>
                            <tr><td><code>domisili_lat</code> *</td><td>-90 s.d. 90 (contoh: 0.855)</td></tr>
                            <tr><td><code>domisili_lng</code> *</td><td>-180 s.d. 180 (contoh: 99.555)</td></tr>
                            <tr><td><code>sekolah_saat_ini</code> *</td><td>Nama sekolah tempat bertugas</td></tr>
                            <tr><td><code>sekolah_saat_ini_lat</code></td><td>Boleh kosong</td></tr>
                            <tr><td><code>sekolah_saat_ini_lng</code></td><td>Boleh kosong</td></tr>
                            <tr><td><code>npsn</code></td><td>NPSN agar terhubung ke data sekolah (boleh kosong)</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
