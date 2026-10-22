
<div class="grid gap-6 lg:grid-cols-5">

    
    <div class="card bg-base-100 shadow-xl lg:col-span-3">
        <div class="card-body p-3">
            <div wire:ignore id="peta-asn" style="height: 480px;" class="rounded-xl z-0"></div>
            <div class="flex gap-4 text-sm mt-2 px-1">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if (! (auth()->user()->hasRole('pemda'))): ?>
                    <span class="flex items-center gap-1">
                        <span class="inline-block w-3.5 h-3.5 rounded-full bg-blue-600"></span> Guru
                    </span>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <span class="flex items-center gap-1">
                    <span class="inline-block w-3.5 h-3.5 rounded-full bg-green-600"></span> Sekolah
                </span>
            </div>
        </div>
    </div>

    
    <div class="card bg-base-100 shadow-xl lg:col-span-2">
        <div class="card-body">
            <h2 class="card-title">🎯 Rekomendasi Penempatan</h2>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->hasRole('pemda')): ?>
                
                <div class="alert alert-info mt-2">
                    <span class="text-sm">
                        Mode ringkasan: peta menampilkan sebaran sekolah tanpa identitas guru.
                        Buka <a href="<?php echo e(route('pemda.dashboard')); ?>" class="link">Dashboard Pemda</a>
                        atau <a href="<?php echo e(route('distribusi.index')); ?>" class="link">Distribusi</a>
                        untuk angka agregat.
                    </span>
                </div>
            <?php else: ?>
            <fieldset class="fieldset w-full">
                <legend class="fieldset-legend font-semibold">Pilih guru</legend>
                <select wire:model="guruId" class="select w-full"
                        <?php if($terkunci): ?> disabled <?php endif; ?>>
                    <option value="">— Pilih guru —</option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $daftarGuru; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $g): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <option value="<?php echo e($g->id); ?>"><?php echo e($g->nama); ?> — <?php echo e($g->mapel); ?> (<?php echo e($g->jenjang); ?>)</option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </select>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($terkunci): ?>
                    <span class="text-xs opacity-60 mt-1">🔒 Terkunci: Anda hanya bisa melihat rekomendasi untuk diri sendiri.</span>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['guruId'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-error text-sm"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </fieldset>

            <button wire:click="cari" wire:loading.attr="disabled"
                    class="btn btn-primary w-full mt-2">
                <span wire:loading.remove>Cari Sekolah Terdekat</span>
                <span wire:loading>Menghitung...</span>
            </button>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($infoGuru): ?>
                <div class="alert <?php echo e($infoGuru['terlaluJauh'] ? 'alert-warning' : 'alert-info'); ?> mt-4">
                    <div class="text-sm">
                        <strong><?php echo e($infoGuru['nama']); ?></strong> (<?php echo e($infoGuru['mapel']); ?>)<br>
                        🏠 <?php echo e($infoGuru['domisili']); ?><br>
                        🏫 Saat ini: <strong><?php echo e($infoGuru['sekolahSaatIni']); ?></strong>
                        (<?php echo e($infoGuru['jarakSaatIni']); ?> km dari rumah)
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($infoGuru['terlaluJauh']): ?> ⚠️ <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pesanUsulan): ?>
                <div class="alert <?php echo e($pesanUsulan['tipe'] === 'sukses' ? 'alert-success' : 'alert-error'); ?> mt-4">
                    <span class="text-sm"><?php echo e($pesanUsulan['teks']); ?></span>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($hasil)): ?>
                <h3 class="font-bold mt-4">🏆 <?php echo e(count($hasil)); ?> rekomendasi terdekat:</h3>
                <ul class="space-y-2 mt-2">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $hasil; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $k): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <li>
                            <div class="card card-compact bg-base-200 w-full text-left hover:bg-base-300 cursor-pointer"
                                 onclick="terbangKe(<?php echo e($k['lat']); ?>, <?php echo e($k['lng']); ?>)">
                                <div class="card-body">
                                    <strong><?php echo e($i + 1); ?>. <?php echo e($k['nama']); ?></strong>
                                    <div>
                                        <span class="badge badge-success">📍 <?php echo e($k['jarak']); ?> km</span>
                                        <span class="badge badge-outline ml-1">butuh <?php echo e($k['butuh']); ?> guru</span>
                                    </div>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($k['hemat'] > 0.5): ?>
                                        <span class="text-sm text-success">
                                            🎉 Menghemat <?php echo e($k['hemat']); ?> km dibanding saat ini
                                        </span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->hasRole('guru', 'kepala_sekolah', 'kabid_ptk')): ?>
                                        <button wire:click.stop="ajukanPenempatan(<?php echo e($k['id']); ?>)"
                                                class="btn btn-sm btn-primary mt-1">
                                            📝 Ajukan Penempatan
                                        </button>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            </div>
                        </li>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </ul>
            <?php elseif($infoGuru): ?>
                <p class="opacity-60 text-sm mt-4">
                    Tidak ada sekolah yang membutuhkan mapel <?php echo e($infoGuru['mapel']); ?> saat ini.
                </p>
            <?php else: ?>
                <p class="opacity-60 text-sm mt-4">
                    Pilih guru lalu klik tombol di atas untuk melihat rekomendasi.
                </p>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>
</div>


<script>
    // Fungsi global: terbangkan peta ke koordinat (dipakai tombol rekomendasi)
    let petaAsn = null;
    function terbangKe(lat, lng) {
        if (petaAsn) petaAsn.flyTo([lat, lng], 15);
    }

    document.addEventListener('DOMContentLoaded', function () {
        // Inisialisasi peta (hanya sekali; wadahnya wire:ignore)
        // Titik awal: tengah Kab. Mandailing Natal (dari sebaran data sekolah)
        petaAsn = L.map('peta-asn').setView([0.79, 99.41], 10);
        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap'
        }).addTo(petaAsn);

        // Tandai semua guru (biru) — data dari server via JSON
        const daftarGuru = <?php echo json_encode($markersGuru, 15, 512) ?>;
        daftarGuru.forEach(g => {
            L.circleMarker([g.lat, g.lng], {
                radius: 9, color: '#fff', weight: 2, fillColor: '#2563eb', fillOpacity: 1
            }).addTo(petaAsn)
              .bindPopup('<b>👩‍🏫 ' + g.nama + '</b><br>Mapel: ' + g.mapel + '<br>' + g.alamat);
        });

        // Tandai semua sekolah (hijau)
        const daftarSekolah = <?php echo json_encode($markersSekolah, 15, 512) ?>;
        daftarSekolah.forEach(s => {
            L.circleMarker([s.lat, s.lng], {
                radius: 11, color: '#fff', weight: 2, fillColor: '#16a34a', fillOpacity: 1
            }).addTo(petaAsn)
              .bindPopup('<b>🏫 ' + s.nama + '</b><br>Butuh: ' + (s.butuh || '-'));
        });

        // Garis rute domisili guru → sekolah rekomendasi (diperbarui tiap pencarian)
        let garisRute = [];
        document.addEventListener('livewire:init', () => {
            // Menerima event dari komponen Livewire (method cari())
            Livewire.on('rute-diperbarui', (data) => {
                // Hapus garis lama
                garisRute.forEach(gr => petaAsn.removeLayer(gr));
                garisRute = [];
                const g = data.guru; // {lat, lng} domisili guru terpilih
                (data.rute || []).forEach(t => {
                    const garis = L.polyline(
                        [[g.lat, g.lng], [t.lat, t.lng]],
                        { color: '#f59e0b', weight: 3, dashArray: '8, 8' }
                    ).addTo(petaAsn);
                    garisRute.push(garis);
                });
                // Arahkan peta ke domisili guru
                petaAsn.flyTo([g.lat, g.lng], 14);
            });
        });
    });
</script>
<?php /**PATH D:\lrvProject\SPK-Pemetaan-Guru\resources\views/livewire/peta-rekomendasi.blade.php ENDPATH**/ ?>