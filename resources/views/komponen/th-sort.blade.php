{{-- Header kolom tabel yang bisa diklik untuk sorting.
     Pakai: @include('komponen.th-sort', ['kolom' => 'nama', 'label' => 'Nama'])
     Query: ?sort=nama&arah=asc|desc (dipertahankan bersama filter lain) --}}
@php
    $sortAktif = request('sort', 'nama');
    $arahAktif = request('arah', 'asc');
    $apakahAktif = $sortAktif === $kolom;
    $arahBaru = ($apakahAktif && $arahAktif === 'asc') ? 'desc' : 'asc';
@endphp
<th>
    <a href="{{ request()->fullUrlWithQuery(['sort' => $kolom, 'arah' => $arahBaru]) }}"
       class="inline-flex items-center gap-1 hover:text-primary">
        {{ $label }}
        @if ($apakahAktif)
            <span class="text-xs">{{ $arahAktif === 'asc' ? '▲' : '▼' }}</span>
        @else
            <span class="text-xs opacity-30">⇅</span>
        @endif
    </a>
</th>
