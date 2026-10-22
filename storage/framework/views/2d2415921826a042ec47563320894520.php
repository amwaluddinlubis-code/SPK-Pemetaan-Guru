<?php $__env->startSection('judul', 'Profil Saya'); ?>

<?php $__env->startSection('konten'); ?>
<div class="max-w-2xl mx-auto space-y-6">
    <div class="card bg-base-100 shadow">
        <div class="card-body">
            <h2 class="card-title">Profil Saya</h2>
            <table class="table table-sm">
                <tr><th class="w-40">Nama</th><td><?php echo e($pengguna->name); ?></td></tr>
                <tr><th>Email</th><td><?php echo e($pengguna->email); ?></td></tr>
                <tr><th>Peran</th><td><span class="badge badge-primary"><?php echo e(\App\Models\User::ROLES[$pengguna->role] ?? $pengguna->role); ?></span></td></tr>
            </table>
        </div>
    </div>

    <div class="card bg-base-100 shadow">
        <div class="card-body">
            <h2 class="card-title">Ganti Password</h2>
            <p class="text-sm opacity-70">Password minimal 8 karakter. Setelah diganti, gunakan password baru saat login berikutnya.</p>
            <form method="POST" action="<?php echo e(route('profil.password')); ?>" class="space-y-4 mt-2">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>
                <fieldset class="fieldset w-full">
                    <legend class="fieldset-legend font-semibold">Password Lama</legend>
                    <input type="password" name="password_lama" required class="input w-full" autocomplete="current-password">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['password_lama'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="text-error text-sm"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </fieldset>
                <fieldset class="fieldset w-full">
                    <legend class="fieldset-legend font-semibold">Password Baru</legend>
                    <input type="password" name="password" required class="input w-full" autocomplete="new-password">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="text-error text-sm"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </fieldset>
                <fieldset class="fieldset w-full">
                    <legend class="fieldset-legend font-semibold">Ulangi Password Baru</legend>
                    <input type="password" name="password_confirmation" required class="input w-full" autocomplete="new-password">
                </fieldset>
                <button type="submit" class="btn btn-primary">Simpan Password Baru</button>
            </form>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\lrvProject\SPK-Pemetaan-Guru\resources\views/profil/show.blade.php ENDPATH**/ ?>