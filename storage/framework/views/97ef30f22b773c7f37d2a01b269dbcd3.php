<?php $__env->startSection('judul', 'Dashboard — PetaPenempatan ASN'); ?>


<?php $__env->startPush('kepala'); ?>
    <?php echo app('Illuminate\Foundation\Vite')('resources/js/peta.js'); ?>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('konten'); ?>
    <h1 class="text-2xl font-bold mb-4"><?php if (isset($component)) { $__componentOriginal8d917d15c7538e1421c51d0f23f3fc09 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8d917d15c7538e1421c51d0f23f3fc09 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ikon','data' => ['nama' => 'dashboard','class' => 'h-7 w-7 inline']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ikon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['nama' => 'dashboard','class' => 'h-7 w-7 inline']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8d917d15c7538e1421c51d0f23f3fc09)): ?>
<?php $attributes = $__attributesOriginal8d917d15c7538e1421c51d0f23f3fc09; ?>
<?php unset($__attributesOriginal8d917d15c7538e1421c51d0f23f3fc09); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8d917d15c7538e1421c51d0f23f3fc09)): ?>
<?php $component = $__componentOriginal8d917d15c7538e1421c51d0f23f3fc09; ?>
<?php unset($__componentOriginal8d917d15c7538e1421c51d0f23f3fc09); ?>
<?php endif; ?> Dashboard Pemetaan</h1>

    
    <div class="stats stats-vertical sm:stats-horizontal shadow w-full mb-6">
        <div class="stat">
            <div class="stat-title">Total Guru</div>
            <div class="stat-value text-primary"><?php echo e($totalGuru); ?></div>
            <div class="stat-desc">terdata di sistem</div>
        </div>
        <div class="stat">
            <div class="stat-title">Total Sekolah</div>
            <div class="stat-value text-secondary"><?php echo e($totalSekolah); ?></div>
            <div class="stat-desc">terdata di sistem</div>
        </div>
        <div class="stat">
            <div class="stat-title">Rata-rata Jarak</div>
            <div class="stat-value">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($rataJarak !== null): ?>
                    <?php echo e(number_format($rataJarak, 1)); ?> km
                <?php else: ?>
                    —
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <div class="stat-desc"><?php echo e($guruDenganJarak); ?> dari <?php echo e($totalGuru); ?> guru memiliki koordinat valid</div>
        </div>
        <div class="stat">
            <div class="stat-title">Butuh Perhatian ⚠️</div>
            <div class="stat-value text-error"><?php echo e($guruJauh); ?></div>
            <div class="stat-desc">guru berjarak &gt; 5 km</div>
        </div>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->hasRole('kabid_ptk', 'kadis')): ?>
            <div class="stat">
                <div class="stat-title">Usulan Menunggu 📝</div>
                <div class="stat-value text-warning"><?php echo e($usulanMenunggu); ?></div>
                <div class="stat-desc"><a href="<?php echo e(route('usulan.index')); ?>" class="link">proses sekarang</a></div>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    
    <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('peta-rekomendasi');

$__keyOuter = $__key ?? null;

$__key = null;
$__componentSlots = [];

$__key ??= \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::generateKey('lw-3246803177-0', $__key);

$__html = app('livewire')->mount($__name, $__params, $__key, $__componentSlots);

echo $__html;

unset($__html);
unset($__key);
$__key = $__keyOuter;
unset($__keyOuter);
unset($__name);
unset($__params);
unset($__componentSlots);
unset($__split);
?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\lrvProject\SPK-Pemetaan-Guru\resources\views/dashboard.blade.php ENDPATH**/ ?>