{{-- Tombol hapus: form DELETE + konfirmasi.
     Pakai: <x-hapus :aksi="route('guru.destroy', $g)" tanya="Hapus data Siti?" /> --}}
@props(['aksi', 'tanya' => 'Hapus data ini?', 'label' => 'Hapus'])
<form method="POST" action="{{ $aksi }}" class="inline" onsubmit="return confirm('{{ $tanya }}')">
    @csrf @method('DELETE')
    <button type="submit" {{ $attributes->merge(['class' => 'btn btn-sm btn-error gap-1']) }}><x-ikon nama="sampah" class="h-4 w-4" />{{ $label }}</button>
</form>
