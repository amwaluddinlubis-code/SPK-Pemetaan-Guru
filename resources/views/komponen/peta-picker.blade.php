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

    // Terbang halus ke titik tertentu (dipakai rekomendasi wilayah & GPS).
    window.petaTerbangKe = function (idPeta, lat, lng, zoom) {
        const peta = (window.petaPickers || {})[idPeta];
        if (peta && isFinite(lat) && isFinite(lng)) {
            peta.flyTo([lat, lng], zoom || 13, { duration: 0.8 });
        }
    };

    // Rekomendasi wilayah: ambil titik tengah kecamatan/desa dari API
    // wilayah (hasil di-cache per halaman), lalu terbang ke sana.
    window.urlApiKoordinat = @js(route('wilayah.api.koordinat'));
    window._cacheKoordinatWilayah = {};
    window.petaTerbangKeWilayah = async function (idPeta, params, zoom) {
        if ((params.kecamatan ?? '') === '' && (params.desa ?? '') === '') return;
        const kunci = JSON.stringify(params);
        let hasil = window._cacheKoordinatWilayah[kunci];
        if (!hasil) {
            try {
                const r = await fetch(window.urlApiKoordinat + '?' + new URLSearchParams(params).toString(), { headers: { 'Accept': 'application/json' } });
                if (!r.ok) return;
                hasil = await r.json();
                window._cacheKoordinatWilayah[kunci] = hasil;
            } catch (e) { return; }
        }
        const target = params.desa ? hasil.desa : hasil.kecamatan;
        if (target) window.petaTerbangKe(idPeta, target.lat, target.lng, zoom);
    };

    // Desa dipilih dari daftar: pakai koordinat yang ikut di opsi bila ada,
    // jika tidak ambil dari API berdasarkan kode desa.
    window.petaTerbangKeDesa = function (idPeta, detail) {
        if (detail.lat != null && detail.lng != null) {
            window.petaTerbangKe(idPeta, detail.lat, detail.lng, 15);
        } else if (detail.kode) {
            window.petaTerbangKeWilayah(idPeta, { desa: detail.kode }, 15);
        }
    };

    // Tombol GPS: isi koordinat dari lokasi perangkat pengguna.
    // Membutuhkan konteks aman (HTTPS/localhost) + izin lokasi browser.
    window.petaPakaiGps = function (idPeta, idLat, idLng) {
        const status = document.getElementById(idPeta + '-gps-status');
        const tulis = (pesan) => { if (status) status.textContent = pesan; };
        if (!navigator.geolocation) {
            tulis('Perangkat/browser tidak mendukung GPS.');
            return;
        }
        tulis('Mengambil lokasi GPS…');
        navigator.geolocation.getCurrentPosition(function (pos) {
            const lat = pos.coords.latitude, lng = pos.coords.longitude;
            const elLat = document.getElementById(idLat);
            const elLng = document.getElementById(idLng);
            if (elLat && elLng) {
                elLat.value = lat.toFixed(7);
                elLng.value = lng.toFixed(7);
                elLat.dispatchEvent(new Event('change'));
                elLng.dispatchEvent(new Event('change'));
            }
            window.petaTerbangKe(idPeta, lat, lng, 16);
            tulis('Lokasi GPS diterapkan (akurasi ±' + Math.round(pos.coords.accuracy) + ' m). Geser pin bila perlu.');
        }, function (err) {
            tulis(err.code === 1
                ? 'Izin lokasi ditolak — izinkan akses lokasi di browser lalu coba lagi.'
                : 'Lokasi GPS tidak didapat — pastikan GPS aktif dan halaman dibuka via HTTPS.');
        }, { enableHighAccuracy: true, timeout: 15000 });
    };
    </script>
@endPushOnce

<div id="{{ $idPeta }}" style="height: 280px;" class="rounded-box z-0 border border-base-300 bg-base-200 shadow-sm"></div>
<p class="mt-1 flex items-center gap-1 text-xs opacity-60"><x-ikon nama="info" class="h-3.5 w-3.5 shrink-0" /> Klik peta atau geser pin untuk mengisi koordinat otomatis (atau ketik manual).</p>
@if ($gps ?? false)
    <div class="mt-2 flex flex-wrap items-center gap-2">
        <button type="button" class="btn btn-sm btn-outline btn-primary"
                onclick="petaPakaiGps('{{ $idPeta }}', '{{ $inputLat }}', '{{ $inputLng }}')"><x-ikon nama="lokasi" class="h-4 w-4" /> Pakai Lokasi GPS Saya</button>
        <span id="{{ $idPeta }}-gps-status" class="text-xs opacity-70"></span>
    </div>
@endif
@push('skrip')
<script>
document.addEventListener('DOMContentLoaded', function () {
    inisiasiPetaPicker(@json($idPeta), @json($inputLat), @json($inputLng), @json($lat), @json($lng));
});
</script>
@endPush
