
<div>
    
    <div class="flex flex-wrap gap-2 mb-4">
        <button wire:click="$set('status', '')"
           class="badge <?php echo e($status === '' ? 'badge-primary' : 'badge-ghost'); ?> badge-lg">Semua</button>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = \App\Models\RekomendasiSwap::STATUS; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $kode => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
            <button wire:click="$set('status', '<?php echo e($kode); ?>')"
               class="badge <?php echo e($status === $kode ? 'badge-primary' : 'badge-ghost'); ?> badge-lg">
                <?php echo e($label); ?>

            </button>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
    </div>

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

        <table class="table table-zebra">
            <thead>
                <tr>
                    <th>Guru A</th>
                    <th>Guru B</th>
                    <th>Penghematan Jarak</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $swaps; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <tr>
                        
                        <td>
                            <div class="font-semibold"><?php echo e($s->guruA->nama); ?></div>
                            <div class="text-xs opacity-70"><?php echo e($s->guruA->mapel); ?></div>
                            <div class="text-xs mt-1">
                                <?php echo e($s->guruA->sekolah->nama ?? '-'); ?>

                                →
                                <span class="font-semibold text-primary"><?php echo e($s->sekolahTujuanA->nama); ?></span>
                            </div>
                            <div class="text-xs opacity-70">
                                <?php echo e(number_format($s->jarak_a_sekarang_km, 1, ',', '.')); ?> km
                                →
                                <?php echo e(number_format($s->jarak_a_usulan_km, 1, ',', '.')); ?> km
                                <span class="opacity-60">(garis lurus)</span>
                            </div>
                        </td>

                        
                        <td>
                            <div class="font-semibold"><?php echo e($s->guruB->nama); ?></div>
                            <div class="text-xs opacity-70"><?php echo e($s->guruB->mapel); ?></div>
                            <div class="text-xs mt-1">
                                <?php echo e($s->guruB->sekolah->nama ?? '-'); ?>

                                →
                                <span class="font-semibold text-primary"><?php echo e($s->sekolahTujuanB->nama); ?></span>
                            </div>
                            <div class="text-xs opacity-70">
                                <?php echo e(number_format($s->jarak_b_sekarang_km, 1, ',', '.')); ?> km
                                →
                                <?php echo e(number_format($s->jarak_b_usulan_km, 1, ',', '.')); ?> km
                                <span class="opacity-60">(garis lurus)</span>
                            </div>
                        </td>

                        
                        <td class="whitespace-nowrap">
                            <div class="font-bold text-success text-lg">
                                −<?php echo e(number_format($s->total_hemat_km, 1, ',', '.')); ?> km
                            </div>
                            <div class="text-xs opacity-70">
                                A: −<?php echo e(number_format($s->hemat_a_km, 1, ',', '.')); ?> km<br>
                                B: −<?php echo e(number_format($s->hemat_b_km, 1, ',', '.')); ?> km
                            </div>
                        </td>

                        
                        <td>
                            <span class="badge <?php echo e($s->badgeStatus()); ?>"><?php echo e($s->labelStatus()); ?></span>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($s->diprosesOleh): ?>
                                <div class="text-xs opacity-70 mt-1">oleh <?php echo e($s->diprosesOleh->name); ?></div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($s->catatan_admin): ?>
                                <div class="text-xs opacity-70 italic">"<?php echo e($s->catatan_admin); ?>"</div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </td>

                        
                        <td class="whitespace-nowrap">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($s->status === 'menunggu'): ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->hasRole('kadis')): ?>
                                    <form method="POST" action="<?php echo e(route('swap.setujui', $s)); ?>" class="inline"
                                          onsubmit="return confirm('Setujui swap ini? Kedua guru akan langsung dimutasi.')">
                                        <?php echo csrf_field(); ?>
                                        <button type="submit" class="btn btn-success btn-sm">Setujui</button>
                                    </form>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->hasRole('kabid_ptk') || auth()->user()->hasRole('kadis')): ?>
                                    <form method="POST" action="<?php echo e(route('swap.tolak', $s)); ?>" class="inline"
                                          onsubmit="const a = prompt('Alasan penolakan (wajib diisi):'); if (!a || !a.trim()) { alert('Alasan penolakan wajib diisi.'); return false; } this.querySelector('[name=catatan_admin]').value = a.trim(); return true;">
                                        <?php echo csrf_field(); ?>
                                        <input type="hidden" name="catatan_admin" value="">
                                        <button type="submit" class="btn btn-error btn-sm">Tolak</button>
                                    </form>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php else: ?>
                                <span class="text-xs opacity-50">—</span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </td>
                    </tr>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <tr>
                        <td colspan="5" class="text-center py-8 opacity-60">
                            Belum ada rekomendasi swap.
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->hasRole('kabid_ptk')): ?>
                                Klik <strong>Generate Rekomendasi</strong> untuk mencari pasangan.
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </td>
                    </tr>
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

    <div class="mt-4">
        <?php echo e($swaps->links()); ?>

    </div>
</div>
<?php /**PATH D:\lrvProject\SPK-Pemetaan-Guru\resources\views/livewire/tabel-swap.blade.php ENDPATH**/ ?>