<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>404 — Halaman Tidak Ditemukan — PetaPenempatan ASN</title>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css']); ?>
</head>
<body class="min-h-screen bg-base-200 flex items-center justify-center p-4">
    <div class="card w-full max-w-md bg-base-100 shadow-xl">
        <div class="card-body text-center">
            <div class="text-6xl mb-2">🗺️</div>
            <h1 class="card-title text-3xl justify-center">404 — Tidak Ditemukan</h1>
            <p class="opacity-70">Halaman yang Anda cari tidak ada atau sudah dipindahkan.
               Periksa kembali alamatnya.</p>
            <div class="card-actions justify-center mt-4">
                <a href="<?php echo e(route('dashboard')); ?>" class="btn btn-primary">← Kembali ke Dashboard</a>
            </div>
        </div>
    </div>
</body>
</html>
<?php /**PATH D:\lrvProject\SPK-Pemetaan-Guru\resources\views/errors/404.blade.php ENDPATH**/ ?>