{{-- Pemilih jumlah baris per halaman (DaisyUI). Dipakai di dalam view komponen
     Livewire tabel yang memakai trait DenganPerHalaman. Daftar opsi di sini
     harus sama dengan DenganPerHalaman::OPSI_PER_HALAMAN (validasi server). --}}
<label class="flex items-center gap-2 text-sm">
    <span class="opacity-70">Baris per halaman:</span>
    <select wire:model.live="perPage" class="select select-sm w-24" aria-label="Baris per halaman">
        @foreach ([10, 15, 20, 25, 50, 100] as $opsi)
            <option value="{{ $opsi }}">{{ $opsi }}</option>
        @endforeach
    </select>
</label>
