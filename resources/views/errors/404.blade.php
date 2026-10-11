<!DOCTYPE html>
{{-- Halaman 404: tidak ditemukan (mandiri, aman untuk semua kondisi) --}}
<html lang="id" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 Tidak Ditemukan — PetaPenempatan ASN</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-base-200 flex items-center justify-center p-6">
    <div class="text-center max-w-md">
        <div class="text-7xl mb-4">🔍</div>
        <h1 class="text-5xl font-extrabold mb-2">404</h1>
        <h2 class="text-xl font-semibold mb-2">Halaman Tidak Ditemukan</h2>
        <p class="opacity-70 mb-6">
            Alamat yang Anda tuju tidak ada atau sudah dipindahkan.
        </p>
        <div class="flex gap-2 justify-center">
            <a href="{{ url('/') }}" class="btn btn-primary">🏠 Ke Beranda</a>
            <button onclick="history.back()" class="btn btn-ghost">← Kembali</button>
        </div>
    </div>
</body>
</html>
