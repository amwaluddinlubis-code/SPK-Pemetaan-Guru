<?php $__env->startSection('judul', 'Kualitas Data — PetaPenempatan ASN'); ?>

<?php $__env->startSection('konten'); ?>
<div class="flex flex-wrap items-center justify-between gap-3 mb-4">
    <h1 class="text-2xl font-bold">Pusat Kualitas Data</h1>
    <a href="<?php echo e(route('kualitas-data.export', request()->query())); ?>" class="btn btn-outline btn-sm">📥 Ekspor Laporan</a>
</div>

<p class="text-sm opacity-70 mb-4">Pemeriksaan bersifat read-only — tidak mengubah data. Klik tautan perbaikan untuk mengedit record yang bermasalah.</p>

<div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4">
    <div class="stat bg-base-100 shadow rounded">
        <div class="stat-title">Total Temuan</div>
        <div class="stat-value"><?php echo e($total); ?></div>
    </div>
    <div class="stat bg-base-100 shadow rounded">
        <div class="stat-title">Dampak Tinggi</div>
        <div class="stat-value text-error"><?php echo e($ringkasan['tinggi']); ?></div>
    </div>
    <div class="stat bg-base-100 shadow rounded">
        <div class="stat-title">Dampak Sedang</div>
        <div class="stat-value text-warning"><?php echo e($ringkasan['sedang']); ?></div>
    </div>
    <div class="stat bg-base-100 shadow rounded">
        <div class="stat-title">Dampak Rendah</div>
        <div class="stat-value text-info"><?php echo e($ringkasan['rendah']); ?></div>
    </div>
</div>

<form method="GET" action="<?php echo e(route('kualitas-data.index')); ?>" class="flex gap-2 mb-4">
    <select name="dampak" class="select select-sm">
        <option value="">Semua dampak</option>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ['tinggi', 'sedang', 'rendah']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
            <option value="<?php echo e($d); ?>" <?php if(request('dampak') === $d): echo 'selected'; endif; ?>><?php echo e(ucfirst($d)); ?></option>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
    </select>
    <select name="jenis" class="select select-sm">
        <option value="">Semua jenis</option>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $daftarJenis; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $j): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
            <option value="<?php echo e($j); ?>" <?php if(request('jenis') === $j): echo 'selected'; endif; ?>><?php echo e($j); ?></option>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
    </select>
    <button type="submit" class="btn btn-primary btn-sm">Filter</button>
    <a href="<?php echo e(route('kualitas-data.index')); ?>" class="btn btn-ghost btn-sm">Reset</a>
</form>

<div class="card bg-base-100 shadow overflow-x-auto">
    <table class="table table-zebra table-sm">
        <thead>
            <tr><th>Entitas</th><th>Nama</th><th>Jenis</th><th>Dampak</th><th>Pesan</th><th>Perbaikan</th></tr>
        </thead>
        <tbody>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $temuan; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <tr>
                    <td><?php echo e($t['entitas']); ?></td>
                    <td><?php echo e($t['nama']); ?></td>
                    <td><code class="text-xs"><?php echo e($t['jenis']); ?></code></td>
                    <td>
                        <span class="badge <?php echo e($t['dampak'] === 'tinggi' ? 'badge-error' : ($t['dampak'] === 'sedang' ? 'badge-warning' : 'badge-info')); ?>"><?php echo e($t['dampak']); ?></span>
                    </td>
                    <td class="text-xs"><?php echo e($t['pesan']); ?></td>
                    <td>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($t['tautan'] !== '#'): ?>
                            <a href="<?php echo e($t['tautan']); ?>" class="btn btn-xs btn-outline">Perbaiki</a>
                        <?php else: ?>
                            <span class="opacity-40 text-xs">—</span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </td>
                </tr>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                <tr><td colspan="6" class="text-center py-8">
                    <div class="text-lg">✅</div>
                    <div class="opacity-60">Tidak ada temuan kualitas data. Data dalam kondisi baik.</div>
                </td></tr>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </tbody>
    </table>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\lrvProject\SPK-Pemetaan-Guru\resources\views/kualitas-data/index.blade.php ENDPATH**/ ?>