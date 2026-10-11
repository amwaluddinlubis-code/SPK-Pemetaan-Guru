<!DOCTYPE html>
{{-- Halaman 403: akses ditolak (mandiri, aman untuk semua kondisi) --}}
<html lang="id" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 Akses Ditolak — PetaPenempatan ASN</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-base-200 flex items-center justify-center p-6">
    <div class="text-center max-w-md">
        <div class="text-7xl mb-4">🚫</div>
        <h1 class="text-5xl font-extrabold mb-2">403</h1>
        <h2 class="text-xl font-semibold mb-2">Akses Ditolak</h2>
        <p class="opacity-70 mb-6">
            Maaf, akun Anda tidak diizinkan membuka halaman ini.
        </p>
        <div class="flex gap-2 justify-center">
            <a href="{{ url('/') }}" class="btn btn-primary">🏠 Ke Beranda</a>
            <button onclick="history.back()" class="btn btn-ghost">← Kembali</button>
        </div>
    </div>
</body>
</html>
