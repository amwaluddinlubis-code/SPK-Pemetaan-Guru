{{-- Daftar error validasi (semua error, bukan hanya pertama).
     Pakai: <x-galat /> di atas form. --}}
@if ($errors->any())
    <div class="alert alert-error {{ $attributes->get('class', 'mb-4') }}">
        <ul class="list-disc list-inside text-sm">
            @foreach ($errors->all() as $pesan)
                <li>{{ $pesan }}</li>
            @endforeach
        </ul>
    </div>
@endif
