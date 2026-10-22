
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
                       placeholder="Cari nama, NIP, mapel, domisili, sekolah…"
                       class="input flex-1 min-w-52">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->hasRole('kabid_ptk', 'kadis')): ?>
                    <select wire:model.live="kecamatan" class="select max-w-xs">
                        <option value="">— Semua Kecamatan —</option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $daftarKecamatan; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $kec): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <option value="<?php echo e($kec); ?>"><?php echo e($kec); ?></option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </select>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($q !== '' || $kecamatan !== ''): ?>
                    <button wire:click="resetFilter" class="btn btn-ghost">Reset</button>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($q !== '' || $kecamatan !== ''): ?>
                <p class="text-sm opacity-60 mt-2">
                    Menampilkan <?php echo e($daftarGuru->total()); ?> hasil
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($q !== ''): ?> untuk “<?php echo e($q); ?>” <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($kecamatan !== ''): ?> di Kec. <?php echo e($kecamatan); ?> <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </p>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.kartu','data' => ['class' => 'overflow-hidden hidden md:block','dataTestid' => 'tabel-guru-desktop']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('kartu'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'overflow-hidden hidden md:block','data-testid' => 'tabel-guru-desktop']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

        <div class="card-body p-0 overflow-x-auto min-w-0">
            <table class="table table-zebra">
                <thead>
                    <tr>
                        <th><button wire:click="urutkan('nama')" class="link">Nama / NIP <?php echo e($sort === 'nama' ? ($arah === 'asc' ? '▲' : '▼') : ''); ?></button></th>
                        <th><button wire:click="urutkan('mapel')" class="link">Mapel <?php echo e($sort === 'mapel' ? ($arah === 'asc' ? '▲' : '▼') : ''); ?></button></th>
                        <th>Status</th>
                        <th>Domisili</th>
                        <th><button wire:click="urutkan('kecamatan')" class="link">Kecamatan <?php echo e($sort === 'kecamatan' ? ($arah === 'asc' ? '▲' : '▼') : ''); ?></button></th>
                        <th>Desa/Kelurahan</th>
                        <th>Sekolah Saat Ini</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $daftarGuru; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $g): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <tr>
                            <td>
                                <strong><?php echo e($g->nama); ?></strong><br>
                                <span class="text-sm opacity-60"><?php echo e($g->nipTersamar()); ?></span>
                            </td>
                            <td>
                                <span class="badge badge-outline"><?php echo e($g->mapel); ?></span>
                                <span class="text-sm opacity-60"><?php echo e($g->jenjang); ?></span>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($g->sudah_sertifikasi): ?>
                                    <span class="badge badge-success badge-sm" title="Sertifikasi: <?php echo e($g->mapel_sertifikasi ?? '—'); ?><?php echo e($g->linear() ? '' : ' — TIDAK LINEAR'); ?>">✓ Serdik</span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! $g->linear()): ?>
                                    <span class="badge badge-error badge-sm" title="Mapel diampu (<?php echo e($g->mapel); ?>) tidak sesuai sertifikat (<?php echo e($g->mapel_sertifikasi); ?>)">Tidak linear</span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                            <td>
                                <span class="badge badge-neutral badge-sm"><?php echo e($g->labelStatusKepegawaian()); ?></span>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($g->berkeluarga()): ?>
                                    <span class="badge badge-info" title="Prioritas keluarga dalam rekomendasi">👨‍👩‍👧 <?php echo e($g->labelStatusPernikahan()); ?></span>
                                <?php else: ?>
                                    <span class="badge badge-ghost"><?php echo e($g->labelStatusPernikahan()); ?></span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                            <td class="text-sm"><?php echo e($g->domisili_alamat); ?></td>
                            <td class="text-sm"><?php echo e($g->kecamatan ?? '—'); ?></td>
                            <td class="text-sm"><?php echo e($g->desa_kelurahan ?? '—'); ?></td>
                            <td class="text-sm"><?php echo e($g->sekolah_saat_ini); ?></td>
                            <td class="text-right whitespace-nowrap">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->hasRole('guru', 'kabid_ptk', 'kepala_sekolah')): ?>
                                    <a href="<?php echo e(route('guru.edit', $g)); ?>" class="btn btn-sm btn-warning">Ubah</a>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->hasRole('kabid_ptk', 'kepala_sekolah')): ?>
                                    <?php if (isset($component)) { $__componentOriginal25dd61e9978e3411b00daff915baae09 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal25dd61e9978e3411b00daff915baae09 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.hapus','data' => ['aksi' => route('guru.destroy', $g),'tanya' => 'Hapus data '.e($g->nama).'?']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('hapus'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['aksi' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('guru.destroy', $g)),'tanya' => 'Hapus data '.e($g->nama).'?']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal25dd61e9978e3411b00daff915baae09)): ?>
<?php $attributes = $__attributesOriginal25dd61e9978e3411b00daff915baae09; ?>
<?php unset($__attributesOriginal25dd61e9978e3411b00daff915baae09); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal25dd61e9978e3411b00daff915baae09)): ?>
<?php $component = $__componentOriginal25dd61e9978e3411b00daff915baae09; ?>
<?php unset($__componentOriginal25dd61e9978e3411b00daff915baae09); ?>
<?php endif; ?>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                        </tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <?php if (isset($component)) { $__componentOriginal2774db502eccf9ab4eb7044e67aef150 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2774db502eccf9ab4eb7044e67aef150 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.kosong','data' => ['pesan' => 'Tidak ada data guru yang cocok.','kolom' => '8']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('kosong'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['pesan' => 'Tidak ada data guru yang cocok.','kolom' => '8']); ?>
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

    
    <div class="grid gap-3 md:hidden" data-testid="kartu-guru-mobile">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $daftarGuru; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $g): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
            <div class="card bg-base-100 shadow">
                <div class="card-body p-4">
                    <div class="flex justify-between items-start gap-2">
                        <div>
                            <h3 class="font-bold"><?php echo e($g->nama); ?></h3>
                            <p class="text-sm opacity-60"><?php echo e($g->nipTersamar()); ?></p>
                        </div>
                        <span class="badge badge-neutral badge-sm"><?php echo e($g->labelStatusKepegawaian()); ?></span>
                    </div>
                    <div class="flex flex-wrap gap-1 mt-2">
                        <span class="badge badge-outline badge-sm"><?php echo e($g->mapel); ?> (<?php echo e($g->jenjang); ?>)</span>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($g->sudah_sertifikasi): ?>
                            <span class="badge badge-success badge-sm">✓ Serdik</span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! $g->linear()): ?>
                            <span class="badge badge-error badge-sm">Tidak linear</span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($g->berkeluarga()): ?>
                            <span class="badge badge-info badge-sm"><?php echo e($g->labelStatusPernikahan()); ?></span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <p class="text-sm mt-2 opacity-70"><?php echo e($g->sekolah_saat_ini); ?></p>
                    <p class="text-sm opacity-60"><?php echo e($g->kecamatan ?? '—'); ?><?php echo e($g->desa_kelurahan ? ', '.$g->desa_kelurahan : ''); ?></p>
                    <div class="card-actions justify-end mt-2">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->hasRole('guru', 'kabid_ptk', 'kepala_sekolah')): ?>
                            <a href="<?php echo e(route('guru.edit', $g)); ?>" class="btn btn-sm btn-warning">Ubah</a>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->hasRole('kabid_ptk', 'kepala_sekolah')): ?>
                            <?php if (isset($component)) { $__componentOriginal25dd61e9978e3411b00daff915baae09 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal25dd61e9978e3411b00daff915baae09 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.hapus','data' => ['aksi' => route('guru.destroy', $g),'tanya' => 'Hapus data '.e($g->nama).'?']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('hapus'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['aksi' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('guru.destroy', $g)),'tanya' => 'Hapus data '.e($g->nama).'?']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal25dd61e9978e3411b00daff915baae09)): ?>
<?php $attributes = $__attributesOriginal25dd61e9978e3411b00daff915baae09; ?>
<?php unset($__attributesOriginal25dd61e9978e3411b00daff915baae09); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal25dd61e9978e3411b00daff915baae09)): ?>
<?php $component = $__componentOriginal25dd61e9978e3411b00daff915baae09; ?>
<?php unset($__componentOriginal25dd61e9978e3411b00daff915baae09); ?>
<?php endif; ?>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
            </div>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            <?php if (isset($component)) { $__componentOriginal2774db502eccf9ab4eb7044e67aef150 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2774db502eccf9ab4eb7044e67aef150 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.kosong','data' => ['pesan' => 'Tidak ada data guru yang cocok.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('kosong'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['pesan' => 'Tidak ada data guru yang cocok.']); ?>
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
    </div>

    <div class="mt-4 flex flex-wrap items-center justify-between gap-2">
        <?php if (isset($component)) { $__componentOriginal42cc9ebc762ae7b6f15beaabdb1d3427 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal42cc9ebc762ae7b6f15beaabdb1d3427 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.info-halaman','data' => ['data' => $daftarGuru]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('info-halaman'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['data' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($daftarGuru)]); ?>
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
        <?php echo e($daftarGuru->links()); ?>

    </div>
</div>
<?php /**PATH D:\lrvProject\SPK-Pemetaan-Guru\resources\views/livewire/tabel-guru.blade.php ENDPATH**/ ?>