
<div class="flex flex-wrap justify-between items-center gap-2 mb-4">
    <div>
        <h1 class="text-2xl font-bold"><?php echo e($judul); ?></h1>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($deskripsi)): ?>
            <p class="text-sm opacity-70 mt-1"><?php echo e($deskripsi); ?></p>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($aksi)): ?>
        <div class="flex flex-wrap gap-2"><?php echo e($aksi); ?></div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH D:\lrvProject\SPK-Pemetaan-Guru\resources\views/components/halaman.blade.php ENDPATH**/ ?>