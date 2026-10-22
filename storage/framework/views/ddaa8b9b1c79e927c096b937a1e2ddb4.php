<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>403 — Akses Ditolak — PetaPenempatan ASN</title>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css']); ?>
</head>
<body class="min-h-screen bg-base-200 flex items-center justify-center p-4">
    <div class="card w-full max-w-md bg-base-100 shadow-xl">
        <div class="card-body text-center">
            <div class="text-6xl mb-2">🚫</div>
            <h1 class="card-title text-3xl justify-center">403 — Akses Ditolak</h1>
            <p class="opacity-70">Maaf, peran akun Anda tidak diizinkan membuka halaman ini.
               Jika Anda merasa ini keliru, hubungi administrator (Kabid PTK).</p>
            <div class="card-actions justify-center mt-4">
                <a href="<?php echo e(route('dashboard')); ?>" class="btn btn-primary">← Kembali ke Dashboard</a>
            </div>
        </div>
    </div>
</body>
</html>
<?php /**PATH D:\lrvProject\SPK-Pemetaan-Guru\resources\views/errors/403.blade.php ENDPATH**/ ?>