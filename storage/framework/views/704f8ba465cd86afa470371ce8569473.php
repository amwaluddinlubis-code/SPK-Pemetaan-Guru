<?php $__env->startSection('judul', 'Analisis Kebutuhan Guru — PetaPenempatan ASN'); ?>

<?php $__env->startSection('konten'); ?>
    <h1 class="text-2xl font-bold mb-1">Analisis Kebutuhan Guru</h1>
    <p class="opacity-70 mb-4"><?php echo e($sekolah->nama); ?> · NPSN <?php echo e($sekolah->npsn); ?> · <?php echo e($sekolah->kecamatan); ?></p>

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

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($rombel <= 0): ?>
        <?php if (isset($component)) { $__componentOriginal5194778a3a7b899dcee5619d0610f5cf = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5194778a3a7b899dcee5619d0610f5cf = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.alert','data' => ['tipe' => 'warning','class' => 'mb-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('alert'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['tipe' => 'warning','class' => 'mb-4']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

            Jumlah rombel sekolah ini belum diisi, jadi kebutuhan belum bisa dihitung.
            <a href="<?php echo e(route('sekolah.edit', $sekolah)); ?>" class="link font-semibold">Isi rombel di form ubah sekolah</a>
            (angka Rombel tersedia di CSV ekspor Dapodik per kecamatan).
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5194778a3a7b899dcee5619d0610f5cf)): ?>
<?php $attributes = $__attributesOriginal5194778a3a7b899dcee5619d0610f5cf; ?>
<?php unset($__attributesOriginal5194778a3a7b899dcee5619d0610f5cf); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5194778a3a7b899dcee5619d0610f5cf)): ?>
<?php $component = $__componentOriginal5194778a3a7b899dcee5619d0610f5cf; ?>
<?php unset($__componentOriginal5194778a3a7b899dcee5619d0610f5cf); ?>
<?php endif; ?>
    <?php else: ?>
        <div class="card bg-base-100 shadow-xl mb-4">
            <div class="card-body">
                <div class="flex flex-wrap gap-2 mb-4">
                    <span class="badge badge-info">Jenjang: <?php echo e($jenjang); ?></span>
                    <span class="badge badge-info">Rombel: <?php echo e($rombel); ?></span>
                    <span class="badge badge-ghost">Acuan beban mengajar: <?php echo e($bebanMengajar); ?> JP/minggu</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="table table-zebra">
                        <thead>
                            <tr>
                                <th>Jenis / Mata Pelajaran</th>
                                <th class="text-right">JP/Minggu</th>
                                <th class="text-right">Kebutuhan (hitung)</th>
                                <th class="text-right">Guru Ada</th>
                                <th class="text-right">Selisih</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $baris; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <tr>
                                    <td class="font-semibold"><?php echo e($b['label']); ?></td>
                                    <td class="text-right"><?php echo e($b['jp_per_minggu'] ?? '—'); ?></td>
                                    <td class="text-right"><?php echo e($b['kebutuhan']); ?></td>
                                    <td class="text-right"><?php echo e($b['eksisting']); ?></td>
                                    <td class="text-right">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($b['selisih'] < 0): ?>
                                            <span class="badge badge-error">kurang <?php echo e(abs($b['selisih'])); ?></span>
                                        <?php elseif($b['selisih'] > 0): ?>
                                            <span class="badge badge-warning">lebih <?php echo e($b['selisih']); ?></span>
                                        <?php else: ?>
                                            <span class="badge badge-success">pas</span>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </td>
                                </tr>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <p class="text-sm opacity-70 mt-4">
                    Rumus: <code>kebutuhan = ceil(rombel × JP/minggu ÷ <?php echo e($bebanMengajar); ?>)</code>;
                    guru kelas SD = jumlah rombel. JP/minggu mengikuti Permendikdasmen No. 13/2025
                    (intrakurikuler saja). Selisih negatif berarti kekurangan guru.
                </p>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->hasRole('kepala_sekolah', 'kabid_ptk')): ?>
                    <form method="POST" action="<?php echo e(route('sekolah.analisis.terapkan', $sekolah)); ?>" class="mt-4"
                          onsubmit="return confirm('Terapkan hasil hitungan ini sebagai kebutuhan guru resmi? Baris kebutuhan lama akan diganti.')">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="btn btn-primary">✓ Terapkan sebagai Kebutuhan Guru</button>
                        <a href="<?php echo e(route('sekolah.index')); ?>" class="btn btn-ghost">Kembali</a>
                    </form>
                <?php else: ?>
                    <a href="<?php echo e(route('sekolah.index')); ?>" class="btn btn-ghost mt-4">Kembali</a>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\lrvProject\SPK-Pemetaan-Guru\resources\views/sekolah/analisis.blade.php ENDPATH**/ ?>