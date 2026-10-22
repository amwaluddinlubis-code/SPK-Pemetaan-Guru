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
    <p class="sub">Dicetak <?php echo e(now()->locale('id')->translatedFormat('d F Y H:i')); ?> — <?php echo e($data->count()); ?> guru</p>
    <table>
        <thead>
            <tr><th>No</th><th>NIP</th><th>Nama</th><th>Mapel</th><th>Jenjang</th><th>Kepegawaian</th><th>Domisili</th><th>Sekolah Saat Ini</th></tr>
        </thead>
        <tbody>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $data; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $g): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <tr>
                    <td><?php echo e($i + 1); ?></td><td><?php echo e($g->nipTersamar()); ?></td><td><?php echo e($g->nama); ?></td>
                    <td><?php echo e($g->mapel); ?></td><td><?php echo e($g->jenjang); ?></td>
                    <td><?php echo e($g->labelStatusKepegawaian()); ?></td>
                    <td><?php echo e($g->domisili_alamat); ?></td><td><?php echo e($g->sekolah_saat_ini); ?></td>
                </tr>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </tbody>
    </table>
</body>
</html>
<?php /**PATH D:\lrvProject\SPK-Pemetaan-Guru\resources\views/guru/cetak.blade.php ENDPATH**/ ?>