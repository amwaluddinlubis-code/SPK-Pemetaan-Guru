{{-- Kartu KPI baku (Redesign R2): label + ikon, angka besar, dan satu
     baris konteks yang MENJELASKAN angka (komposisi/perbandingan/status)
     — bukan pengulangan label. Nada memberi makna warna:
     netral | peringatan | bahaya | sukses. Bila $tautan diisi, seluruh
     kartu menjadi tautan. Konteks ditulis sebagai isi slot.
     Pakai: <x-kartu-kpi label="Total Guru" :nilai="$total" ikon="guru">
              552 dari 581 berdomisili terpetakan
            </x-kartu-kpi> --}}
@php
    $petaNada = [
        'netral' => ['nilai' => 'text-base-content', 'ikon' => 'bg-primary/10 text-primary', 'bingkai' => ''],
        'peringatan' => ['nilai' => 'text-warning', 'ikon' => 'bg-warning/15 text-warning', 'bingkai' => 'border-l-4 border-warning'],
        'bahaya' => ['nilai' => 'text-error', 'ikon' => 'bg-error/10 text-error', 'bingkai' => 'border-l-4 border-error'],
        'sukses' => ['nilai' => 'text-success', 'ikon' => 'bg-success/10 text-success', 'bingkai' => ''],
    ];
    $gayaNada = $petaNada[$nada ?? 'netral'] ?? $petaNada['netral'];
    $kelasKartuKpi = 'block bg-base-100 shadow rounded-box p-4 sm:p-5 flex-1 min-w-44 '.$gayaNada['bingkai'];
@endphp
@if (! empty($tautan ?? null))
    <a href="{{ $tautan }}" class="{{ $kelasKartuKpi }} transition hover:shadow-md hover:-translate-y-0.5">
@else
    <div class="{{ $kelasKartuKpi }}">
@endif
    <div class="flex items-start justify-between gap-2">
        <p class="text-sm font-medium opacity-70">{{ $label }}</p>
        @isset($ikon)
            <span class="rounded-lg p-1.5 {{ $gayaNada['ikon'] }}"><x-ikon :nama="$ikon" class="h-5 w-5" /></span>
        @endisset
    </div>
    <p class="text-3xl font-extrabold tracking-tight mt-1 {{ $gayaNada['nilai'] }}">{{ $nilai }}</p>
    <div class="text-xs opacity-70 mt-1 leading-relaxed">{{ $slot }}</div>
@if (! empty($tautan ?? null))
    </a>
@else
    </div>
@endif
