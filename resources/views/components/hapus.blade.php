{{-- Tombol hapus: form DELETE + konfirmasi.
     Pakai: <x-hapus :aksi="route('guru.destroy', $g)" tanya="Hapus data Siti?" /> --}}
@props(['aksi', 'tanya' => 'Hapus data ini?', 'label' => 'Hapus'])
<form method="POST" action="{{ $aksi }}" class="inline" onsubmit="return confirm('{{ $tanya }}')">
    @csrf @method('DELETE')
    <button type="submit" {{ $attributes->merge(['class' => 'btn btn-sm btn-error']) }}>{{ $label }}</button>
</form>
