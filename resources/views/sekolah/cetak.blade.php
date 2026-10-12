<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Data Sekolah — PetaPenempatan ASN</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; color: #111; margin: 16px; }
        h1 { font-size: 16px; margin-bottom: 2px; }
        p.sub { font-size: 11px; color: #555; margin-top: 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 1px solid #888; padding: 4px 6px; text-align: left; }
        th { background: #eee; }
        .toolbar { text-align: center; margin-bottom: 12px; }
        .toolbar button { font-size: 14px; padding: 8px 20px; cursor: pointer; }
        @media print { .toolbar { display: none; } body { margin: 0; } }
    </style>
</head>
<body>
    <div class="toolbar"><button onclick="window.print()">🖨️ Cetak / Simpan PDF</button></div>
    <h1>Data Sekolah</h1>
    <p class="sub">Dicetak {{ now()->locale('id')->translatedFormat('d F Y H:i') }} — {{ $data->count() }} sekolah</p>
    <table>
        <thead>
            <tr><th>No</th><th>NPSN</th><th>Nama</th><th>Kecamatan</th><th>Kategori Daerah</th><th>Kebutuhan Guru</th></tr>
        </thead>
        <tbody>
            @foreach ($data as $i => $s)
                <tr>
                    <td>{{ $i + 1 }}</td><td>{{ $s->npsn }}</td><td>{{ $s->nama }}</td>
                    <td>{{ $s->kecamatan }}</td><td>{{ $s->labelKategoriDaerah() }}</td>
                    <td>{{ $s->kebutuhan->map(fn ($k) => $k->mapel.' ('.$k->jumlah.')')->join(', ') ?: '—' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
