
<div>
    
    <div class="flex flex-wrap gap-2 mb-4">
        <button wire:click="$set('status', '')"
           class="badge <?php echo e($status === '' ? 'badge-primary' : 'badge-ghost'); ?> badge-lg">Semua</button>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = \App\Models\HasilSpk::STATUS; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $kode => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
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
                    <th>Guru</th>
                    <th>Sekolah Tujuan</th>
                    <th>Jarak</th>
                    <th>Kelayakan</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $spks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <tr>
                        
                        <td>
                            <div class="font-semibold"><?php echo e($s->guru->nama); ?></div>
                            <div class="text-xs opacity-70"><?php echo e($s->guru->mapel); ?></div>
                            <div class="text-xs mt-1">
                                Asal: <?php echo e($s->guru->sekolah->nama ?? '-'); ?>

                            </div>
                            <div class="text-xs opacity-70">
                                Jarak sekarang: <?php echo e(number_format($s->jarak_sekarang_km, 1, ',', '.')); ?> km (garis lurus)
                            </div>
                        </td>

                        
                        <td>
                            <div class="font-semibold text-primary"><?php echo e($s->sekolahTujuan->nama); ?></div>
                            <div class="text-xs opacity-70"><?php echo e($s->sekolahTujuan->kecamatan); ?></div>
                            <div class="text-xs mt-1">
                                <?php $jj = $jarakJalan[$s->id] ?? null; ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($jj && $jj['sumber'] === 'jalan'): ?>
                                    Jarak usulan: <strong><?php echo e(number_format($jj['jarak_km'], 1, ',', '.')); ?> km</strong>
                                    <span class="badge badge-info badge-xs" title="Jarak tempuh jalan via routing<?php echo e($jj['cached'] ? ' (cache)' : ''); ?><?php echo e($jj['waktu_menit'] ? ' · ±'.$jj['waktu_menit'].' mnt' : ''); ?>">🚗 jalan</span>
                                    <br><span class="opacity-60"><?php echo e(number_format($s->jarak_km, 1, ',', '.')); ?> km garis lurus</span>
                                <?php else: ?>
                                    Jarak usulan: <?php echo e(number_format($s->jarak_km, 1, ',', '.')); ?> km (garis lurus)
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </td>

                        
                        <td class="whitespace-nowrap">
                            <div class="font-bold text-success text-lg">
                                −<?php echo e(number_format($s->hematKm(), 1, ',', '.')); ?> km
                            </div>
                            <div class="text-xs opacity-70">lebih dekat</div>
                            <div class="text-xs opacity-50">(garis lurus)</div>
                        </td>

                        
                        <td>
                            <?php
                                $kunciKuota = $s->sekolah_tujuan_id . '|' . strtolower(trim($s->guru->mapel));
                                $sisa = $sisaKuota[$kunciKuota] ?? 0;
                            ?>
                            <ul class="text-xs space-y-1 list-none p-0 m-0">
                                <li>✅ Mapel <strong><?php echo e($s->guru->mapel); ?></strong> dibutuhkan sekolah tujuan</li>
                                <li>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($sisa > 0): ?>
                                        ✅ Sisa formasi: <strong><?php echo e($sisa); ?></strong>
                                    <?php else: ?>
                                        ⚠️ Sisa formasi: <strong>0</strong> (penuh)
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </li>
                                <li>
                                    ✅ Menghemat <?php echo e(number_format($s->hematKm(), 1, ',', '.')); ?> km
                                    (<?php echo e(number_format($s->jarak_sekarang_km, 1, ',', '.')); ?> → <?php echo e(number_format($s->jarak_km, 1, ',', '.')); ?> km),
                                    di atas ambang <?php echo e($ambangKm); ?> km
                                </li>
                                <li>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($s->guru->berkeluarga()): ?>
                                        👨‍👩‍👧 <?php echo e($s->guru->labelStatusPernikahan()); ?> — prioritas keluarga
                                    <?php else: ?>
                                        <?php echo e($s->guru->labelStatusPernikahan()); ?>

                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </li>
                                <li>
                                    <span class="badge badge-neutral badge-xs"><?php echo e($s->guru->labelStatusKepegawaian()); ?></span>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($s->guru->sudah_sertifikasi): ?>
                                        <span class="badge <?php echo e($s->guru->linear() ? 'badge-success' : 'badge-error'); ?> badge-xs">
                                            Serdik<?php echo e($s->guru->linear() ? '' : ' tidak linear'); ?>

                                        </span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </li>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($dampakAsal[$s->id])): ?>
                                    <li class="text-warning">⚠️ <?php echo e($dampakAsal[$s->id]); ?></li>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </ul>
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
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->hasRole('kabid_ptk', 'kadis')): ?>
                                <a href="<?php echo e(route('spk.telaahan', $s)); ?>" target="_blank"
                                   class="btn btn-info btn-sm" title="Cetak telaahan staf untuk PPK">📄 Telaahan</a>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($s->status === 'rekomendasi'): ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->hasRole('kadis')): ?>
                                    <form method="POST" action="<?php echo e(route('spk.setujui', $s)); ?>" class="inline"
                                          onsubmit="return confirm('Setujui penempatan ini? Guru akan langsung dimutasi.')">
                                        <?php echo csrf_field(); ?>
                                        <button type="submit" class="btn btn-success btn-sm">Setujui</button>
                                    </form>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->hasRole('kabid_ptk') || auth()->user()->hasRole('kadis')): ?>
                                    <form method="POST" action="<?php echo e(route('spk.tolak', $s)); ?>" class="inline"
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
                        <td colspan="6" class="text-center py-8 opacity-60">
                            Belum ada rekomendasi SPK.
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->hasRole('kabid_ptk')): ?>
                                Klik <strong>Generate SPK</strong> untuk mencari penempatan.
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
        <?php echo e($spks->links()); ?>

    </div>
</div>
<?php /**PATH D:\lrvProject\SPK-Pemetaan-Guru\resources\views/livewire/tabel-spk.blade.php ENDPATH**/ ?>