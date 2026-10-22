
<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['tipe' => 'info']));

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

foreach (array_filter((['tipe' => 'info']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>
<?php
$kelas = [
    'sukses' => 'alert-success',
    'error' => 'alert-error',
    'warning' => 'alert-warning',
    'info' => 'alert-info',
][$tipe] ?? 'alert-info';
?>
<div <?php echo e($attributes->merge(['class' => 'alert '.$kelas])); ?>>
    <?php echo e($slot); ?>

</div>
<?php /**PATH D:\lrvProject\SPK-Pemetaan-Guru\resources\views/components/alert.blade.php ENDPATH**/ ?>