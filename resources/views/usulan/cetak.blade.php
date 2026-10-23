<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Usulan Penempatan — PetaPenempatan ASN</title>
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
    <div class="toolbar"><button onclick="window.print()">Cetak / Simpan PDF</button></div>
    @include('komponen.kop-surat')
    <h1>Usulan Penempatan Guru</h1>
    <p class="sub">Dicetak {{ now()->locale('id')->translatedFormat('d F Y H:i') }} — {{ $data->count() }} usulan</p>
    <table>
        <thead>
            <tr><th>No</th><th>Tanggal</th><th>Guru</th><th>Sekolah Tujuan</th><th>Alasan</th><th>Status</th></tr>
        </thead>
        <tbody>
            @foreach ($data as $i => $u)
                <tr>
                    <td>{{ $i + 1 }}</td><td>{{ $u->created_at?->format('d-m-Y') }}</td>
                    <td>{{ $u->guru?->nama }} ({{ $u->guru?->nipTersamar() }})</td>
                    <td>{{ $u->sekolah?->nama }}</td><td>{{ $u->alasan }}</td>
                    <td>{{ $u->labelStatus() }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
