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
                @if ($errors->any())
                    <div class="alert alert-error">
                        <ul class="list-disc list-inside text-sm">
                            @foreach ($errors->all() as $pesan)
                                <li>{{ $pesan }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <form method="POST" action="{{ route('sekolah.import.proses') }}" enctype="multipart/form-data">
                    @csrf
                    <label class="form-control w-full">
                        <span class="label-text font-semibold">File Excel/CSV (maks. 5 MB)</span>
                        <input type="file" name="berkas" accept=".xlsx,.xls,.csv" required
                               class="file-input file-input-bordered w-full">
                    </label>
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
                            <tr><td><code>npsn</code> *</td><td>Unik, 8 digit</td></tr>
                            <tr><td><code>nama</code> *</td><td>Nama sekolah</td></tr>
                            <tr><td><code>alamat</code></td><td>Alamat lengkap</td></tr>
                            <tr><td><code>kecamatan</code></td><td>Nama kecamatan Madina</td></tr>
                            <tr><td><code>lat</code></td><td>-90 s.d. 90 (boleh kosong)</td></tr>
                            <tr><td><code>lng</code></td><td>-180 s.d. 180 (boleh kosong)</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
