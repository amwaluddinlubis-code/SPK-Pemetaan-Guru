<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lupa Password — PetaPenempatan ASN</title>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css']); ?>
</head>
<body class="min-h-screen bg-base-200 flex items-center justify-center p-4">
    <div class="card w-full max-w-md bg-base-100 shadow-xl">
        <div class="card-body">
            <h1 class="card-title text-2xl">Lupa Password</h1>
            <p class="text-sm opacity-70">Masukkan email akun Anda. Kami akan mengirimkan tautan untuk mereset password.</p>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('sukses')): ?>
                <div class="alert alert-success text-sm"><?php echo e(session('sukses')); ?></div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errors->any()): ?>
                <?php if (isset($component)) { $__componentOriginald7761d07c2d3283832d1345e27d63637 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald7761d07c2d3283832d1345e27d63637 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.galat','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('galat'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald7761d07c2d3283832d1345e27d63637)): ?>
<?php $attributes = $__attributesOriginald7761d07c2d3283832d1345e27d63637; ?>
<?php unset($__attributesOriginald7761d07c2d3283832d1345e27d63637); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald7761d07c2d3283832d1345e27d63637)): ?>
<?php $component = $__componentOriginald7761d07c2d3283832d1345e27d63637; ?>
<?php unset($__componentOriginald7761d07c2d3283832d1345e27d63637); ?>
<?php endif; ?>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <form method="POST" action="<?php echo e(route('password.email')); ?>" class="space-y-4 mt-2">
                <?php echo csrf_field(); ?>
                <fieldset class="fieldset w-full">
                    <legend class="fieldset-legend font-semibold">Email</legend>
                    <input type="email" name="email" required value="<?php echo e(old('email')); ?>"
                           class="input w-full" placeholder="nama@contoh.id" autofocus>
                </fieldset>
                <button type="submit" class="btn btn-primary w-full">Kirim Tautan Reset</button>
            </form>

            <a href="<?php echo e(route('login')); ?>" class="link link-primary text-sm mt-2">← Kembali ke halaman login</a>
        </div>
    </div>
</body>
</html>
<?php /**PATH D:\lrvProject\SPK-Pemetaan-Guru\resources\views/auth/lupa-password.blade.php ENDPATH**/ ?>