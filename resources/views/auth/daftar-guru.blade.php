<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun Guru — PetaPenempatan ASN</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-base-200">

<div class="min-h-screen flex items-center justify-center p-6">
    <div class="w-full max-w-lg">
        <div class="text-center mb-6">
            <div class="text-5xl">🗺️</div>
            <h1 class="text-2xl font-extrabold mt-3">Daftar Akun Guru</h1>
            <p class="text-sm opacity-60 mt-1">
                Untuk guru yang belum punya akun. Pendaftaran diperiksa &amp; disetujui
                operator dinas sebelum akun aktif.
            </p>
        </div>

        <div class="card bg-base-100 shadow-2xl">
            <div class="card-body sm:p-8">
                @if (session('sukses'))
                    <div class="alert alert-success text-sm mb-4">{{ session('sukses') }}</div>
                @endif

                <x-galat />

                <form method="POST" action="{{ route('daftar-guru.simpan') }}">
                    @csrf

                    <fieldset class="fieldset w-full mb-3">
                        <label class="fieldset-legend font-semibold mb-1" for="f-nama">Nama lengkap (sesuai SK)</label>
                        <input id="f-nama" type="text" name="nama" value="{{ old('nama') }}" required
                               class="input w-full focus:input-primary" placeholder="cth: SITI AMINAH, S.Pd">
                    </fieldset>

                    <fieldset class="fieldset w-full mb-3">
                        <label class="fieldset-legend font-semibold mb-1" for="f-nip">NIP / NIPPPK</label>
                        <input id="f-nip" type="text" name="nip" value="{{ old('nip') }}"
                               class="input w-full focus:input-primary" placeholder="Bila belum ada, kosongkan dulu">
                        <p class="text-xs opacity-60 mt-1">Akun memakai NIP sebagai username &amp; password awal. Belum punya NIP? Tetap boleh mendaftar — operator yang memvalidasi.</p>
                    </fieldset>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3">
                        <fieldset class="fieldset w-full">
                            <label class="fieldset-legend font-semibold mb-1" for="f-mapel">Mata pelajaran</label>
                            <select id="f-mapel" name="mapel" class="select w-full focus:select-primary">
                                <option value="">— Pilih —</option>
                                @foreach ($daftarMapel as $m)
                                    <option value="{{ $m }}" @selected(old('mapel') === $m)>{{ $m }}</option>
                                @endforeach
                            </select>
                        </fieldset>
                        <fieldset class="fieldset w-full">
                            <label class="fieldset-legend font-semibold mb-1" for="f-jenjang">Jenjang</label>
                            <select id="f-jenjang" name="jenjang" class="select w-full focus:select-primary">
                                <option value="">— Pilih —</option>
                                @foreach (['TK', 'SD', 'SMP'] as $j)
                                    <option value="{{ $j }}" @selected(old('jenjang') === $j)>{{ $j }}</option>
                                @endforeach
                            </select>
                        </fieldset>
                    </div>

                    <fieldset class="fieldset w-full mb-3">
                        <label class="fieldset-legend font-semibold mb-1" for="f-status">Status kepegawaian</label>
                        <select id="f-status" name="status_kepegawaian" class="select w-full focus:select-primary">
                            <option value="">— Pilih —</option>
                            @foreach (\App\Models\Guru::STATUS_KEPEGAWAIAN as $kunci => $label)
                                <option value="{{ $kunci }}" @selected(old('status_kepegawaian') === $kunci)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </fieldset>

                    <fieldset class="fieldset w-full mb-3">
                        <label class="fieldset-legend font-semibold mb-1" for="f-sekolah">Sekolah tempat bertugas</label>
                        <select id="f-sekolah" name="sekolah_id" class="select w-full focus:select-primary">
                            <option value="">— Pilih sekolah —</option>
                            @foreach ($daftarSekolah as $s)
                                <option value="{{ $s->id }}" @selected((string) old('sekolah_id') === (string) $s->id)>{{ $s->nama }} ({{ $s->jenjang }})</option>
                            @endforeach
                        </select>
                    </fieldset>

                    <fieldset class="fieldset w-full mb-3">
                        <label class="fieldset-legend font-semibold mb-1" for="f-nama-sekolah">Nama sekolah (bila belum ada di daftar)</label>
                        <input id="f-nama-sekolah" type="text" name="nama_sekolah" value="{{ old('nama_sekolah') }}"
                               class="input w-full focus:input-primary">
                    </fieldset>

                    <fieldset class="fieldset w-full mb-5">
                        <label class="fieldset-legend font-semibold mb-1" for="f-hp">Nomor HP / WhatsApp</label>
                        <input id="f-hp" type="text" name="no_hp" value="{{ old('no_hp') }}" required
                               class="input w-full focus:input-primary" placeholder="08…">
                    </fieldset>

                    <button type="submit" class="btn btn-primary w-full btn-lg">Kirim Pendaftaran</button>
                    <div class="text-center mt-3">
                        <a href="{{ route('login') }}" class="link link-primary text-sm">← Kembali ke halaman masuk</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

</body>
</html>
