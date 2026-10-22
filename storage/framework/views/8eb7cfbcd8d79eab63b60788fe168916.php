<?php $__env->startSection('judul', 'Distribusi Defisit & Surplus — PetaPenempatan ASN'); ?>

<?php $__env->startSection('konten'); ?>
<div class="mb-4">
    <h1 class="text-2xl font-bold">Distribusi Defisit & Surplus Guru</h1>
    <p class="text-sm opacity-70 mt-1">
        Rumus: <code>defisit = max(kebutuhan − tersedia, 0)</code>,
        <code>surplus = max(tersedia − kebutuhan, 0)</code>.
        Guru dalam proses mutasi (rekomendasi aktif) dihitung sekali di sekolah asal.
        Hanya data agregat — tanpa identitas guru.
    </p>
</div>

<div class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-4">
    <div class="stat bg-base-100 shadow rounded"><div class="stat-title">Tersedia</div><div class="stat-value"><?php echo e($total['tersedia']); ?></div></div>
    <div class="stat bg-base-100 shadow rounded"><div class="stat-title">Kebutuhan</div><div class="stat-value"><?php echo e($total['kebutuhan']); ?></div></div>
    <div class="stat bg-base-100 shadow rounded"><div class="stat-title">Defisit</div><div class="stat-value text-error"><?php echo e($total['defisit']); ?></div></div>
    <div class="stat bg-base-100 shadow rounded"><div class="stat-title">Surplus</div><div class="stat-value text-success"><?php echo e($total['surplus']); ?></div></div>
    <div class="stat bg-base-100 shadow rounded"><div class="stat-title">Dalam Proses</div><div class="stat-value text-warning"><?php echo e($total['dalam_proses']); ?></div></div>
</div>

<form method="GET" action="<?php echo e(route('distribusi.index')); ?>" class="flex gap-2 mb-4">
    <select name="kecamatan" class="select select-sm">
        <option value="">Semua kecamatan</option>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $filter['kecamatan']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
            <option value="<?php echo e($k); ?>" <?php if(request('kecamatan') === $k): echo 'selected'; endif; ?>><?php echo e($k); ?></option>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
    </select>
    <select name="mapel" class="select select-sm">
        <option value="">Semua mapel</option>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $filter['mapel']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
            <option value="<?php echo e($m); ?>" <?php if(request('mapel') === $m): echo 'selected'; endif; ?>><?php echo e($m); ?></option>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
    </select>
    <button type="submit" class="btn btn-primary btn-sm">Filter</button>
    <a href="<?php echo e(route('distribusi.index')); ?>" class="btn btn-ghost btn-sm">Reset</a>
</form>

<div class="card bg-base-100 shadow overflow-x-auto">
    <table class="table table-zebra table-sm">
        <thead>
            <tr><th>Kecamatan</th><th>Mapel</th><th class="text-right">Tersedia</th><th class="text-right">Kebutuhan</th><th class="text-right">Defisit</th><th class="text-right">Surplus</th><th class="text-right">Dalam Proses</th></tr>
        </thead>
        <tbody>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $data; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <tr>
                    <td><?php echo e($d['kecamatan'] ?: '—'); ?></td>
                    <td><?php echo e($d['mapel']); ?></td>
                    <td class="text-right"><?php echo e($d['tersedia']); ?></td>
                    <td class="text-right"><?php echo e($d['kebutuhan']); ?></td>
                    <td class="text-right <?php echo e($d['defisit'] > 0 ? 'text-error font-bold' : ''); ?>"><?php echo e($d['defisit']); ?></td>
                    <td class="text-right <?php echo e($d['surplus'] > 0 ? 'text-success font-bold' : ''); ?>"><?php echo e($d['surplus']); ?></td>
                    <td class="text-right"><?php echo e($d['dalam_proses']); ?></td>
                </tr>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                <?php if (isset($component)) { $__componentOriginal2774db502eccf9ab4eb7044e67aef150 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2774db502eccf9ab4eb7044e67aef150 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.kosong','data' => ['pesan' => 'Tidak ada data untuk filter ini.','kolom' => '7']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('kosong'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['pesan' => 'Tidak ada data untuk filter ini.','kolom' => '7']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2774db502eccf9ab4eb7044e67aef150)): ?>
<?php $attributes = $__attributesOriginal2774db502eccf9ab4eb7044e67aef150; ?>
<?php unset($__attributesOriginal2774db502eccf9ab4eb7044e67aef150); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2774db502eccf9ab4eb7044e67aef150)): ?>
<?php $component = $__componentOriginal2774db502eccf9ab4eb7044e67aef150; ?>
<?php unset($__componentOriginal2774db502eccf9ab4eb7044e67aef150); ?>
<?php endif; ?>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </tbody>
    </table>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\lrvProject\SPK-Pemetaan-Guru\resources\views/distribusi/index.blade.php ENDPATH**/ ?>