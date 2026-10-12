<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Data Guru — PetaPenempatan ASN</title>
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
    <h1>Data Guru</h1>
    <p class="sub">Dicetak {{ now()->locale('id')->translatedFormat('d F Y H:i') }} — {{ $data->count() }} guru</p>
    <table>
        <thead>
            <tr><th>No</th><th>NIP</th><th>Nama</th><th>Mapel</th><th>Jenjang</th><th>Kepegawaian</th><th>Domisili</th><th>Sekolah Saat Ini</th></tr>
        </thead>
        <tbody>
            @foreach ($data as $i => $g)
                <tr>
                    <td>{{ $i + 1 }}</td><td>{{ $g->nipTersamar() }}</td><td>{{ $g->nama }}</td>
                    <td>{{ $g->mapel }}</td><td>{{ $g->jenjang }}</td>
                    <td>{{ $g->labelStatusKepegawaian() }}</td>
                    <td>{{ $g->domisili_alamat }}</td><td>{{ $g->sekolah_saat_ini }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
