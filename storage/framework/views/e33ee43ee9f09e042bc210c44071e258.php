
<div>
    <?php if (isset($component)) { $__componentOriginalc60d0f205c398e761e33cf70feb386d7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc60d0f205c398e761e33cf70feb386d7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.kartu','data' => ['class' => 'mb-4 p-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('kartu'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mb-4 p-4']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

        <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
            <div>
                <label class="label"><span class="label-text">Aksi</span></label>
                <select wire:model.live="aksi" class="select select-sm w-full">
                    <option value="">Semua</option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $daftarAksi; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <option value="<?php echo e($a); ?>"><?php echo e($a); ?></option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </select>
            </div>
            <div>
                <label class="label"><span class="label-text">Aktor</span></label>
                <select wire:model.live="user_id" class="select select-sm w-full">
                    <option value="">Semua</option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $daftarUser; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <option value="<?php echo e($u->id); ?>"><?php echo e($u->name); ?> (<?php echo e($u->role); ?>)</option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </select>
            </div>
            <div>
                <label class="label"><span class="label-text">Hasil</span></label>
                <select wire:model.live="hasil" class="select select-sm w-full">
                    <option value="">Semua</option>
                    <option value="sukses">Sukses</option>
                    <option value="gagal">Gagal</option>
                </select>
            </div>
            <div>
                <label class="label"><span class="label-text">Dari</span></label>
                <input type="date" wire:model.live="dari" class="input input-sm w-full">
            </div>
            <div>
                <label class="label"><span class="label-text">Sampai</span></label>
                <input type="date" wire:model.live="sampai" class="input input-sm w-full">
            </div>
        </div>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($aksi !== '' || $user_id !== '' || $hasil !== '' || $dari !== '' || $sampai !== ''): ?>
            <div class="mt-3">
                <button wire:click="resetFilter" class="btn btn-ghost btn-sm">Reset</button>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.kartu','data' => ['class' => 'overflow-x-auto']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('kartu'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'overflow-x-auto']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

        <table class="table table-zebra table-sm">
            <thead>
                <tr><th>Waktu</th><th>Aktor</th><th>Aksi</th><th>Entitas</th><th>Hasil</th><th>Detail</th></tr>
            </thead>
            <tbody>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $log; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <tr>
                        <td class="whitespace-nowrap"><?php echo e($l->waktu->format('d/m/Y H:i')); ?></td>
                        <td><?php echo e($l->user?->name ?? '—'); ?></td>
                        <td><span class="badge badge-outline"><?php echo e($l->aksi); ?></span></td>
                        <td><?php echo e($l->entitas_tipe); ?><?php echo e($l->entitas_id ? ' #'.$l->entitas_id : ''); ?></td>
                        <td>
                            <span class="badge <?php echo e($l->hasil === 'sukses' ? 'badge-success' : 'badge-error'); ?>"><?php echo e($l->hasil); ?></span>
                        </td>
                        <td class="text-xs">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($l->perubahan): ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $l->perubahan; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                    <div><span class="font-semibold"><?php echo e($k); ?>:</span> <?php echo e(is_array($v) ? json_encode($v) : $v); ?></div>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <?php else: ?>
                                <span class="opacity-40">—</span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </td>
                    </tr>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <?php if (isset($component)) { $__componentOriginal2774db502eccf9ab4eb7044e67aef150 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2774db502eccf9ab4eb7044e67aef150 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.kosong','data' => ['pesan' => 'Belum ada catatan audit.','kolom' => '6']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('kosong'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['pesan' => 'Belum ada catatan audit.','kolom' => '6']); ?>
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

    <div class="mt-4"><?php echo e($log->links()); ?></div>
</div>
<?php /**PATH D:\lrvProject\SPK-Pemetaan-Guru\resources\views/livewire/tabel-aktivitas.blade.php ENDPATH**/ ?>