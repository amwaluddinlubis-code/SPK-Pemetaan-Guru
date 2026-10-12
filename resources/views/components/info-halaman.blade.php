{{-- Info pagination: "Menampilkan 1–15 dari 552 data".
     Pakai: <x-info-halaman :data="$daftarGuru" /> --}}
@props(['data'])
<p class="text-sm opacity-70">
    Menampilkan {{ $data->firstItem() ?? 0 }}–{{ $data->lastItem() ?? 0 }}
    dari {{ $data->total() }} data
</p>
