
<div>
    <?php if (isset($component)) { $__componentOriginalc60d0f205c398e761e33cf70feb386d7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc60d0f205c398e761e33cf70feb386d7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.kartu','data' => ['class' => 'mb-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('kartu'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mb-4']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

        <div class="card-body py-3">
            <div class="flex flex-wrap gap-2">
                <input type="text" wire:model.live.debounce.500ms="q"
                       placeholder="Cari nama/NIP guru…"
                       class="input flex-1 min-w-52">
                <select wire:model.live="status" class="select max-w-xs">
                    <option value="">— Semua Status —</option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = \App\Models\UsulanPenempatan::STATUS; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $nilai => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <option value="<?php echo e($nilai); ?>"><?php echo e($label); ?></option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </select>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($q !== '' || $status !== ''): ?>
                    <button wire:click="resetFilter" class="btn btn-ghost">Reset</button>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($q !== '' || $status !== ''): ?>
                <p class="text-sm opacity-60 mt-2">Menampilkan <?php echo e($daftarUsulan->total()); ?> hasil.</p>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc60d0f205c398e761e33cf70feb386d7)): ?>
<?php $attributes = $__attributesOriginalc60d0f205c398e761e33cf70feb386d7; ?>
<?php unset($__attributesOriginalc60d0f205c398e761e33cf70feb386d7); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc60d0f205c398e761e33cf70feb386d7)): ?>
<?php $component = $__componentOriginalc60d0f205c398e761e33cf70feb386d7; ?>
<?php unset($__componentOriginalc60d0f205c398e761e33cf70feb386d7); ?>
<?php endif; ?>

    <?php if (isset($component)) { $__componentOriginalc60d0f205c398e761e33cf70feb386d7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc60d0f205c398e761e33cf70feb386d7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.kartu','data' => ['class' => 'overflow-hidden']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('kartu'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'overflow-hidden']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

        <div class="card-body p-0 overflow-x-auto min-w-0">
            <table class="table table-zebra">
                <thead>
                    <tr>
                        <th>Guru</th>
                        <th>Sekolah Tujuan</th>
                        <th><button wire:click="urutkan('status')" class="link">Status <?php echo e($sort === 'status' ? ($arah === 'asc' ? '▲' : '▼') : ''); ?></button></th>
                        <th>Diajukan Oleh</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $daftarUsulan; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <tr>
                            <td>
                                <strong><?php echo e($u->guru->nama); ?></strong><br>
                                <span class="text-sm opacity-60"><?php echo e($u->guru->nipTersamar()); ?> • <?php echo e($u->guru->mapel); ?></span>
                            </td>
                            <td class="text-sm"><?php echo e($u->sekolah->nama); ?></td>
                            <td><span class="badge <?php echo e($u->badgeStatus()); ?>"><?php echo e($u->labelStatus()); ?></span></td>
                            <td class="text-sm"><?php echo e($u->pengaju->name ?? '—'); ?></td>
                            <td class="text-right">
                                <a href="<?php echo e(route('usulan.show', $u)); ?>" class="btn btn-sm btn-info">Lihat</a>
                            </td>
                        </tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <?php if (isset($component)) { $__componentOriginal2774db502eccf9ab4eb7044e67aef150 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2774db502eccf9ab4eb7044e67aef150 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.kosong','data' => ['pesan' => 'Tidak ada usulan yang cocok.','kolom' => '5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('kosong'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['pesan' => 'Tidak ada usulan yang cocok.','kolom' => '5']); ?>
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
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc60d0f205c398e761e33cf70feb386d7)): ?>
<?php $attributes = $__attributesOriginalc60d0f205c398e761e33cf70feb386d7; ?>
<?php unset($__attributesOriginalc60d0f205c398e761e33cf70feb386d7); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc60d0f205c398e761e33cf70feb386d7)): ?>
<?php $component = $__componentOriginalc60d0f205c398e761e33cf70feb386d7; ?>
<?php unset($__componentOriginalc60d0f205c398e761e33cf70feb386d7); ?>
<?php endif; ?>

    <div class="mt-4 flex flex-wrap items-center justify-between gap-2">
        <?php if (isset($component)) { $__componentOriginal42cc9ebc762ae7b6f15beaabdb1d3427 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal42cc9ebc762ae7b6f15beaabdb1d3427 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.info-halaman','data' => ['data' => $daftarUsulan]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('info-halaman'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['data' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($daftarUsulan)]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal42cc9ebc762ae7b6f15beaabdb1d3427)): ?>
<?php $attributes = $__attributesOriginal42cc9ebc762ae7b6f15beaabdb1d3427; ?>
<?php unset($__attributesOriginal42cc9ebc762ae7b6f15beaabdb1d3427); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal42cc9ebc762ae7b6f15beaabdb1d3427)): ?>
<?php $component = $__componentOriginal42cc9ebc762ae7b6f15beaabdb1d3427; ?>
<?php unset($__componentOriginal42cc9ebc762ae7b6f15beaabdb1d3427); ?>
<?php endif; ?>
        <?php echo e($daftarUsulan->links()); ?>

    </div>
</div>
<?php /**PATH D:\lrvProject\SPK-Pemetaan-Guru\resources\views/livewire/tabel-usulan.blade.php ENDPATH**/ ?>