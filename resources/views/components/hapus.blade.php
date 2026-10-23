{{-- Tombol hapus: form DELETE + konfirmasi.
     Pakai: <x-hapus :aksi="route('guru.destroy', $g)" tanya="Hapus data Siti?" />
     Mode ikon: <x-hapus ... ikon-saja petunjuk="Hapus" /> (label menjadi
     tooltip title + aria-label) untuk kolom aksi tabel. --}}
@props(['aksi', 'tanya' => 'Hapus data ini?', 'label' => 'Hapus', 'ikonSaja' => false, 'petunjuk' => null])
<form method="POST" action="{{ $aksi }}" class="inline" data-konfirmasi data-judul="Hapus Data" data-pesan="{{ $tanya }}" data-tombol="Hapus" data-warna="btn-error" @if ($petunjuk) title="{{ $petunjuk }}" @endif>
    @csrf @method('DELETE')
    <button type="submit" {{ $attributes->merge(['class' => 'btn btn-sm btn-error gap-1']) }} @if ($ikonSaja) aria-label="{{ $label }}" @endif><x-ikon nama="sampah" class="h-4 w-4" />@unless ($ikonSaja){{ $label }}@endunless</button>
</form>
