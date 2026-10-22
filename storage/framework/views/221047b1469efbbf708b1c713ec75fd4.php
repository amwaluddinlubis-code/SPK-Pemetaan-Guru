<?php $__env->startSection('judul', 'Hasil Simulasi — PetaPenempatan ASN'); ?>

<?php $__env->startSection('konten'); ?>
<div class="flex flex-wrap items-center justify-between gap-3 mb-4">
    <h1 class="text-2xl font-bold">Hasil Simulasi</h1>
    <a href="<?php echo e(route('simulasi.index')); ?>" class="btn btn-outline btn-sm">← Simulasi Baru</a>
</div>

<div class="alert alert-warning mb-4">
    <span class="text-sm">⚠️ Ini hasil <strong>simulasi hipotetis</strong> (<?php echo e($hasil['versi_algoritme']); ?>, <?php echo e($hasil['waktu']); ?>) — <strong>bukan</strong> rekomendasi resmi dan <strong>tidak</strong> mengubah data operasional.</span>
</div>

<div class="card bg-base-100 shadow p-4 mb-4">
    <h2 class="font-bold mb-2">Parameter</h2>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-2 text-sm">
        <div>Ambang jarak: <strong><?php echo e($hasil['parameter']['ambang_km']); ?> km</strong></div>
        <div>Mapel: <strong><?php echo e($hasil['parameter']['mapel']); ?></strong></div>
        <div>Kecamatan: <strong><?php echo e($hasil['parameter']['kecamatan']); ?></strong></div>
        <div>Sertakan 3T: <strong><?php echo e($hasil['parameter']['sertakan_3t'] ? 'Ya' : 'Tidak'); ?></strong></div>
    </div>
</div>

<div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4">
    <div class="stat bg-base-100 shadow rounded"><div class="stat-title">Kandidat</div><div class="stat-value"><?php echo e($hasil['jumlah_kandidat']); ?></div></div>
    <div class="stat bg-base-100 shadow rounded"><div class="stat-title">Total Hemat (km)</div><div class="stat-value"><?php echo e($hasil['total_hemat_km']); ?></div></div>
    <div class="stat bg-base-100 shadow rounded"><div class="stat-title">Rata-rata Hemat (km)</div><div class="stat-value"><?php echo e($hasil['rata_hemat_km']); ?></div></div>
    <div class="stat bg-base-100 shadow rounded"><div class="stat-title">Baseline: Rekomendasi Aktif</div><div class="stat-value"><?php echo e($hasil['baseline']['rekomendasi_aktif']); ?></div></div>
</div>

<div class="card bg-base-100 shadow p-4 mb-4">
    <h2 class="font-bold mb-2">Asumsi & Keterbatasan</h2>
    <ul class="list-disc list-inside text-sm opacity-80">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $hasil['asumsi']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
            <li><?php echo e($a); ?></li>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
    </ul>
</div>

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($hasil['sekolah_terdampak']) > 0): ?>
<div class="card bg-base-100 shadow p-4 mb-4">
    <h2 class="font-bold mb-2">Sekolah Terdampak (<?php echo e(count($hasil['sekolah_terdampak'])); ?>)</h2>
    <div class="flex flex-wrap gap-2">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $hasil['sekolah_terdampak']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
            <span class="badge badge-outline"><?php echo e($s); ?></span>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
    </div>
</div>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

<div class="card bg-base-100 shadow overflow-x-auto">
    <table class="table table-zebra table-sm">
        <thead><tr><th>Kandidat</th><th>Asal</th><th>Tujuan (simulasi)</th><th class="text-right">Hemat (km)</th></tr></thead>
        <tbody>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $hasil['kandidat']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <tr>
                    <td>Kandidat <?php echo e($loop->iteration); ?></td>
                    <td><?php echo e($k['sekolah_asal']); ?></td>
                    <td><?php echo e($k['sekolah_tujuan']); ?></td>
                    <td class="text-right"><?php echo e($k['hemat_km']); ?></td>
                </tr>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                <tr><td colspan="4" class="text-center py-6 opacity-60">Tidak ada kandidat untuk parameter ini.</td></tr>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </tbody>
    </table>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hasil['jumlah_kandidat'] > 50): ?>
        <p class="text-xs opacity-60 p-3">Menampilkan 50 dari <?php echo e($hasil['jumlah_kandidat']); ?> kandidat.</p>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\lrvProject\SPK-Pemetaan-Guru\resources\views/simulasi/hasil.blade.php ENDPATH**/ ?>