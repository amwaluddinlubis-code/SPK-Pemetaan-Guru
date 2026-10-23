{{--
    Lencana status linieritas penempatan (Kepmen 222/O/2025, fase 1:
    peringatan informatif). Pemakaian:
        <x-lencana-linieritas :guru="$h->guru" :jenjang="$h->sekolahTujuan->jenjang" :mapel="$h->mapel" />
    Tidak merender apa pun bila status tanpa label (guru belum bersertifikat).
--}}
@props(['guru', 'jenjang' => null, 'mapel'])
@php
    $statusLinier = ($guru && $mapel)
        ? \App\Services\Linieritas::statusUntuk($guru, $jenjang, (string) $mapel)
        : ['status' => 'tanpa_label', 'label' => null, 'kelas' => null];
@endphp
@if ($statusLinier['label'])
    <span class="badge {{ $statusLinier['kelas'] }} badge-sm whitespace-normal text-left"
          title="Kesesuaian bidang studi sertifikat pendidik dengan bidang tugas tujuan (Kepmen 222/O/2025). Hanya peringatan — tidak menghalangi penetapan.">
        {{ $statusLinier['label'] }}
    </span>
@endif
