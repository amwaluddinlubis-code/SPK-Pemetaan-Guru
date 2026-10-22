<?php $__env->startSection('judul', 'Eksekutif Pemda — PetaPenempatan ASN'); ?>

<?php $__env->startSection('konten'); ?>
    <h1 class="text-2xl font-bold mb-1"><?php if (isset($component)) { $__componentOriginal8d917d15c7538e1421c51d0f23f3fc09 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8d917d15c7538e1421c51d0f23f3fc09 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ikon','data' => ['nama' => 'distribusi','class' => 'h-7 w-7 inline']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ikon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['nama' => 'distribusi','class' => 'h-7 w-7 inline']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8d917d15c7538e1421c51d0f23f3fc09)): ?>
<?php $attributes = $__attributesOriginal8d917d15c7538e1421c51d0f23f3fc09; ?>
<?php unset($__attributesOriginal8d917d15c7538e1421c51d0f23f3fc09); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8d917d15c7538e1421c51d0f23f3fc09)): ?>
<?php $component = $__componentOriginal8d917d15c7538e1421c51d0f23f3fc09; ?>
<?php unset($__componentOriginal8d917d15c7538e1421c51d0f23f3fc09); ?>
<?php endif; ?> Dashboard Eksekutif Pemda</h1>
    <p class="text-sm opacity-60 mb-4">Ringkasan penempatan Guru ASN se-Kabupaten — hanya lihat, tanpa ubah data.</p>

    
    <div class="stats stats-vertical sm:stats-horizontal shadow w-full mb-6" data-testid="ringkasan-eksekutif">
        <div class="stat">
            <div class="stat-title">Total Guru</div>
            <div class="stat-value text-primary"><?php echo e($totalGuru); ?></div>
            <div class="stat-desc">terdata di sistem</div>
        </div>
        <div class="stat">
            <div class="stat-title">Total Sekolah</div>
            <div class="stat-value text-secondary"><?php echo e($totalSekolah); ?></div>
            <div class="stat-desc">terdata di sistem</div>
        </div>
        <div class="stat">
            <div class="stat-title">Kebutuhan Guru</div>
            <div class="stat-value text-warning"><?php echo e($totalKebutuhan); ?></div>
            <div class="stat-desc">formasi belum terpenuhi</div>
        </div>
        <div class="stat bg-error/10 border-l-4 border-error">
            <div class="stat-title font-bold text-error">Menunggu Keputusan</div>
            <div class="stat-value text-error"><?php echo e($totalMenunggu); ?></div>
            <div class="stat-desc"><?php echo e($menungguUsulan); ?> usulan · <?php echo e($menungguSpk); ?> SPK · <?php echo e($menungguSwap); ?> swap</div>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        
        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <h2 class="card-title text-lg">📍 Distribusi per Kecamatan</h2>
                <div class="overflow-x-auto">
                    <table class="table table-zebra">
                        <thead>
                            <tr>
                                <th>Kecamatan</th>
                                <th class="text-right">Guru</th>
                                <th class="text-right">Sekolah</th>
                                <th class="text-right">Kebutuhan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $perKecamatan; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <tr>
                                    <td class="text-sm font-semibold"><?php echo e($r['kecamatan']); ?></td>
                                    <td class="text-sm text-right"><?php echo e($r['guru']); ?></td>
                                    <td class="text-sm text-right"><?php echo e($r['sekolah']); ?></td>
                                    <td class="text-sm text-right">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($r['kebutuhan'] > 0): ?>
                                            <span class="badge badge-warning"><?php echo e($r['kebutuhan']); ?></span>
                                        <?php else: ?>
                                            <span class="text-sm opacity-50">—</span>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </td>
                                </tr>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                <tr><td colspan="4" class="text-center text-sm opacity-50">Belum ada data kecamatan.</td></tr>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        
        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <h2 class="card-title text-lg">🎓 Distribusi per Jenjang</h2>
                <div class="overflow-x-auto">
                    <table class="table table-zebra">
                        <thead>
                            <tr>
                                <th>Jenjang</th>
                                <th class="text-right">Guru</th>
                                <th class="text-right">Sekolah</th>
                                <th class="text-right">Kebutuhan</th>
                                <th class="text-right">Defisit</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $perJenjang; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <tr>
                                    <td><span class="badge badge-outline"><?php echo e($r['jenjang']); ?></span></td>
                                    <td class="text-sm text-right"><?php echo e($r['guru']); ?></td>
                                    <td class="text-sm text-right"><?php echo e($r['sekolah']); ?></td>
                                    <td class="text-sm text-right"><?php echo e($r['kebutuhan']); ?></td>
                                    <td class="text-sm text-right">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($r['defisit'] > 0): ?>
                                            <span class="badge badge-error"><?php echo e($r['defisit']); ?></span>
                                        <?php else: ?>
                                            <span class="text-sm opacity-50">—</span>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </td>
                                </tr>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <p class="text-xs opacity-50 mt-2">Jenjang sekolah disimpulkan dari awalan nama sekolah.</p>
            </div>
        </div>
    </div>

    
    <?php
        $warna = [
            'diajukan' => 'badge-warning', 'diverifikasi' => 'badge-info',
            'rekomendasi' => 'badge-warning', 'menunggu' => 'badge-warning',
            'disetujui' => 'badge-success', 'ditolak' => 'badge-error',
            'batal_karena_konflik' => 'badge-ghost',
        ];
    ?>
    <div class="grid gap-6 lg:grid-cols-3 mt-6">
        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <h2 class="card-title text-lg">📝 Usulan Penempatan</h2>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $rekapUsulan; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status => $jml): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <div class="flex justify-between items-center py-1">
                        <span class="badge <?php echo e($warna[$status] ?? 'badge-ghost'); ?>"><?php echo e(\App\Models\UsulanPenempatan::STATUS[$status] ?? $status); ?></span>
                        <span class="font-bold"><?php echo e($jml); ?></span>
                    </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <p class="text-sm opacity-50">Belum ada usulan.</p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <h2 class="card-title text-lg">🎯 SPK Penempatan</h2>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $rekapSpk; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status => $jml): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <div class="flex justify-between items-center py-1">
                        <span class="badge <?php echo e($warna[$status] ?? 'badge-ghost'); ?>"><?php echo e(\App\Models\HasilSpk::STATUS[$status] ?? $status); ?></span>
                        <span class="font-bold"><?php echo e($jml); ?></span>
                    </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <p class="text-sm opacity-50">Belum ada hasil SPK.</p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <h2 class="card-title text-lg">🔄 Mutasi Silang</h2>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $rekapSwap; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status => $jml): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <div class="flex justify-between items-center py-1">
                        <span class="badge <?php echo e($warna[$status] ?? 'badge-ghost'); ?>"><?php echo e(\App\Models\RekomendasiSwap::STATUS[$status] ?? $status); ?></span>
                        <span class="font-bold"><?php echo e($jml); ?></span>
                    </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <p class="text-sm opacity-50">Belum ada rekomendasi swap.</p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\lrvProject\SPK-Pemetaan-Guru\resources\views/pemda/dashboard.blade.php ENDPATH**/ ?>