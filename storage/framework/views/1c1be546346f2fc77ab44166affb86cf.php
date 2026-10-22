<?php $__env->startSection('judul', 'Mutasi Silang — PetaPenempatan ASN'); ?>

<?php $__env->startSection('konten'); ?>


<?php if (isset($component)) { $__componentOriginalbf8f23e81188b3bdd1bb43b9e82f4c0b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalbf8f23e81188b3bdd1bb43b9e82f4c0b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.halaman','data' => ['judul' => '🔄 Mutasi Silang (Swap)']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('halaman'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['judul' => '🔄 Mutasi Silang (Swap)']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

     <?php $__env->slot('deskripsi', null, []); ?> 
        Rekomendasi pertukaran dua guru bermapel sama: keduanya menjadi
        lebih dekat ke domisili setelah bertukar sekolah.
     <?php $__env->endSlot(); ?>
     <?php $__env->slot('aksi', null, []); ?> 
        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->hasRole('kabid_ptk')): ?>
            <form method="POST" action="<?php echo e(route('swap.generate')); ?>"
                  onsubmit="return confirm('Jalankan pencocokan pasangan swap untuk semua guru?')">
                <?php echo csrf_field(); ?>
                <button type="submit" class="btn btn-primary">⚙️ Generate Rekomendasi</button>
            </form>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
     <?php $__env->endSlot(); ?>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalbf8f23e81188b3bdd1bb43b9e82f4c0b)): ?>
<?php $attributes = $__attributesOriginalbf8f23e81188b3bdd1bb43b9e82f4c0b; ?>
<?php unset($__attributesOriginalbf8f23e81188b3bdd1bb43b9e82f4c0b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalbf8f23e81188b3bdd1bb43b9e82f4c0b)): ?>
<?php $component = $__componentOriginalbf8f23e81188b3bdd1bb43b9e82f4c0b; ?>
<?php unset($__componentOriginalbf8f23e81188b3bdd1bb43b9e82f4c0b); ?>
<?php endif; ?>

<?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('tabel-swap');

$__keyOuter = $__key ?? null;

$__key = null;
$__componentSlots = [];

$__key ??= \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::generateKey('lw-2564212333-0', $__key);

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

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\lrvProject\SPK-Pemetaan-Guru\resources\views/swap/index.blade.php ENDPATH**/ ?>