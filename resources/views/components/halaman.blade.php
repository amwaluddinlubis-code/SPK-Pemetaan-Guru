{{-- Header halaman: judul + deskripsi opsional + tombol aksi kanan.
     Pakai: <x-halaman judul="🎯 SPK"><x-slot:deskripsi>...</x-slot:deskripsi><x-slot:aksi>...tombol...</x-slot:aksi></x-halaman> --}}
<div class="flex flex-wrap justify-between items-center gap-2 mb-4">
    <div>
        <h1 class="text-2xl font-bold">{{ $judul }}</h1>
        @isset($deskripsi)
            <p class="text-sm opacity-70 mt-1">{{ $deskripsi }}</p>
        @endisset
    </div>
    @isset($aksi)
        <div class="flex flex-wrap gap-2">{{ $aksi }}</div>
    @endisset
</div>
