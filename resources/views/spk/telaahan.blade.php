<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Telaahan Staf — {{ $guru->nama }}</title>
    <style>
        body { font-family: "Times New Roman", Times, serif; color: #111; margin: 0; padding: 24px; }
        .kertas { max-width: 720px; margin: 0 auto; }
        p, li { line-height: 1.55; }
        .saran { text-align: justify; }
        .judul { text-align: center; margin: 20px 0; }
        .judul h3 { font-size: 15px; margin: 0; text-decoration: underline; text-transform: uppercase; }
        .judul p { font-size: 13px; margin: 4px 0; }
        table.data { width: 100%; border-collapse: collapse; font-size: 13px; margin: 8px 0 16px; }
        table.data th, table.data td { border: 1px solid #555; padding: 6px 8px; text-align: left; vertical-align: top; }
        table.data th { background: #eee; width: 38%; }
        h4 { font-size: 14px; margin: 18px 0 6px; }
        ol, ul { font-size: 13px; }
        li { margin-bottom: 4px; }
        .ttd { display: flex; justify-content: space-between; margin-top: 40px; font-size: 13px; }
        .ttd div { text-align: center; width: 40%; }
        .ttd .nama { margin-top: 70px; text-decoration: underline; font-weight: bold; }
        .catatan { font-size: 12px; font-style: italic; color: #444; margin-top: 24px; border-top: 1px solid #999; padding-top: 8px; }
        .toolbar { text-align: center; margin-bottom: 16px; }
        .toolbar button { font-family: sans-serif; font-size: 14px; padding: 8px 20px; cursor: pointer; }
        @media print {
            .toolbar { display: none; }
            body { padding: 0; }
            .ttd { page-break-inside: avoid; }
            @page { size: A4; margin: 18mm 16mm; }
        }
    </style>
</head>
<body>
<div class="kertas">
    <div class="toolbar">
        <button onclick="window.print()">Cetak / Simpan PDF</button>
    </div>

    @include('komponen.kop-surat')

    <div class="judul">
        <h3>Telaahan Staf</h3>
        <p>Nomor: .../TU-Dik/{{ date('m') }}/{{ date('Y') }}</p>
        <p>Perihal: Usulan Penempatan Guru {{ $guru->mapel }}</p>
    </div>

    <h4>I. Dasar</h4>
    <ol>
        <li>Undang-Undang Nomor 20 Tahun 2023 tentang Aparatur Sipil Negara.</li>
        <li>Peraturan Pemerintah Nomor 11 Tahun 2017 jo. Nomor 17 Tahun 2020 tentang Manajemen PNS.</li>
        <li>Undang-Undang Nomor 14 Tahun 2005 tentang Guru dan Dosen (linearitas sertifikasi).</li>
        <li>Hasil analisis kebutuhan guru dan rekomendasi SPK sistem informasi pemetaan penempatan guru.</li>
    </ol>

    <h4>II. Data Guru</h4>
    <table class="data">
        <tr><th>Nama / NIP</th><td>{{ $guru->nama }} / {{ $guru->nip }}</td></tr>
        <tr><th>Status Kepegawaian</th><td>{{ $guru->labelStatusKepegawaian() }}</td></tr>
        <tr><th>Sertifikasi</th><td>
            {{ $guru->sudah_sertifikasi ? 'Sudah sertifikasi ('.($guru->mapel_sertifikasi ?? '—').')' : 'Belum sertifikasi' }}
            @if ($guru->sudah_sertifikasi)
                — {{ $guru->linear() ? 'linear dengan mapel yang diampu' : 'TIDAK LINEAR dengan mapel yang diampu' }}
            @endif
        </td></tr>
        @php
            $statusKepmen = \App\Services\Linieritas::statusUntuk($guru, $spk->jenjangLinieritas() ?? $guru->jenjang, $spk->mapelTujuan());
        @endphp
        <tr><th>Linieritas Penempatan (Kepmen 222/O/2025)</th><td>
            @if ($guru->bidang_studi_serdik)
                Bidang studi serdik: {{ $guru->bidang_studi_serdik }}{{ $guru->kode_bidang_studi ? ' (kode '.$guru->kode_bidang_studi.')' : '' }} —
            @endif
            {{ $statusKepmen['label'] ?? 'Tidak dinilai (guru belum bersertifikat pendidik)' }}
        </td></tr>
        <tr><th>Status Pernikahan</th><td>{{ $guru->labelStatusPernikahan() }}{{ $guru->berkeluarga() ? ' (prioritas keluarga)' : '' }}</td></tr>
        <tr><th>Mapel / Jenjang</th><td>{{ $guru->mapel }} / {{ $guru->jenjang }}</td></tr>
        <tr><th>Sekolah Asal</th><td>{{ $guru->sekolah->nama ?? $guru->sekolah_saat_ini ?? '—' }}</td></tr>
        <tr><th>Sekolah Tujuan</th><td>{{ $spk->sekolahTujuan->nama ?? '—' }}</td></tr>
    </table>

    <h4>III. Analisis</h4>
    <ol>
        <li><strong>Kebutuhan formasi:</strong> sekolah tujuan membutuhkan guru {{ $spk->mapelTujuan() }} (rekomendasi sistem)@if ($spk->tingkatLinieritas() === 2) — diisi lewat <strong>linier kelompok</strong> dari bidang studi sertifikasi guru (mapel guru saat ini: {{ $guru->mapel }})@endif.</li>
        @php $pbTelaah = \App\Services\PerkiraanBebanKerja::untukRekomendasi($spk); @endphp
        @if ($pbTelaah)
            <li><strong>Perkiraan beban mengajar:</strong> ±{{ number_format($pbTelaah['jp'], 1, ',', '.') }} JP/minggu di sekolah tujuan
                (total {{ $pbTelaah['total_jp'] }} JP mapel dibagi {{ $pbTelaah['pembagi'] }} guru) —
                {{ $pbTelaah['cukup'] ? 'mencukupi syarat beban kerja penuh (≥ 24 JP).' : 'DI BAWAH 24 JP: beban kerja penuh terancam dan tunjangan profesi guru berisiko tidak terpenuhi.' }}</li>
        @endif
        @if ($spk->tujuanDaerahKhusus())
            <li><strong>Tunjangan Khusus:</strong> sekolah tujuan berkategori daerah khusus ({{ str_replace('_', ' ', $spk->sekolahTujuan->kategori_daerah) }});
                penempatan ini berhak atas Tunjangan Khusus sebesar 1× gaji pokok per bulan (Permendikdasmen 10/2026 Pasal 8–10).</li>
        @endif
        <li><strong>Skor peringkat hibrida (Greedy + SAW–TOPSIS):</strong>
            @if ($spk->sudahDinilai())
                <strong>{{ number_format($spk->skorPersen(), 1, ',', '.') }}/100</strong>
                (SAW {{ number_format($spk->skor_saw * 100, 1, ',', '.') }} · TOPSIS {{ number_format($spk->skor_topsis * 100, 1, ',', '.') }}) —
                peringkat ke-{{ data_get($spk->detail_skor, 'peringkat_ke', '—') }} di antara alternatif layak bagi guru ini.
                Kriteria saat penilaian: penghematan {{ number_format((float) data_get($spk->detail_skor, 'kriteria.hemat_km', $spk->hematKm()), 1, ',', '.') }} km (bobot 40%),
                jarak tujuan {{ number_format((float) data_get($spk->detail_skor, 'kriteria.jarak_tujuan_km', $spk->jarak_km), 1, ',', '.') }} km (25%),
                sisa formasi {{ (int) data_get($spk->detail_skor, 'kriteria.sisa_formasi', 0) }} (25%),
                prioritas daerah tujuan {{ $spk->sekolahTujuan->labelKategoriDaerah() }} (10%).
            @else
                belum tersedia — rekomendasi ini dibuat sebelum metode SAW–TOPSIS diterapkan.
            @endif
        </li>
        <li><strong>Jarak domisili:</strong> saat ini {{ number_format($spk->jarak_sekarang_km, 1, ',', '.') }} km (garis lurus),
            menjadi {{ number_format($spk->jarak_km, 1, ',', '.') }} km — penghematan
            {{ number_format($spk->hematKm(), 1, ',', '.') }} km.</li>
        <li><strong>Dampak ke sekolah asal:</strong>
            @if ($dampakAsal)
                <span style="color:#b45309;">{{ $dampakAsal }}</span>
            @else
                tidak teridentifikasi kekurangan kritis di sekolah asal.
            @endif
        </li>
        <li><strong>Kepegawaian:</strong> berstatus {{ $guru->labelStatusKepegawaian() }}.
            @if (strtolower($guru->status_kepegawaian) === 'pppk')
                Mutasi PPPK agar memperhatikan perjanjian kerja dengan instansi dan ketentuan PPK.
            @endif
        </li>
        <li><strong>Linearitas:</strong>
            @if (! $guru->linear())
                <span style="color:#b91c1c;">sertifikat pendidik tidak linear dengan mapel yang diampu — perlu pertimbangan khusus.</span>
            @else
                memenuhi ketentuan linearitas.
            @endif
        </li>
    </ol>

    <h4>IV. Saran</h4>
    <p class="saran" style="font-size:13px;">
        Berdasarkan analisis di atas, disarankan kepada Pejabat Pembina Kepegawaian untuk
        <strong>{{ $dampakAsal || ! $guru->linear() ? 'menunda/mengkaji ulang' : 'menyetujui' }}</strong>
        usulan penempatan {{ $guru->nama }} ke {{ $spk->sekolahTujuan->nama ?? 'sekolah tujuan' }}.
        @if ($dampakAsal || ! $guru->linear())
            Pertimbangan: terdapat catatan pada poin III yang perlu diselesaikan terlebih dahulu.
        @endif
    </p>

    <div class="ttd">
        <div>
            Panyabungan, {{ $tanggal }}<br>
            Kepala Bidang PTK
            <div class="nama">( ............................................ )</div>
            NIP. ............................................
        </div>
        <div>
            Mengetahui,<br>
            Kepala Dinas Pendidikan
            <div class="nama">( ............................................ )</div>
            NIP. ............................................
        </div>
    </div>

    <p class="catatan">Dokumen ini dihasilkan otomatis oleh Sistem Informasi Pemetaan Penempatan Guru ASN
        berdasarkan data dan rekomendasi SPK per {{ $tanggal }}. Jarak dinyatakan sebagai garis lurus
        (bukan jarak tempuh jalan).</p>
</div>
</body>
</html>
