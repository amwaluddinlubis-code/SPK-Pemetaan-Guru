<?php $__env->startSection('judul', $aksi . ' Guru — PetaPenempatan ASN'); ?>

<?php
    // Peta field -> tab, untuk menentukan tab awal saat ada error validasi
    $petaTab = [
        'nip' => 'pribadi', 'nama' => 'pribadi', 'status_pernikahan' => 'pribadi',
        'mapel' => 'kepegawaian', 'jenjang' => 'kepegawaian',
        'status_kepegawaian' => 'kepegawaian', 'sudah_sertifikasi' => 'kepegawaian',
        'mapel_sertifikasi' => 'kepegawaian', 'sekolah_id' => 'kepegawaian',
        'kecamatan' => 'domisili', 'desa_kelurahan' => 'domisili',
        'domisili_alamat' => 'domisili', 'domisili_lat' => 'domisili', 'domisili_lng' => 'domisili',
        'sekolah_saat_ini' => 'domisili', 'sekolah_saat_ini_lat' => 'domisili',
        'sekolah_saat_ini_lng' => 'domisili',
    ];
    $tabAktif = 'pribadi';
    $hitungError = ['pribadi' => 0, 'kepegawaian' => 0, 'domisili' => 0];
    foreach ($errors->keys() as $f) {
        $t = $petaTab[$f] ?? null;
        if ($t) {
            $hitungError[$t]++;
            // Tab awal = tab dari error validasi pertama
            if ($tabAktif === 'pribadi' && array_sum($hitungError) === 1) {
                $tabAktif = $t;
            }
        }
    }
?>

<?php $__env->startSection('konten'); ?>
    <h1 class="text-2xl font-bold mb-4"><?php echo e($aksi); ?> Data Guru</h1>

    <div class="card bg-base-100 shadow-xl">
        <div class="card-body">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errors->any()): ?>
                <div class="alert alert-error">
                    <ul class="list-disc list-inside text-sm">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pesan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <li><?php echo e($pesan); ?></li>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </ul>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            
            <form method="POST"
                  action="<?php echo e($guru->exists ? route('guru.update', $guru) : route('guru.store')); ?>"
                  x-data="{ tab: '<?php echo e($tabAktif); ?>' }"
                  data-testid="form-guru-tab">
                <?php echo csrf_field(); ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($guru->exists): ?> <?php echo method_field('PUT'); ?> <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                
                <div class="tabs tabs-box mb-4" role="tablist" aria-label="Bagian form guru">
                    <button type="button" role="tab" :aria-selected="tab === 'pribadi'"
                            class="tab" :class="{ 'tab-active': tab === 'pribadi' }"
                            @click="tab = 'pribadi'">
                        1. Data Pribadi
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hitungError['pribadi'] > 0): ?>
                            <span class="badge badge-error badge-xs ml-1"><?php echo e($hitungError['pribadi']); ?></span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </button>
                    <button type="button" role="tab" :aria-selected="tab === 'kepegawaian'"
                            class="tab" :class="{ 'tab-active': tab === 'kepegawaian' }"
                            @click="tab = 'kepegawaian'">
                        2. Kepegawaian
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hitungError['kepegawaian'] > 0): ?>
                            <span class="badge badge-error badge-xs ml-1"><?php echo e($hitungError['kepegawaian']); ?></span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </button>
                    <button type="button" role="tab" :aria-selected="tab === 'domisili'"
                            class="tab" :class="{ 'tab-active': tab === 'domisili' }"
                            @click="tab = 'domisili'; $nextTick(() => Object.values(window.petaPickers || {}).forEach(m => m.invalidateSize()))">
                        3. Domisili & Penempatan
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hitungError['domisili'] > 0): ?>
                            <span class="badge badge-error badge-xs ml-1"><?php echo e($hitungError['domisili']); ?></span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </button>
                </div>

                
                <div x-show="tab === 'pribadi'" x-transition role="tabpanel">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <fieldset class="fieldset w-full">
                            <legend class="fieldset-legend font-semibold">NIP</legend>
                            <input type="text" name="nip" value="<?php echo e(old('nip', $guru->nip)); ?>" required
                                   class="input w-full" placeholder="198501012010011001">
                        </fieldset>
                        <fieldset class="fieldset w-full">
                            <legend class="fieldset-legend font-semibold">Nama Lengkap</legend>
                            <input type="text" name="nama" value="<?php echo e(old('nama', $guru->nama)); ?>" required
                                   class="input w-full" placeholder="Siti Aminah">
                        </fieldset>
                        <fieldset class="fieldset w-full">
                            <legend class="fieldset-legend font-semibold">Status Pernikahan</legend>
                            <select name="status_pernikahan" class="select w-full">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = \App\Models\Guru::STATUS_PERNIKAHAN; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $kode => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                    <option value="<?php echo e($kode); ?>"
                                        <?php echo e(old('status_pernikahan', $guru->status_pernikahan ?? 'belum_kawin') === $kode ? 'selected' : ''); ?>>
                                        <?php echo e($label); ?>

                                    </option>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            </select>
                        </fieldset>
                    </div>
                    <div class="flex justify-end mt-4">
                        <button type="button" class="btn btn-primary" @click="tab = 'kepegawaian'">Lanjut →</button>
                    </div>
                </div>

                
                <div x-show="tab === 'kepegawaian'" x-transition role="tabpanel" x-cloak>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <fieldset class="fieldset w-full">
                            <legend class="fieldset-legend font-semibold">Mata Pelajaran</legend>
                            <input type="text" name="mapel" value="<?php echo e(old('mapel', $guru->mapel)); ?>" required
                                   class="input w-full" placeholder="Matematika">
                        </fieldset>
                        <fieldset class="fieldset w-full">
                            <legend class="fieldset-legend font-semibold">Jenjang</legend>
                            <select name="jenjang" class="select w-full" required>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ['SD', 'SMP', 'SMA']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $j): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                    <option value="<?php echo e($j); ?>" <?php echo e(old('jenjang', $guru->jenjang) == $j ? 'selected' : ''); ?>>
                                        <?php echo e($j); ?>

                                    </option>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            </select>
                        </fieldset>
                        <fieldset class="fieldset w-full">
                            <legend class="fieldset-legend font-semibold">Status Kepegawaian</legend>
                            <select name="status_kepegawaian" class="select w-full">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = \App\Models\Guru::STATUS_KEPEGAWAIAN; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $kode => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                    <option value="<?php echo e($kode); ?>"
                                        <?php echo e(old('status_kepegawaian', $guru->status_kepegawaian ?? 'pns') === $kode ? 'selected' : ''); ?>>
                                        <?php echo e($label); ?>

                                    </option>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            </select>
                        </fieldset>
                        <fieldset class="fieldset w-full">
                            <legend class="fieldset-legend font-semibold">Sertifikasi Pendidik</legend>
                            <select name="sudah_sertifikasi" class="select w-full">
                                <option value="0" <?php echo e(old('sudah_sertifikasi', $guru->sudah_sertifikasi ?? 0) == 0 ? 'selected' : ''); ?>>Belum sertifikasi</option>
                                <option value="1" <?php echo e(old('sudah_sertifikasi', $guru->sudah_sertifikasi ?? 0) == 1 ? 'selected' : ''); ?>>Sudah sertifikasi</option>
                            </select>
                        </fieldset>
                        <fieldset class="fieldset w-full">
                            <legend class="fieldset-legend font-semibold">Mapel Sertifikat (bila sudah sertifikasi)</legend>
                            <input type="text" name="mapel_sertifikasi"
                                   value="<?php echo e(old('mapel_sertifikasi', $guru->mapel_sertifikasi)); ?>"
                                   class="input w-full" placeholder="cth: Matematika" maxlength="50">
                        </fieldset>
                        
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->hasRole('kabid_ptk')): ?>
                            <fieldset class="fieldset w-full">
                                <legend class="fieldset-legend font-semibold">Sekolah Tempat Bertugas</legend>
                                <select name="sekolah_id" class="select w-full">
                                    <option value="">— Belum terdata di sistem —</option>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $daftarSekolah; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                        <option value="<?php echo e($s->id); ?>"
                                            <?php echo e(old('sekolah_id', $guru->sekolah_id) == $s->id ? 'selected' : ''); ?>>
                                            <?php echo e($s->nama); ?>

                                        </option>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                </select>
                            </fieldset>
                        <?php else: ?>
                            
                            <input type="hidden" name="sekolah_id" value="<?php echo e($guru->sekolah_id); ?>">
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <div class="flex justify-between mt-4">
                        <button type="button" class="btn btn-ghost" @click="tab = 'pribadi'">← Kembali</button>
                        <button type="button" class="btn btn-primary" @click="tab = 'domisili'; $nextTick(() => Object.values(window.petaPickers || {}).forEach(m => m.invalidateSize()))">Lanjut →</button>
                    </div>
                </div>

                
                <div x-show="tab === 'domisili'" x-transition role="tabpanel" x-cloak>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <fieldset class="fieldset w-full">
                            <legend class="fieldset-legend font-semibold">Kecamatan Domisili</legend>
                            <?php
                                $kecAktif = old('kecamatan', $guru->kecamatan);
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
                            <legend class="fieldset-legend font-semibold">Desa / Kelurahan Domisili</legend>
                            <input type="text" name="desa_kelurahan"
                                   value="<?php echo e(old('desa_kelurahan', $guru->desa_kelurahan)); ?>"
                                   class="input w-full" placeholder="cth: Panyabungan Jae" maxlength="100">
                        </fieldset>
                        <fieldset class="fieldset w-full sm:col-span-2">
                            <legend class="fieldset-legend font-semibold">Alamat Domisili</legend>
                            <input type="text" name="domisili_alamat"
                                   value="<?php echo e(old('domisili_alamat', $guru->domisili_alamat)); ?>" required
                                   class="input w-full" placeholder="Jl. Mawar No.10, Kec. Sukamaju">
                        </fieldset>
                        <fieldset class="fieldset w-full">
                            <legend class="fieldset-legend font-semibold">Latitude</legend>
                            <input type="text" id="domisili_lat" name="domisili_lat"
                                   value="<?php echo e(old('domisili_lat', $guru->domisili_lat)); ?>" required
                                   class="input w-full" placeholder="0.7900">
                        </fieldset>
                        <fieldset class="fieldset w-full">
                            <legend class="fieldset-legend font-semibold">Longitude</legend>
                            <input type="text" id="domisili_lng" name="domisili_lng"
                                   value="<?php echo e(old('domisili_lng', $guru->domisili_lng)); ?>" required
                                   class="input w-full" placeholder="99.4100">
                        </fieldset>
                    </div>

                    
                    <div class="mt-3">
                        <?php echo $__env->make('komponen.peta-picker', [
                            'idPeta' => 'peta-domisili',
                            'inputLat' => 'domisili_lat',
                            'inputLng' => 'domisili_lng',
                            'lat' => (float) (old('domisili_lat', $guru->domisili_lat) ?: 0.79),
                            'lng' => (float) (old('domisili_lng', $guru->domisili_lng) ?: 99.41),
                        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    </div>
                    <p class="text-xs opacity-60 mt-1">
                        💡 Tips: buka Google Maps, klik kanan lokasi rumah → salin koordinatnya.
                    </p>

                    <div class="divider">Sekolah tempat bertugas saat ini</div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <fieldset class="fieldset w-full sm:col-span-2">
                            <legend class="fieldset-legend font-semibold">Nama Sekolah</legend>
                            <input type="text" name="sekolah_saat_ini"
                                   value="<?php echo e(old('sekolah_saat_ini', $guru->sekolah_saat_ini)); ?>" required
                                   class="input w-full" placeholder="SDN 5 Cijauh">
                        </fieldset>
                        <fieldset class="fieldset w-full">
                            <legend class="fieldset-legend font-semibold">Latitude Sekolah</legend>
                            <input type="text" id="sekolah_saat_ini_lat" name="sekolah_saat_ini_lat"
                                   value="<?php echo e(old('sekolah_saat_ini_lat', $guru->sekolah_saat_ini_lat)); ?>"
                                   class="input w-full" placeholder="0.7900">
                        </fieldset>
                        <fieldset class="fieldset w-full">
                            <legend class="fieldset-legend font-semibold">Longitude Sekolah</legend>
                            <input type="text" id="sekolah_saat_ini_lng" name="sekolah_saat_ini_lng"
                                   value="<?php echo e(old('sekolah_saat_ini_lng', $guru->sekolah_saat_ini_lng)); ?>"
                                   class="input w-full" placeholder="99.4100">
                        </fieldset>
                    </div>

                    
                    <div class="mt-3">
                        <?php echo $__env->make('komponen.peta-picker', [
                            'idPeta' => 'peta-sekolah-saat-ini',
                            'inputLat' => 'sekolah_saat_ini_lat',
                            'inputLng' => 'sekolah_saat_ini_lng',
                            'lat' => (float) (old('sekolah_saat_ini_lat', $guru->sekolah_saat_ini_lat) ?: 0.79),
                            'lng' => (float) (old('sekolah_saat_ini_lng', $guru->sekolah_saat_ini_lng) ?: 99.41),
                        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    </div>

                    <div class="flex justify-between mt-4">
                        <button type="button" class="btn btn-ghost" @click="tab = 'kepegawaian'">← Kembali</button>
                    </div>
                </div>

                <div class="flex gap-2 mt-6">
                    <button type="submit" class="btn btn-primary">💾 Simpan</button>
                    <a href="<?php echo e(route('guru.index')); ?>" class="btn btn-ghost">Batal</a>
                </div>
            </form>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\lrvProject\SPK-Pemetaan-Guru\resources\views/guru/form.blade.php ENDPATH**/ ?>