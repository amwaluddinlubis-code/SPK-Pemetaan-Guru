
<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['aksi', 'tanya' => 'Hapus data ini?', 'label' => 'Hapus']));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['aksi', 'tanya' => 'Hapus data ini?', 'label' => 'Hapus']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>
<form method="POST" action="<?php echo e($aksi); ?>" class="inline" onsubmit="return confirm('<?php echo e($tanya); ?>')">
    <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
    <button type="submit" <?php echo e($attributes->merge(['class' => 'btn btn-sm btn-error'])); ?>><?php echo e($label); ?></button>
</form>
<?php /**PATH D:\lrvProject\SPK-Pemetaan-Guru\resources\views/components/hapus.blade.php ENDPATH**/ ?>