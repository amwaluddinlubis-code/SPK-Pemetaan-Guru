{{--
    Chip konteks Pemda di halaman daftar: menyatakan cakupan wilayah yang
    sedang ditampilkan + lingkup pribadi sesuai peran pengguna.
    Hanya tampil selama konteks Pemda aktif.
    Pakai: <x-chip-konteks jenis="sekolah" /> atau jenis="guru".
--}}
@props(['jenis' => 'sekolah'])

@if (\App\Services\KonteksPemda::aktif())
    @php $pengguna = auth()->user(); @endphp
    <div class="alert alert-info mb-4 py-2" data-testid="chip-konteks">
        <div class="text-sm">
            <span class="font-semibold">📍 Konteks {{ \App\Services\KonteksPemda::namaPemda() }}</span>
            —
            @if ($jenis === 'sekolah')
                {{ \App\Models\Sekolah::count() }} sekolah tampil
            @else
                {{ \App\Models\Guru::count() }} guru tampil
            @endif
            · {{ \App\Services\KonteksPemda::daftarKecamatan()->count() }} kecamatan dalam konteks
            @if ($pengguna->role === 'kepala_sekolah' && $pengguna->sekolah)
                <br>🏫 Sekolah Anda: {{ $pengguna->sekolah->nama }} (Kec. {{ $pengguna->sekolah->kecamatan }})
            @elseif ($pengguna->role === 'ketua_pgri' && $pengguna->kecamatan)
                <br>🗺️ Wilayah pantauan Anda: Kec. {{ $pengguna->kecamatan }}
            @elseif ($pengguna->role === 'guru' && $pengguna->guru?->sekolah)
                <br>🏫 Penempatan Anda: {{ $pengguna->guru->sekolah->nama }}
            @endif
        </div>
    </div>
@endif
