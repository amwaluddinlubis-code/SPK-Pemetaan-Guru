{{-- Pemilih koordinat klik-peta (Leaflet + OpenStreetMap).
     Dipakai di form guru & sekolah agar lat/lng terisi otomatis dan akurat.
     Parameter:
       $idPeta   → id unik div peta (mis. 'peta-domisili')
       $inputLat → id input latitude (mis. 'domisili_lat')
       $inputLng → id input longitude (mis. 'domisili_lng')
       $lat, $lng → posisi awal pin (angka) --}}

@pushOnce('kepala')
    {{-- Leaflet dibundle Vite — tidak tergantung CDN --}}
    @vite('resources/js/peta.js')
    <script>
    // Inisialisasi satu pemilih koordinat:
    // klik peta / geser pin → input lat/lng terisi; ketik manual → pin ikut pindah.
    function inisiasiPetaPicker(idPeta, idLat, idLng, latAwal, lngAwal) {
        const peta = L.map(idPeta).setView([latAwal, lngAwal], 13);
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
@endPushOnce

<div id="{{ $idPeta }}" style="height: 280px;" class="rounded-xl z-0 border border-base-300"></div>
<p class="text-xs opacity-60 mt-1">🖱️ Klik peta atau geser pin untuk mengisi koordinat otomatis (atau ketik manual).</p>
@push('skrip')
<script>
document.addEventListener('DOMContentLoaded', function () {
    inisiasiPetaPicker(@json($idPeta), @json($inputLat), @json($inputLng), @json($lat), @json($lng));
});
</script>
@endPush
