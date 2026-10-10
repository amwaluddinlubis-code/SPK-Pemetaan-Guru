{{-- Komponen Livewire: peta interaktif + panel rekomendasi.
     Catatan: div peta memakai wire:ignore agar Livewire tidak
     me-render ulang peta setiap ada perubahan data. --}}
<div class="grid gap-6 lg:grid-cols-5">

    {{-- ===== PETA (kiri, lebar) ===== --}}
    <div class="card bg-base-100 shadow-xl lg:col-span-3">
        <div class="card-body p-3">
            <div wire:ignore id="peta-asn" style="height: 480px;" class="rounded-xl z-0"></div>
            <div class="flex gap-4 text-sm mt-2 px-1">
                <span class="flex items-center gap-1">
                    <span class="inline-block w-3.5 h-3.5 rounded-full bg-blue-600"></span> Guru
                </span>
                <span class="flex items-center gap-1">
                    <span class="inline-block w-3.5 h-3.5 rounded-full bg-green-600"></span> Sekolah
                </span>
            </div>
        </div>
    </div>

    {{-- ===== PANEL REKOMENDASI (kanan) ===== --}}
    <div class="card bg-base-100 shadow-xl lg:col-span-2">
        <div class="card-body">
            <h2 class="card-title">🎯 Rekomendasi Penempatan</h2>

            <label class="form-control w-full">
                <span class="label-text font-semibold">Pilih guru</span>
                <select wire:model="guruId" class="select select-bordered w-full"
                        @if($terkunci) disabled @endif>
                    <option value="">— Pilih guru —</option>
                    @foreach ($daftarGuru as $g)
                        <option value="{{ $g->id }}">{{ $g->nama }} — {{ $g->mapel }} ({{ $g->jenjang }})</option>
                    @endforeach
                </select>
                @if($terkunci)
                    <span class="text-xs opacity-60 mt-1">🔒 Terkunci: Anda hanya bisa melihat rekomendasi untuk diri sendiri.</span>
                @endif
                @error('guruId') <span class="text-error text-sm">{{ $message }}</span> @enderror
            </label>

            <button wire:click="cari" wire:loading.attr="disabled"
                    class="btn btn-primary w-full mt-2">
                <span wire:loading.remove>Cari Sekolah Terdekat</span>
                <span wire:loading>Menghitung...</span>
            </button>

            {{-- Info guru terpilih --}}
            @if ($infoGuru)
                <div class="alert {{ $infoGuru['terlaluJauh'] ? 'alert-warning' : 'alert-info' }} mt-4">
                    <div class="text-sm">
                        <strong>{{ $infoGuru['nama'] }}</strong> ({{ $infoGuru['mapel'] }})<br>
                        🏠 {{ $infoGuru['domisili'] }}<br>
                        🏫 Saat ini: <strong>{{ $infoGuru['sekolahSaatIni'] }}</strong>
                        ({{ $infoGuru['jarakSaatIni'] }} km dari rumah)
                        @if ($infoGuru['terlaluJauh']) ⚠️ @endif
                    </div>
                </div>
            @endif

            {{-- Daftar rekomendasi --}}
            @if (!empty($hasil))
                <h3 class="font-bold mt-4">🏆 {{ count($hasil) }} rekomendasi terdekat:</h3>
                <ul class="space-y-2 mt-2">
                    @foreach ($hasil as $i => $k)
                        <li>
                            <button class="card card-compact bg-base-200 w-full text-left hover:bg-base-300"
                                    onclick="terbangKe({{ $k['lat'] }}, {{ $k['lng'] }})">
                                <div class="card-body">
                                    <strong>{{ $i + 1 }}. {{ $k['nama'] }}</strong>
                                    <div>
                                        <span class="badge badge-success">📍 {{ $k['jarak'] }} km</span>
                                        <span class="badge badge-outline ml-1">butuh {{ $k['butuh'] }} guru</span>
                                    </div>
                                    @if ($k['hemat'] > 0.5)
                                        <span class="text-sm text-success">
                                            🎉 Menghemat {{ $k['hemat'] }} km dibanding saat ini
                                        </span>
                                    @endif
                                </div>
                            </button>
                        </li>
                    @endforeach
                </ul>
            @elseif ($infoGuru)
                <p class="opacity-60 text-sm mt-4">
                    Tidak ada sekolah yang membutuhkan mapel {{ $infoGuru['mapel'] }} saat ini.
                </p>
            @else
                <p class="opacity-60 text-sm mt-4">
                    Pilih guru lalu klik tombol di atas untuk melihat rekomendasi.
                </p>
            @endif
        </div>
    </div>
</div>

{{-- ===== JavaScript peta (Leaflet) ===== --}}
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
        const daftarGuru = @json($markersGuru);
        daftarGuru.forEach(g => {
            L.circleMarker([g.lat, g.lng], {
                radius: 9, color: '#fff', weight: 2, fillColor: '#2563eb', fillOpacity: 1
            }).addTo(petaAsn)
              .bindPopup('<b>👩‍🏫 ' + g.nama + '</b><br>Mapel: ' + g.mapel + '<br>' + g.alamat);
        });

        // Tandai semua sekolah (hijau)
        const daftarSekolah = @json($markersSekolah);
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
