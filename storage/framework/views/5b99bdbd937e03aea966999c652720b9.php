<?php $__env->startSection('judul', 'Proyeksi Kebutuhan — PetaPenempatan ASN'); ?>

<?php $__env->startSection('konten'); ?>
<div class="mb-4">
    <h1 class="text-2xl font-bold">Proyeksi Kebutuhan Guru 1–5 Tahun</h1>
</div>

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! $dataCukup): ?>
    <div class="alert alert-warning mb-4">
        <span class="text-sm">⚠️ <strong>Data belum cukup untuk membuat proyeksi.</strong> Aplikasi saat ini hanya menyimpan snapshot kondisi terkini tanpa deret waktu historis. Kami tidak mengarang angka proyeksi.</span>
    </div>

    <div class="card bg-base-100 shadow p-6 mb-4">
        <h2 class="font-bold text-lg mb-3">Syarat Data untuk Proyeksi</h2>
        <ul class="list-disc list-inside text-sm space-y-1 opacity-80">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $syarat; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <li><?php echo e($s); ?></li>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </ul>
    </div>

    <div class="card bg-base-100 shadow p-6">
        <h2 class="font-bold text-lg mb-3">Metodologi yang Akan Dipakai</h2>
        <ul class="list-disc list-inside text-sm space-y-1 opacity-80">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $metodologi; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <li><?php echo e($m); ?></li>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </ul>
        <p class="text-xs opacity-60 mt-4">Jejak audit operasional (<a href="<?php echo e(route('aktivitas.index')); ?>" class="link">/aktivitas</a>) mulai mencatat perubahan data — setelah terkumpul beberapa periode, data ini dapat menjadi dasar deret waktu.</p>
    </div>
<?php else: ?>
    <div class="alert alert-info">Data historis tersedia — proyeksi dapat dihitung. (Belum diimplementasikan.)</div>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\lrvProject\SPK-Pemetaan-Guru\resources\views/proyeksi/index.blade.php ENDPATH**/ ?>