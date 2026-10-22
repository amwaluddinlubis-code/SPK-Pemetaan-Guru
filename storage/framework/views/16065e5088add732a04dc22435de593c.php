

<?php if (! $__env->hasRenderedOnce('6afe82ca-79b2-43e8-a744-76b64012cb6c')): $__env->markAsRenderedOnce('6afe82ca-79b2-43e8-a744-76b64012cb6c');
$__env->startPush('kepala'); ?>
    
    <?php echo app('Illuminate\Foundation\Vite')('resources/js/peta.js'); ?>
    <script>
    // Inisialisasi satu pemilih koordinat:
    // klik peta / geser pin → input lat/lng terisi; ketik manual → pin ikut pindah.
    function inisiasiPetaPicker(idPeta, idLat, idLng, latAwal, lngAwal) {
        const peta = L.map(idPeta).setView([latAwal, lngAwal], 13);
        // Daftarkan instance agar tab/step bisa memanggil invalidateSize()
        // saat panel peta ditampilkan (Leaflet butuh ini jika di-init tersembunyi).
        window.petaPickers = window.petaPickers || {};
        window.petaPickers[idPeta] = peta;
        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap'
        }).addTo(peta);

        const marker = L.marker([latAwal, lngAwal], { draggable: true }).addTo(peta);
        const elLat = document.getElementById(idLat);
        const elLng = document.getElementById(idLng);

        function isiInput(lat, lng) {
            elLat.value = lat.toFixed(7);
            elLng.value = lng.toFixed(7);
        }
        peta.on('click', function (e) {
            marker.setLatLng(e.latlng);
            isiInput(e.latlng.lat, e.latlng.lng);
        });
        marker.on('dragend', function () {
            const p = marker.getLatLng();
            isiInput(p.lat, p.lng);
        });
        function dariInput() {
            const la = parseFloat(elLat.value);
            const ln = parseFloat(elLng.value);
            if (!isNaN(la) && !isNaN(ln)) {
                marker.setLatLng([la, ln]);
                peta.setView([la, ln], peta.getZoom());
            }
        }
        elLat.addEventListener('change', dariInput);
        elLng.addEventListener('change', dariInput);
    }
    </script>
<?php $__env->stopPush(); endif; ?>

<div id="<?php echo e($idPeta); ?>" style="height: 280px;" class="rounded-xl z-0 border border-base-300"></div>
<p class="text-xs opacity-60 mt-1">🖱️ Klik peta atau geser pin untuk mengisi koordinat otomatis (atau ketik manual).</p>
<?php $__env->startPush('skrip'); ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    inisiasiPetaPicker(<?php echo json_encode($idPeta, 15, 512) ?>, <?php echo json_encode($inputLat, 15, 512) ?>, <?php echo json_encode($inputLng, 15, 512) ?>, <?php echo json_encode($lat, 15, 512) ?>, <?php echo json_encode($lng, 15, 512) ?>);
});
</script>
<?php $__env->stopPush(); ?>
<?php /**PATH D:\lrvProject\SPK-Pemetaan-Guru\resources\views/komponen/peta-picker.blade.php ENDPATH**/ ?>