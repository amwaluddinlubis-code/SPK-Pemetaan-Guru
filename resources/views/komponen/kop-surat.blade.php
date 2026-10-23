{{-- Kop surat resmi untuk dokumen cetak: logo pemda (Branding) di kiri,
     nama pemda aktif (KonteksPemda) + Dinas Pendidikan di tengah.
     Dipakai telaahan staf & halaman cetak daftar (guru/sekolah/usulan).
     Tanpa konteks pemda, kop memakai nama aplikasi. --}}
@php
    $kopNamaPemda = \App\Services\KonteksPemda::namaPemda();
    $kopLogo = \App\Services\Branding::logoUrl();
@endphp
<style>
    .kop-surat { display: flex; align-items: center; gap: 16px; border-bottom: 3px double #111; padding-bottom: 10px; margin-bottom: 18px; }
    .kop-surat img.kop-logo { height: 68px; width: 68px; object-fit: contain; }
    .kop-surat .kop-penyeimbang { width: 68px; flex: none; }
    .kop-surat .kop-teks { flex: 1; text-align: center; }
    .kop-surat .kop-pemda { font-size: 17px; font-weight: bold; text-transform: uppercase; letter-spacing: .02em; margin: 0; }
    .kop-surat .kop-dinas { font-size: 15px; font-weight: bold; text-transform: uppercase; margin: 2px 0 0; }
    .kop-surat .kop-sub { font-size: 11px; margin: 2px 0 0; color: #333; }
</style>
<div class="kop-surat">
    @if ($kopLogo)
        <img src="{{ $kopLogo }}" alt="Logo pemda" class="kop-logo">
    @endif
    <div class="kop-teks">
        <p class="kop-pemda">{{ $kopNamaPemda ? strtoupper($kopNamaPemda) : 'PetaPenempatan ASN' }}</p>
        <p class="kop-dinas">Dinas Pendidikan</p>
        <p class="kop-sub">Sistem Informasi Pemetaan Penempatan Guru ASN</p>
    </div>
    @if ($kopLogo)
        <span class="kop-penyeimbang"></span>
    @endif
</div>
