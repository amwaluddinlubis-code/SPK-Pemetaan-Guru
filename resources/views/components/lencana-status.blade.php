{{-- Lencana status baku (Redesign R3): warna = makna, dipetakan tetap:
     sukses (selesai/terverifikasi), peringatan (menunggu/perhatian),
     bahaya (ditolak/gagal), info (proses), netral (lainnya).
     Pakai: <x-lencana-status nada="sukses" teks="Terverifikasi" /> --}}
@props(['nada' => 'netral', 'teks' => null, 'judul' => null])
@php
    $kelasNada = [
        'sukses' => 'badge-success',
        'peringatan' => 'badge-warning',
        'bahaya' => 'badge-error',
        'info' => 'badge-info',
        'utama' => 'badge-primary',
        'gelap' => 'badge-neutral',
        'netral' => 'badge-ghost',
    ][$nada] ?? 'badge-ghost';
@endphp
<span class="badge {{ $kelasNada }} badge-sm whitespace-nowrap" @if ($judul) title="{{ $judul }}" @endif>{{ $teks ?? $slot }}</span>
