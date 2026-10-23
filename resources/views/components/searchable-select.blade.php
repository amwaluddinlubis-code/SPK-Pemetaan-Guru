{{--
    Select dengan pencarian (combobox Alpine) — pengganti <select> biasa untuk
    daftar panjang (wilayah, sekolah, kecamatan).

    Pemakaian form biasa:
        <x-searchable-select name="sekolah_id" :options="$opsiSekolah"
            :selected="old('sekolah_id', $guru->sekolah_id)"
            placeholder="— Pilih Sekolah —" />

    Pemakaian Livewire (filter):
        <x-searchable-select :options="$opsiProvinsi" :selected="$provinsi"
            wire-model="provinsi" placeholder="— Semua Provinsi —" />

    $options: array [['value' => .., 'label' => ..], ...].
    Memilih opsi memicu CustomEvent 'searchable-select-change' (detail: name, value)
    yang menggelembung ke elemen induk — dipakai mis. form sekolah untuk memuat
    daftar desa mengikuti kecamatan terpilih.
--}}
@props([
    'options' => [],
    'selected' => '',
    'name' => null,
    'wireModel' => null,
    'placeholder' => '— Pilih —',
    'disabled' => false,
    'class' => '',
])

@php
    $opsi = collect($options)
        ->map(fn ($o) => ['value' => (string) ($o['value'] ?? ''), 'label' => (string) ($o['label'] ?? '')])
        ->values()
        ->all();
    $nilaiAwal = (string) ($selected ?? '');
    $labelAwal = collect($opsi)->firstWhere('value', $nilaiAwal)['label'] ?? '';
    // Id stabil untuk pasangan label form (label for="f-<name>") + nama aksesibel.
    $idKotak = $name ? 'f-'.$name : ($wireModel ? 'f-'.str_replace('.', '-', $wireModel) : null);
@endphp

<div class="relative {{ $class }}"
     x-data="{
        open: false,
        query: '',
        value: @js($nilaiAwal),
        label: @js($labelAwal),
        options: @js($opsi),
        sorot: 0,
        get tersaring() {
            const q = this.query.trim().toLowerCase();
            const semua = q === ''
                ? this.options
                : this.options.filter(o => o.label.toLowerCase().includes(q));
            return semua.slice(0, 100);
        },
        buka() {
            if ({{ $disabled ? 'true' : 'false' }}) return;
            this.query = '';
            this.sorot = 0;
            this.open = true;
            this.$nextTick(() => this.$refs.kotak.select());
        },
        tutup() { this.open = false; this.query = ''; },
        pilih(o) {
            this.value = o.value;
            this.label = o.label;
            this.tutup();
            this.kirim();
        },
        kosongkan() {
            this.value = '';
            this.label = '';
            this.tutup();
            this.kirim();
        },
        kirim() {
            this.$refs.tersembunyi.value = this.value;
            this.$refs.tersembunyi.dispatchEvent(new Event('input', { bubbles: true }));
            this.$refs.tersembunyi.dispatchEvent(new Event('change', { bubbles: true }));
            @if ($wireModel)
                if (this.$wire) this.$wire.set(@js($wireModel), this.value);
            @endif
            this.$el.dispatchEvent(new CustomEvent('searchable-select-change', {
                bubbles: true,
                detail: { name: @js($name ?? $wireModel), value: this.value, label: this.label },
            }));
        },
        navigasi(arah) {
            const n = this.tersaring.length;
            if (n === 0) return;
            this.sorot = (this.sorot + arah + n) % n;
            this.$nextTick(() => {
                this.$refs.daftar?.children[this.sorot]?.scrollIntoView({ block: 'nearest' });
            });
        },
     }"
     x-on:click.outside="tutup()"
     x-on:keydown.escape="tutup()">
    <input type="hidden" x-ref="tersembunyi"
           @if ($name) name="{{ $name }}" @endif
           @if ($wireModel) wire:model="{{ $wireModel }}" @endif
           value="{{ $nilaiAwal }}">
    <div class="relative">
        <input type="text" x-ref="kotak"
               @if ($idKotak) id="{{ $idKotak }}" @endif
               aria-label="{{ $placeholder }}"
               class="input w-full pr-14"
               role="combobox" aria-expanded="open"
               value="{{ $labelAwal }}"
               x-bind:value="open ? query : label"
               x-on:input="query = $event.target.value; sorot = 0"
               x-on:focus="buka()"
               x-on:click="buka()"
               x-on:keydown.arrow-down.prevent="open ? navigasi(1) : buka()"
               x-on:keydown.arrow-up.prevent="navigasi(-1)"
               x-on:keydown.enter.prevent="if (open && tersaring[sorot]) pilih(tersaring[sorot])"
               placeholder="{{ $placeholder }}"
               @if ($disabled) disabled @endif
               autocomplete="off">
        <span class="absolute inset-y-0 right-2 flex items-center gap-1">
            <button type="button" tabindex="-1" x-show="value !== '' && !open"
                    x-on:click.stop="kosongkan()" title="Kosongkan pilihan"
                    class="btn btn-ghost btn-xs btn-circle" @if ($disabled) disabled @endif>✕</button>
            <span class="pointer-events-none opacity-50 text-xs">▾</span>
        </span>
    </div>
    <ul x-ref="daftar" x-show="open" style="display: none;"
        class="absolute z-40 mt-1 w-full max-h-64 overflow-auto rounded-box border border-base-300 bg-base-100 py-1 shadow-lg">
        <li x-show="value !== ''">
            <button type="button" class="block w-full px-3 py-2 text-left italic opacity-70 hover:bg-base-200"
                    x-on:click="kosongkan()">{{ $placeholder }}</button>
        </li>
        <template x-for="(o, i) in tersaring" :key="o.value + '|' + o.label">
            <li>
                <button type="button" class="block w-full px-3 py-2 text-left"
                        x-bind:class="i === sorot ? 'bg-base-200' : ''"
                        x-on:mouseenter="sorot = i"
                        x-on:click="pilih(o)"
                        x-text="o.label"></button>
            </li>
        </template>
        <li x-show="tersaring.length === 0" class="px-3 py-2 opacity-60">Tidak ada yang cocok.</li>
        <li x-show="options.length > 100 && query.trim() === ''" class="px-3 py-2 text-xs opacity-50">
            Menampilkan 100 pertama — ketik untuk mempersempit.
        </li>
    </ul>
</div>
