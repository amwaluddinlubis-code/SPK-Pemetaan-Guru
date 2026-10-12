{{-- Kartu konten standar.
     Pakai: <x-kartu class="mb-4">...isi...</x-kartu> --}}
@props(['class' => ''])
<div {{ $attributes->merge(['class' => 'card bg-base-100 shadow-xl '.$class]) }}>
    {{ $slot }}
</div>
