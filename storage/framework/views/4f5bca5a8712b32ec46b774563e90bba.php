<?php $__env->startSection('judul', 'Aksesibilitas Sekolah — PetaPenempatan ASN'); ?>

<?php $__env->startSection('konten'); ?>
<div class="mb-4">
    <h1 class="text-2xl font-bold">Analisis Jarak & Aksesibilitas</h1>
    <p class="text-sm opacity-70 mt-1">
        Jarak dihitung dengan <strong>Haversine (garis lurus)</strong>, konsisten dengan mesin SPK.
        Ini <strong>bukan</strong> waktu tempuh perjalanan darat — jangan gunakan sebagai dasar klaim waktu tempuh.
        Data agregat per kecamatan; tanpa identitas guru.
    </p>
</div>

<form method="GET" action="<?php echo e(route('aksesibilitas.index')); ?>" class="flex gap-2 mb-4">
    <select name="kategori_daerah" class="select select-sm">
        <option value="">Semua kategori daerah</option>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ['biasa', 'terpencil', 'sangat_terpencil']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
            <option value="<?php echo e($k); ?>" <?php if(request('kategori_daerah') === $k): echo 'selected'; endif; ?>><?php echo e(ucfirst(str_replace('_', ' ', $k))); ?></option>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
    </select>
    <button type="submit" class="btn btn-primary btn-sm">Filter</button>
    <a href="<?php echo e(route('aksesibilitas.index')); ?>" class="btn btn-ghost btn-sm">Reset</a>
</form>

<div class="card bg-base-100 shadow overflow-x-auto">
    <table class="table table-zebra table-sm">
        <thead>
            <tr><th>Kecamatan</th><th>Kategori Daerah</th><th class="text-right">Guru</th><th class="text-right">Rata-rata (km)</th><th class="text-right">Maks (km)</th><th class="text-right">&gt; 15 km</th></tr>
        </thead>
        <tbody>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $data; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <tr>
                    <td><?php echo e($d['kecamatan'] ?: '—'); ?></td>
                    <td><?php echo e(ucfirst(str_replace('_', ' ', $d['kategori_daerah'] ?? 'biasa'))); ?></td>
                    <td class="text-right"><?php echo e($d['jumlah']); ?></td>
                    <td class="text-right"><?php echo e($d['rata_km']); ?></td>
                    <td class="text-right"><?php echo e($d['maks_km']); ?></td>
                    <td class="text-right <?php echo e($d['di_atas_15km'] > 0 ? 'text-warning font-bold' : ''); ?>"><?php echo e($d['di_atas_15km']); ?></td>
                </tr>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                <?php if (isset($component)) { $__componentOriginal2774db502eccf9ab4eb7044e67aef150 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2774db502eccf9ab4eb7044e67aef150 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.kosong','data' => ['pesan' => 'Tidak ada data jarak yang valid.','kolom' => '6']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('kosong'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['pesan' => 'Tidak ada data jarak yang valid.','kolom' => '6']); ?>
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
        <tfoot>
            <tr class="font-bold"><td colspan="2">Total</td><td class="text-right"><?php echo e($total_guru); ?></td><td colspan="3"></td></tr>
        </tfoot>
    </table>
</div>

<div class="alert alert-info mt-4">
    <span class="text-sm">⚠️ Kualitas data: guru/sekolah tanpa koordinat valid tidak masuk perhitungan. Periksa <a href="<?php echo e(route('kualitas-data.index')); ?>" class="link">Pusat Kualitas Data</a> untuk temuan.</span>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\lrvProject\SPK-Pemetaan-Guru\resources\views/aksesibilitas/index.blade.php ENDPATH**/ ?>