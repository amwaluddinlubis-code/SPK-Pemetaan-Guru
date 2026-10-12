{{-- Alert umum: sukses/error/warning/info.
     Pakai: <x-alert tipe="warning">...isi...</x-alert> --}}
@props(['tipe' => 'info'])
@php
$kelas = [
    'sukses' => 'alert-success',
    'error' => 'alert-error',
    'warning' => 'alert-warning',
    'info' => 'alert-info',
][$tipe] ?? 'alert-info';
@endphp
<div {{ $attributes->merge(['class' => 'alert '.$kelas]) }}>
    {{ $slot }}
</div>
