<?php $__env->startSection('judul', $aksi . ' Pengguna — PetaPenempatan ASN'); ?>

<?php $__env->startSection('konten'); ?>
    <h1 class="text-2xl font-bold mb-4"><?php echo e($aksi); ?> Pengguna</h1>

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

            <form method="POST"
                  action="<?php echo e($user->exists ? route('pengguna.update', $user) : route('pengguna.store')); ?>">
                <?php echo csrf_field(); ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($user->exists): ?> <?php echo method_field('PUT'); ?> <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <div class="grid gap-4 sm:grid-cols-2">
                    <fieldset class="fieldset w-full">
                        <legend class="fieldset-legend font-semibold">Nama</legend>
                        <input type="text" name="name" value="<?php echo e(old('name', $user->name)); ?>" required
                               class="input w-full" placeholder="Nama lengkap">
                    </fieldset>
                    <fieldset class="fieldset w-full">
                        <legend class="fieldset-legend font-semibold">Email</legend>
                        <input type="email" name="email" value="<?php echo e(old('email', $user->email)); ?>" required
                               class="input w-full" placeholder="nama@contoh.id">
                    </fieldset>
                    <fieldset class="fieldset w-full">
                        <legend class="fieldset-legend font-semibold">
                            Password <?php echo e($user->exists ? '(kosongkan jika tidak diganti)' : ''); ?>

                        </legend>
                        <input type="password" name="password" <?php echo e($user->exists ? '' : 'required'); ?>

                               class="input w-full" placeholder="Minimal 6 karakter">
                    </fieldset>
                    <fieldset class="fieldset w-full">
                        <legend class="fieldset-legend font-semibold">Peran</legend>
                        <select name="role" id="pilih-role" class="select w-full" required>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = \App\Models\User::ROLES; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $nilai => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <option value="<?php echo e($nilai); ?>"
                                    <?php echo e(old('role', $user->role) == $nilai ? 'selected' : ''); ?>>
                                    <?php echo e($label); ?>

                                </option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </select>
                    </fieldset>
                </div>

                <div class="divider">Scope (sesuai peran)</div>
                <p class="text-sm opacity-60 mb-4">
                    Isi sesuai peran yang dipilih. Dikosongkan = tidak dibatasi.
                </p>

                <div class="grid gap-4 sm:grid-cols-2">
                    
                    <fieldset class="fieldset w-full" id="field-guru">
                        <legend class="fieldset-legend font-semibold">Terhubung ke Guru</legend>
                        <select name="guru_id" class="select w-full">
                            <option value="">— Tidak ada —</option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $daftarGuru; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $g): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <option value="<?php echo e($g->id); ?>"
                                    <?php echo e(old('guru_id', $user->guru_id) == $g->id ? 'selected' : ''); ?>>
                                    <?php echo e($g->nama); ?> (<?php echo e($g->nip); ?>)
                                </option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </select>
                    </fieldset>

                    
                    <fieldset class="fieldset w-full" id="field-sekolah">
                        <legend class="fieldset-legend font-semibold">Terhubung ke Sekolah</legend>
                        <select name="sekolah_id" class="select w-full">
                            <option value="">— Tidak ada —</option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $daftarSekolah; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <option value="<?php echo e($s->id); ?>"
                                    <?php echo e(old('sekolah_id', $user->sekolah_id) == $s->id ? 'selected' : ''); ?>>
                                    <?php echo e($s->nama); ?>

                                </option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </select>
                    </fieldset>

                    
                    <fieldset class="fieldset w-full sm:col-span-2" id="field-kecamatan">
                        <legend class="fieldset-legend font-semibold">Kecamatan (untuk Ketua PGRI)</legend>
                        <?php $kecAktif = old('kecamatan', $user->kecamatan); ?>
                        <select name="kecamatan" class="select w-full">
                            <option value="">— Pilih Kecamatan —</option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $daftarKecamatan; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $kec): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <option value="<?php echo e($kec); ?>" <?php echo e($kecAktif == $kec ? 'selected' : ''); ?>>
                                    <?php echo e($kec); ?>

                                </option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($kecAktif && ! $daftarKecamatan->contains($kecAktif)): ?>
                                <option value="<?php echo e($kecAktif); ?>" selected><?php echo e($kecAktif); ?></option>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </select>
                    </fieldset>
                </div>

                <div class="flex gap-2 mt-6">
                    <button type="submit" class="btn btn-primary">💾 Simpan</button>
                    <a href="<?php echo e(route('pengguna.index')); ?>" class="btn btn-ghost">Batal</a>
                </div>
            </form>
        </div>
    </div>

    <script>
    // Tampilkan hanya field scope yang relevan dengan peran terpilih
    (function () {
        const pilih = document.getElementById('pilih-role');
        const field = {
            guru: document.getElementById('field-guru'),
            kepala_sekolah: document.getElementById('field-sekolah'),
            ketua_pgri: document.getElementById('field-kecamatan'),
        };
        function sesuaikan() {
            const peran = pilih.value;
            field.guru.style.display = peran === 'guru' ? '' : 'none';
            field.kepala_sekolah.style.display = peran === 'kepala_sekolah' ? '' : 'none';
            field.ketua_pgri.style.display = peran === 'ketua_pgri' ? '' : 'none';
        }
        pilih.addEventListener('change', sesuaikan);
        sesuaikan();
    })();
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\lrvProject\SPK-Pemetaan-Guru\resources\views/pengguna/form.blade.php ENDPATH**/ ?>