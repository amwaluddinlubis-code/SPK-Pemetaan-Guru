{{--
    Select desa/kelurahan terpencarian, opsi dimuat dari API wilayah mengikuti
    kecamatan terpilih. Harus berada di dalam elemen induk Alpine yang menyediakan:
      - petaKodeKecamatan: peta NAMA KECAMATAN (huruf besar) => kode Kemendagri
      - opsiDesa: array [{kode, nama}] hasil muatDesa()
      - muatDesa(namaKecamatan): memuat opsiDesa dari /wilayah/api/desa
    dan mendengarkan event 'searchable-select-change' dari x-searchable-select
    kecamatan. Nilai tetap teks bebas: pengguna boleh mengetik desa di luar daftar.
--}}
@props([
    'name' => 'desa_kelurahan',
    'selected' => '',
    'placeholder' => 'cth: Panyabungan Jae',
])

<div class="relative"
     x-data="{ open: false, nilai: @js((string) ($selected ?? '')) }"
     x-on:click.outside="open = false"
     x-on:keydown.escape="open = false">
    <input type="text" id="f-{{ $name }}" name="{{ $name }}" x-model="nilai"
           aria-label="Desa/Kelurahan"
           class="input w-full pr-8" placeholder="{{ $placeholder }}" maxlength="100"
           autocomplete="off"
           x-on:focus="if ((opsiDesa ?? []).length) open = true"
           x-on:input="open = (opsiDesa ?? []).length > 0">
    <span class="pointer-events-none absolute inset-y-0 right-2 flex items-center opacity-50 text-xs"
          x-show="(opsiDesa ?? []).length">▾</span>
    <ul x-show="open && (opsiDesa ?? []).length" style="display: none;"
        class="absolute z-40 mt-1 w-full max-h-64 overflow-auto rounded-box border border-base-300 bg-base-100 py-1 shadow-lg">
        <template x-for="d in (opsiDesa ?? []).filter(d => nilai.trim() === '' || d.nama.toLowerCase().includes(nilai.trim().toLowerCase())).slice(0, 100)" :key="d.kode">
            <li>
                <button type="button" class="block w-full px-3 py-2 text-left hover:bg-base-200"
                        x-on:click="nilai = d.nama; open = false; $dispatch('desa-terpilih', { kode: d.kode, nama: d.nama, lat: d.lat ?? null, lng: d.lng ?? null })"
                        x-text="d.nama"></button>
            </li>
        </template>
    </ul>
    <p class="text-xs opacity-60 mt-1" x-show="(opsiDesa ?? []).length" style="display: none;">
        Pilih dari daftar desa kecamatan terpilih, atau ketik bebas.
    </p>
</div>
