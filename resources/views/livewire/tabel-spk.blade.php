{{-- Tabel SPK Penempatan (Livewire): filter status + pagination tanpa reload. --}}
<div>
<x-halaman judul="SPK Penempatan" ikon="spk">
    <x-slot:deskripsi>
        Rekomendasi pengisian formasi: guru dengan jarak tugas &gt; {{ \App\Http\Controllers\SpkController::AMBANG_KM }} km
        dipasangkan ke sekolah yang kekurangan mapelnya. Kelayakan disaring secara greedy,
        lalu alternatif diperingkat dengan skor hibrida SAW + TOPSIS.
    </x-slot:deskripsi>
    <x-slot:aksi>
        {{-- Tombol generate: khusus administrator (kabid_ptk) --}}
        @if (auth()->user()->hasRole('kabid_ptk'))
            <form method="POST" action="{{ route('spk.generate') }}" class="tooltip tooltip-bottom" data-tip="Generate SPK"
                  data-konfirmasi data-judul="Generate SPK" data-pesan="Jalankan SPK untuk semua guru yang jaraknya melebihi {{ \App\Http\Controllers\SpkController::AMBANG_KM }} km?" data-tombol="Jalankan SPK" data-warna="btn-primary">
                @csrf
                <button type="submit" class="btn btn-sm btn-primary" aria-label="Generate SPK"><x-ikon nama="spk" class="h-4 w-4" /></button>
            </form>
        @endif
    </x-slot:aksi>
</x-halaman>

<div class="card bg-base-100 shadow mb-4">
    <div class="card-body py-3 text-sm">
      <p class="leading-relaxed">
        <strong><x-ikon nama="simulasi" class="h-5 w-5 inline align-[-3px]" /> Metode peringkat — Greedy + SAW–TOPSIS.</strong>
        Greedy menyaring kelayakan (jarak &gt; {{ \App\Http\Controllers\SpkController::AMBANG_KM }} km, tujuan lebih dekat,
        formasi tersedia, guru asal sekolah 3T dilindungi) dan mengalokasikan formasi tanpa over-booking.
        Alternatif yang lolos lalu dinilai SAW dan TOPSIS dengan kriteria:
        penghematan jarak <strong>40%</strong>, jarak tujuan <strong>25%</strong>, sisa formasi <strong>25%</strong>,
        prioritas daerah tujuan <strong>10%</strong>. Skor akhir = rata-rata SAW dan TOPSIS (0–100);
        rincian kriteria tersimpan pada setiap rekomendasi dan tercantum di telaahan staf.
      </p>
    </div>
</div>

<div>
    {{-- Pencarian teks --}}
    <div class="flex flex-wrap gap-2 mb-4">
        <input aria-label="Cari nama guru atau sekolah tujuan…" type="text" wire:model.live.debounce.500ms="q"
               placeholder="Cari nama guru atau sekolah tujuan…"
               class="input flex-1 min-w-52">
        @if ($q !== '' || $status !== '')
            <button wire:click="resetFilter" class="btn btn-ghost">Reset</button>
        @endif
    </div>

    {{-- Filter status --}}
    <div class="flex flex-wrap gap-2 mb-4">
        <button wire:click="$set('status', '')"
           class="badge {{ $status === '' ? 'badge-primary' : 'badge-ghost' }} badge-lg">Semua</button>
        @foreach (\App\Models\HasilSpk::STATUS as $kode => $label)
            <button wire:click="$set('status', '{{ $kode }}')"
               class="badge {{ $status === $kode ? 'badge-primary' : 'badge-ghost' }} badge-lg">
                {{ $label }}
            </button>
        @endforeach
    </div>

    <x-kartu class="overflow-x-auto">
        <table class="table table-zebra tabel-baku">
            <thead>
                <tr>
                    <th>Guru</th>
                    <th>Sekolah Tujuan</th>
                    <th>Jarak</th>
                    <th>Kelayakan</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($spks as $s)
                    <tr>
                        {{-- Guru: nama, mapel, sekolah asal + jarak saat ini --}}
                        <td>
                            <div class="font-semibold">{{ $s->guru->nama }}</div>
                            <div class="text-xs opacity-70">{{ $s->guru->mapel }}</div>
                            <div class="text-xs mt-1">
                                Asal: {{ $s->guru->sekolah->nama ?? '-' }}
                            </div>
                            <div class="text-xs opacity-70">
                                Jarak sekarang: {{ number_format($s->jarak_sekarang_km, 1, ',', '.') }} km (garis lurus)
                            </div>
                        </td>

                        {{-- Sekolah tujuan + jarak usulan (hybrid: jalan bila tersedia) --}}
                        <td>
                            <div class="font-semibold text-primary">{{ $s->sekolahTujuan->nama }}</div>
                            <div class="text-xs opacity-70">{{ $s->sekolahTujuan->kecamatan }}</div>
                            <div class="text-xs mt-1">
                                @php $jj = $jarakJalan[$s->id] ?? null; @endphp
                                @if ($jj && $jj['sumber'] === 'jalan')
                                    Jarak usulan: <strong>{{ number_format($jj['jarak_km'], 1, ',', '.') }} km</strong>
                                    <span class="badge badge-info badge-xs" title="Jarak tempuh jalan via routing{{ $jj['cached'] ? ' (cache)' : '' }}{{ $jj['waktu_menit'] ? ' · ±'.$jj['waktu_menit'].' mnt' : '' }}">jalan</span>
                                    <br><span class="opacity-60">{{ number_format($s->jarak_km, 1, ',', '.') }} km garis lurus</span>
                                @else
                                    Jarak usulan: {{ number_format($s->jarak_km, 1, ',', '.') }} km (garis lurus)
                                @endif
                            </div>
                        </td>

                        {{-- Penghematan + skor hibrida SAW-TOPSIS --}}
                        <td class="whitespace-nowrap">
                            <div class="font-bold text-success text-lg">
                                −{{ number_format($s->hematKm(), 1, ',', '.') }} km
                            </div>
                            <div class="text-xs opacity-70">lebih dekat</div>
                            <div class="text-xs opacity-50">(garis lurus)</div>
                            @if ($s->sudahDinilai())
                                <div class="mt-2 rounded bg-base-200 px-2 py-1">
                                    <div class="text-xs">Skor hibrida <strong>{{ number_format($s->skorPersen(), 1, ',', '.') }}/100</strong></div>
                                    <div class="text-xs opacity-70">
                                        SAW {{ number_format($s->skor_saw * 100, 1, ',', '.') }} · TOPSIS {{ number_format($s->skor_topsis * 100, 1, ',', '.') }}
                                    </div>
                                </div>
                            @else
                                <div class="mt-2 text-xs opacity-50">Skor hibrida belum tersedia<br>(rekomendasi lama)</div>
                            @endif
                        </td>

                        {{-- Kelayakan: penjelasan mengapa kandidat ini lolos (SPK-003) --}}
                        <td>
                            @php
                                $kunciKuota = $s->sekolah_tujuan_id . '|' . strtolower(trim($s->mapelTujuan()));
                                $sisa = $sisaKuota[$kunciKuota] ?? 0;
                            @endphp
                            <ul class="text-xs space-y-1 list-none p-0 m-0">
                                <li><x-ikon nama="centang" class="h-3.5 w-3.5 inline text-success align-[-2px]" /> Mapel <strong>{{ $s->mapelTujuan() }}</strong> dibutuhkan sekolah tujuan</li>
                                @if ($s->tingkatLinieritas() === 2)
                                    <li><x-ikon nama="swap" class="h-3.5 w-3.5 inline align-[-2px]" /> Mengisi formasi <strong>{{ $s->mapelTujuan() }}</strong> lewat linier kelompok
                                        (mapel guru: {{ $s->guru->mapel }}{{ $s->guru->bidang_studi_serdik ? '; serdik '.$s->guru->bidang_studi_serdik : '' }})</li>
                                @endif
                                @if (isset($perkiraanBeban[$s->id]))
                                    @php $pb = $perkiraanBeban[$s->id]; @endphp
                                    <li>
                                        @if ($pb['cukup'])
                                            <x-ikon nama="centang" class="h-3.5 w-3.5 inline text-success align-[-2px]" /> Perkiraan beban di tujuan ±{{ number_format($pb['jp'], 1, ',', '.') }} JP/minggu (mencukupi)
                                        @else
                                            <x-ikon nama="peringatan" class="h-3.5 w-3.5 inline text-warning align-[-2px]" /> Perkiraan beban di tujuan hanya ±{{ number_format($pb['jp'], 1, ',', '.') }} JP/minggu
                                            (&lt; 24) — beban kerja penuh terancam, tunjangan profesi berisiko
                                        @endif
                                    </li>
                                @endif
                                @if ($s->tujuanDaerahKhusus())
                                    <li><x-ikon nama="info" class="h-3.5 w-3.5 inline text-info align-[-2px]" /> Tujuan daerah khusus ({{ str_replace('_', ' ', $s->sekolahTujuan->kategori_daerah) }}) —
                                        penempatan ini berhak <strong>Tunjangan Khusus</strong> 1× gaji pokok/bulan</li>
                                @endif
                                <li>
                                    @if ($sisa > 0)
                                        <x-ikon nama="centang" class="h-3.5 w-3.5 inline text-success align-[-2px]" /> Sisa formasi: <strong>{{ $sisa }}</strong>
                                    @else
                                        <x-ikon nama="peringatan" class="h-3.5 w-3.5 inline text-warning align-[-2px]" /> Sisa formasi: <strong>0</strong> (penuh)
                                    @endif
                                </li>
                                <li>
                                    <x-ikon nama="centang" class="h-3.5 w-3.5 inline text-success align-[-2px]" /> Menghemat {{ number_format($s->hematKm(), 1, ',', '.') }} km
                                    ({{ number_format($s->jarak_sekarang_km, 1, ',', '.') }} → {{ number_format($s->jarak_km, 1, ',', '.') }} km),
                                    di atas ambang {{ $ambangKm }} km
                                </li>
                                <li>
                                    @if ($s->guru->berkeluarga())
                                        {{ $s->guru->labelStatusPernikahan() }} — prioritas keluarga
                                    @else
                                        {{ $s->guru->labelStatusPernikahan() }}
                                    @endif
                                </li>
                                <li>
                                    <span class="badge badge-neutral badge-xs">{{ $s->guru->labelStatusKepegawaian() }}</span>
                                    @if ($s->guru->sudah_sertifikasi)
                                        <span class="badge {{ $s->guru->linear() ? 'badge-success' : 'badge-error' }} badge-xs">
                                            Serdik{{ $s->guru->linear() ? '' : ' tidak linear' }}
                                        </span>
                                        <x-lencana-linieritas :guru="$s->guru" :jenjang="$s->jenjangLinieritas()" :mapel="$s->mapelTujuan()" />
                                    @endif
                                </li>
                                @if (! empty($dampakAsal[$s->id]))
                                    <li class="text-warning"><x-ikon nama="peringatan" class="h-3.5 w-3.5 inline text-warning align-[-2px]" /> {{ $dampakAsal[$s->id] }}</li>
                                @endif
                            </ul>
                        </td>

                        {{-- Status --}}
                        <td>
                            <span class="badge {{ $s->badgeStatus() }}">{{ $s->labelStatus() }}</span>
                            @if ($s->diprosesOleh)
                                <div class="text-xs opacity-70 mt-1">oleh {{ $s->diprosesOleh->name }}</div>
                            @endif
                            @if ($s->catatan_admin)
                                <div class="text-xs opacity-70 italic">"{{ $s->catatan_admin }}"</div>
                            @endif
                        </td>

                        {{-- Aksi: hanya untuk yang masih berstatus rekomendasi --}}
                        <td class="whitespace-nowrap">
                            <div class="grup-aksi">
                            @if (auth()->user()->hasRole('kabid_ptk', 'kadis'))
                                <a href="{{ route('spk.telaahan', $s) }}" target="_blank"
                                   class="btn btn-info btn-sm tooltip tooltip-left" data-tip="Telaahan — cetak telaahan staf untuk PPK" aria-label="Telaahan — cetak telaahan staf untuk PPK"><x-ikon nama="usulan" class="h-4 w-4" /></a>
                            @endif
                            @if ($s->status === 'rekomendasi')
                                @if (auth()->user()->hasRole('kadis'))
                                    <form method="POST" action="{{ route('spk.setujui', $s) }}" class="inline tooltip tooltip-left" data-tip="Setujui penempatan"
                                          data-konfirmasi data-judul="Setujui Penempatan" data-pesan="Setujui penempatan ini? Guru akan langsung dimutasi ke sekolah tujuan." data-tombol="Setujui" data-warna="btn-success">
                                        @csrf
                                        <button type="submit" class="btn btn-success btn-sm" aria-label="Setujui penempatan"><x-ikon nama="centang" class="h-4 w-4" /></button>
                                    </form>
                                @endif
                                @if (auth()->user()->hasRole('kabid_ptk') || auth()->user()->hasRole('kadis'))
                                    <form method="POST" action="{{ route('spk.tolak', $s) }}" class="inline tooltip tooltip-left" data-tip="Tolak rekomendasi"
                                          data-konfirmasi data-alasan="catatan_admin" data-alasan-label="Alasan penolakan" data-judul="Tolak Rekomendasi" data-pesan="Rekomendasi ditolak dan reservasi formasinya terlepas. Alasan tercatat pada rekomendasi." data-tombol="Tolak" data-warna="btn-error">
                                        @csrf
                                        <input type="hidden" name="catatan_admin" value="">
                                        <button type="submit" class="btn btn-error btn-sm" aria-label="Tolak rekomendasi"><x-ikon nama="silang" class="h-4 w-4" /></button>
                                    </form>
                                @endif
                            @endif
                            </div>
                            @if ($s->status !== 'rekomendasi')
                                <span class="text-xs opacity-50">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    @php $pesanKosongSpk = 'Belum ada rekomendasi SPK.'.(auth()->user()->hasRole('kabid_ptk') ? ' Klik Generate SPK untuk mencari penempatan.' : ''); @endphp
                    <x-kosong :pesan="$pesanKosongSpk" kolom="6" />
                @endforelse
            </tbody>
        </table>
    </x-kartu>

    <div class="mt-4 flex flex-wrap items-center justify-between gap-2">
        <x-info-halaman :data="$spks" />
        <div class="flex flex-wrap items-center gap-3">
            {{ $spks->links() }}
            <x-per-halaman />
        </div>
    </div>
</div>

</div>
