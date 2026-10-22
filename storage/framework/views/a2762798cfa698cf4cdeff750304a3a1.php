<?php $__env->startSection('judul', 'Simulasi Kebijakan — PetaPenempatan ASN'); ?>

<?php $__env->startSection('konten'); ?>
<div class="mb-4">
    <h1 class="text-2xl font-bold">Simulasi Kebijakan “What-If”</h1>
    <p class="text-sm opacity-70 mt-1">Uji skenario parameter tanpa mengubah data operasional. Hasil bersifat hipotetis, bukan keputusan resmi.</p>
</div>

<form method="POST" action="<?php echo e(route('simulasi.jalankan')); ?>" class="card bg-base-100 shadow p-6 max-w-xl">
    <?php echo csrf_field(); ?>
    <div class="grid gap-4">
        <div>
            <label class="label"><span class="label-text font-semibold">Ambang jarak (km) *</span></label>
            <input type="number" name="ambang_km" value="<?php echo e(old('ambang_km', 15)); ?>" min="1" max="500" step="0.5" class="input w-full" required>
            <p class="text-xs opacity-60 mt-1">Hanya guru yang jaraknya melebihi ambang ini yang dipertimbangkan.</p>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['ambang_km'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-error text-xs mt-1"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
        <div>
            <label class="label"><span class="label-text font-semibold">Mapel</span></label>
            <select name="mapel" class="select w-full">
                <option value="">Semua mapel</option>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $daftarMapel; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <option value="<?php echo e($m); ?>" <?php if(old('mapel') === $m): echo 'selected'; endif; ?>><?php echo e($m); ?></option>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </select>
        </div>
        <div>
            <label class="label"><span class="label-text font-semibold">Kecamatan domisili</span></label>
            <select name="kecamatan" class="select w-full">
                <option value="">Semua kecamatan</option>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $daftarKecamatan; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <option value="<?php echo e($k); ?>" <?php if(old('kecamatan') === $k): echo 'selected'; endif; ?>><?php echo e($k); ?></option>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </select>
        </div>
        <div>
            <label class="label cursor-pointer justify-start gap-3">
                <input type="checkbox" name="sertakan_3t" value="1" class="checkbox" <?php if(old('sertakan_3t')): echo 'checked'; endif; ?>>
                <span class="label-text">Sertakan guru dari sekolah 3T sebagai kandidat</span>
            </label>
        </div>
        <button type="submit" class="btn btn-primary">Jalankan Simulasi</button>
    </div>
</form>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\lrvProject\SPK-Pemetaan-Guru\resources\views/simulasi/index.blade.php ENDPATH**/ ?>