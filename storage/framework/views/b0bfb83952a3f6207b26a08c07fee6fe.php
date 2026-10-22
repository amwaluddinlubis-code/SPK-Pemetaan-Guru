<?php $__env->startSection('judul', $aksi . ' Sekolah — PetaPenempatan ASN'); ?>

<?php $__env->startSection('konten'); ?>
    <h1 class="text-2xl font-bold mb-4"><?php echo e($aksi); ?> Data Sekolah</h1>

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
                  action="<?php echo e($sekolah->exists ? route('sekolah.update', $sekolah) : route('sekolah.store')); ?>">
                <?php echo csrf_field(); ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($sekolah->exists): ?> <?php echo method_field('PUT'); ?> <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <div class="grid gap-4 sm:grid-cols-2">
                    <fieldset class="fieldset w-full">
                        <legend class="fieldset-legend font-semibold">NPSN</legend>
                        <input type="text" name="npsn" value="<?php echo e(old('npsn', $sekolah->npsn)); ?>" required
                               class="input w-full" placeholder="20100001">
                    </fieldset>
                    <fieldset class="fieldset w-full">
                        <legend class="fieldset-legend font-semibold">Nama Sekolah</legend>
                        <input type="text" name="nama" value="<?php echo e(old('nama', $sekolah->nama)); ?>" required
                               class="input w-full" placeholder="SDN 1 Sukamaju">
                    </fieldset>
                    <fieldset class="fieldset w-full">
                        <legend class="fieldset-legend font-semibold">Jenjang</legend>
                        <select name="jenjang" class="select w-full">
                            <option value="">— Otomatis dari nama —</option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ['SD', 'SMP']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $j): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <option value="<?php echo e($j); ?>"
                                    <?php echo e(old('jenjang', $sekolah->jenjang) === $j ? 'selected' : ''); ?>><?php echo e($j); ?></option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </select>
                    </fieldset>
                    <fieldset class="fieldset w-full">
                        <legend class="fieldset-legend font-semibold">Jumlah Rombel</legend>
                        <input type="number" name="rombel" value="<?php echo e(old('rombel', $sekolah->rombel)); ?>" min="0" max="300"
                               class="input w-full" placeholder="mis. 6">
                        <legend class="fieldset-legend-alt">Dari Dapodik (kolom Rombel di CSV ekspor kecamatan) — bahan hitung analisis kebutuhan.</legend>
                    </fieldset>
                    <fieldset class="fieldset w-full sm:col-span-2">
                        <legend class="fieldset-legend font-semibold">Alamat</legend>
                        <input type="text" name="alamat" value="<?php echo e(old('alamat', $sekolah->alamat)); ?>" required
                               class="input w-full" placeholder="Jl. Pendidikan No.1">
                    </fieldset>
                    <fieldset class="fieldset w-full sm:col-span-2">
                        <legend class="fieldset-legend font-semibold">Kecamatan</legend>
                        <?php
                            $kecAktif = old('kecamatan', $sekolah->kecamatan);
                            $opsiKec = $daftarKecamatan->contains($kecAktif) || empty($kecAktif)
                                ? $daftarKecamatan
                                : $daftarKecamatan->concat([$kecAktif])->sort()->values();
                        ?>
                        <select name="kecamatan" class="select w-full">
                            <option value="">— Pilih Kecamatan —</option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $opsiKec; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $kec): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <option value="<?php echo e($kec); ?>" <?php echo e($kecAktif == $kec ? 'selected' : ''); ?>>
                                    <?php echo e($kec); ?>

                                </option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </select>
                    </fieldset>
                    <fieldset class="fieldset w-full">
                        <legend class="fieldset-legend font-semibold">Desa / Kelurahan</legend>
                        <input type="text" name="desa_kelurahan"
                               value="<?php echo e(old('desa_kelurahan', $sekolah->desa_kelurahan)); ?>"
                               class="input w-full" placeholder="cth: Panyabungan Jae" maxlength="100">
                    </fieldset>
                    <fieldset class="fieldset w-full">
                        <legend class="fieldset-legend font-semibold">Kategori Daerah</legend>
                        <select name="kategori_daerah" class="select w-full">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = \App\Models\Sekolah::KATEGORI_DAERAH; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $kode => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <option value="<?php echo e($kode); ?>"
                                    <?php echo e(old('kategori_daerah', $sekolah->kategori_daerah ?? 'biasa') === $kode ? 'selected' : ''); ?>>
                                    <?php echo e($label); ?>

                                </option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </select>
                        <legend class="fieldset-legend-alt">Guru di sekolah Terpencil/3T dilindungi dari mutasi keluar lewat SPK (rem pemerataan).</legend>
                    </fieldset>
                    <fieldset class="fieldset w-full">
                        <legend class="fieldset-legend font-semibold">Latitude</legend>
                        <input type="text" id="lat" name="lat" value="<?php echo e(old('lat', $sekolah->lat)); ?>" required
                               class="input w-full" placeholder="0.7900">
                    </fieldset>
                    <fieldset class="fieldset w-full">
                        <legend class="fieldset-legend font-semibold">Longitude</legend>
                        <input type="text" id="lng" name="lng" value="<?php echo e(old('lng', $sekolah->lng)); ?>" required
                               class="input w-full" placeholder="99.4100">
                    </fieldset>
                </div>

                
                <div class="mt-3">
                    <?php echo $__env->make('komponen.peta-picker', [
                        'idPeta' => 'peta-sekolah',
                        'inputLat' => 'lat',
                        'inputLng' => 'lng',
                        'lat' => (float) (old('lat', $sekolah->lat) ?: 0.79),
                        'lng' => (float) (old('lng', $sekolah->lng) ?: 99.41),
                    ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                </div>

                <div class="divider">Kebutuhan Guru per Mata Pelajaran</div>

                
                <?php
                    // Data awal: dari input lama (jika validasi gagal) atau dari database
                    $awal = old('kebutuhan', $sekolah->kebutuhan->map(fn ($k) => [
                        'mapel' => $k->mapel, 'jumlah' => $k->jumlah,
                    ])->toArray());
                    if (empty($awal)) $awal = [['mapel' => '', 'jumlah' => 1]];
                ?>
                <div x-data='{ "baris": <?php echo json_encode(array_values($awal), 15, 512) ?> }'>
                    <template x-for="(b, i) in baris" :key="i">
                        <div class="flex gap-2 mb-2">
                            <input type="text" :name="'kebutuhan[' + i + '][mapel]'" x-model="b.mapel"
                                   placeholder="Mata pelajaran (mis. Matematika)"
                                   class="input flex-1">
                            <input type="number" :name="'kebutuhan[' + i + '][jumlah]'" x-model="b.jumlah"
                                   min="1" placeholder="Jumlah" class="input w-28">
                            <button type="button" @click="baris.splice(i, 1)"
                                    class="btn btn-error btn-outline" title="Hapus baris">✕</button>
                        </div>
                    </template>
                    <button type="button" @click="baris.push({mapel: '', jumlah: 1})"
                            class="btn btn-sm btn-secondary mt-1">+ Tambah Mapel</button>
                </div>

                <div class="flex gap-2 mt-6">
                    <button type="submit" class="btn btn-primary">💾 Simpan</button>
                    <a href="<?php echo e(route('sekolah.index')); ?>" class="btn btn-ghost">Batal</a>
                </div>
            </form>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\lrvProject\SPK-Pemetaan-Guru\resources\views/sekolah/form.blade.php ENDPATH**/ ?>