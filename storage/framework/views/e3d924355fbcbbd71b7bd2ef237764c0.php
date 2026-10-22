<?php $__env->startSection('judul', 'Ajukan Usulan — PetaPenempatan ASN'); ?>

<?php $__env->startSection('konten'); ?>
    <h1 class="text-2xl font-bold mb-4">📝 Ajukan Usulan Penempatan</h1>

    <div class="card bg-base-100 shadow-xl">
        <div class="card-body">
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

            <form method="POST" action="<?php echo e(route('usulan.store')); ?>">
                <?php echo csrf_field(); ?>
                <div class="grid gap-4 sm:grid-cols-2">
                    <fieldset class="fieldset w-full">
                        <legend class="fieldset-legend font-semibold">Guru yang diusulkan</legend>
                        <select name="guru_id" class="select w-full" required>
                            <option value="">— Pilih guru —</option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $daftarGuru; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $g): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <option value="<?php echo e($g->id); ?>"
                                    <?php echo e(old('guru_id') == $g->id ? 'selected' : ''); ?>>
                                    <?php echo e($g->nama); ?> — <?php echo e($g->mapel); ?> (<?php echo e($g->sekolah_saat_ini); ?>)
                                </option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </select>
                    </fieldset>
                    <fieldset class="fieldset w-full">
                        <legend class="fieldset-legend font-semibold">Sekolah Tujuan</legend>
                        <select name="sekolah_id" class="select w-full" required>
                            <option value="">— Pilih sekolah —</option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $daftarSekolah; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <option value="<?php echo e($s->id); ?>"
                                    <?php echo e(old('sekolah_id') == $s->id ? 'selected' : ''); ?>>
                                    <?php echo e($s->nama); ?> (<?php echo e($s->kecamatan ?? '-'); ?>)
                                </option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </select>
                    </fieldset>
                    <fieldset class="fieldset w-full sm:col-span-2">
                        <legend class="fieldset-legend font-semibold">Alasan Pengajuan (min. 10 karakter)</legend>
                        <textarea name="alasan" rows="4" required
                                  class="textarea w-full"
                                  placeholder="mis. Jarak domisili ke sekolah saat ini 12 km; sekolah tujuan butuh guru Matematika dan hanya 1,5 km dari rumah."><?php echo e(old('alasan')); ?></textarea>
                    </fieldset>
                </div>
                <p class="text-xs opacity-60 mt-2">
                    💡 Sekolah tujuan harus membutuhkan mapel guru yang diusulkan.
                    Alur: Diajukan → Diverifikasi Kabid → Disetujui Kadis (data guru otomatis dimutasi).
                </p>

                <div class="flex gap-2 mt-6">
                    <button type="submit" class="btn btn-primary">📨 Kirim Usulan</button>
                    <a href="<?php echo e(route('usulan.index')); ?>" class="btn btn-ghost">Batal</a>
                </div>
            </form>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\lrvProject\SPK-Pemetaan-Guru\resources\views/usulan/form.blade.php ENDPATH**/ ?>