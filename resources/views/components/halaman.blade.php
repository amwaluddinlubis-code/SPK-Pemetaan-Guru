{{-- Header halaman baku (Redesign R1): ikon menu + judul + deskripsi
     opsional + tombol aksi kanan. Ikon memakai nama ikon yang sama
     dengan menu bilah sisi (lihat komponen ikon), BUKAN emoji.
     Pakai: <x-halaman judul="Data Sekolah" ikon="sekolah">
              <x-slot:deskripsi>...</x-slot:deskripsi>
              <x-slot:aksi>...tombol...</x-slot:aksi>
            </x-halaman> --}}
<div class="flex flex-wrap justify-between items-center gap-2 mb-6">
    <div class="min-w-0">
        <h1 class="text-2xl font-bold tracking-tight flex items-center gap-2.5">
            @isset($ikon)<x-ikon :nama="$ikon" class="h-7 w-7 shrink-0 text-primary" />@endisset
            <span>{{ $judul }}</span>
        </h1>
        @isset($deskripsi)
            <p class="text-sm opacity-70 mt-1">{{ $deskripsi }}</p>
        @endisset
    </div>
    @isset($aksi)
        <div class="flex flex-wrap gap-2">{{ $aksi }}</div>
    @endisset
</div>
