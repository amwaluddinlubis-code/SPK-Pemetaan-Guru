
<div>
<x-halaman judul="Proyeksi Kebutuhan Guru 1–5 Tahun" ikon="proyeksi" />

@if (! $dataCukup)
    <div class="alert alert-warning mb-4">
        <span class="text-sm">⚠️ <strong>Data belum cukup untuk membuat proyeksi.</strong> Aplikasi saat ini hanya menyimpan snapshot kondisi terkini tanpa deret waktu historis. Kami tidak mengarang angka proyeksi.</span>
    </div>

    <div class="card bg-base-100 shadow p-6 mb-4">
        <h2 class="font-bold text-lg mb-3">Syarat Data untuk Proyeksi</h2>
        <ul class="list-disc list-inside text-sm space-y-1 opacity-80">
            @foreach ($syarat as $s)
                <li>{{ $s }}</li>
            @endforeach
        </ul>
    </div>

    <div class="card bg-base-100 shadow p-6">
        <h2 class="font-bold text-lg mb-3">Metodologi yang Akan Dipakai</h2>
        <ul class="list-disc list-inside text-sm space-y-1 opacity-80">
            @foreach ($metodologi as $m)
                <li>{{ $m }}</li>
            @endforeach
        </ul>
        <p class="text-xs opacity-60 mt-4">Jejak audit operasional (<a href="{{ route('aktivitas.index') }}" class="link">/aktivitas</a>) mulai mencatat perubahan data — setelah terkumpul beberapa periode, data ini dapat menjadi dasar deret waktu.</p>
    </div>
@else
    <div class="alert alert-info">Data historis tersedia — proyeksi dapat dihitung. (Belum diimplementasikan.)</div>
@endif

</div>
