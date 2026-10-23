{{-- Komponen Livewire: peta interaktif + panel rekomendasi.
     Redesign R4 (pola Esri Calcite): peta layar-penuh sebagai fokus;
     panel rekomendasi melayang ringkas di tepi kiri atas (maks 320px,
     bisa dilipat), legenda tetap di kiri bawah, kartu kandidat menyorot
     penanda sekolah di peta saat disentuh/diklik.
     Catatan: div peta memakai wire:ignore agar Livewire tidak
     me-render ulang peta setiap ada perubahan data. --}}
<div class="relative h-[calc(100dvh-11rem)] min-h-[480px] overflow-hidden rounded-box border border-base-300 bg-base-200 shadow-xl">

    {{-- ===== PETA (lapisan dasar, fokus utama) ===== --}}
    <div wire:ignore id="peta-asn" class="absolute inset-0 z-0"></div>

    {{-- ===== LEGENDA TETAP (kiri bawah, selalu terlihat) ===== --}}
    <div class="absolute bottom-3 left-3 z-10 flex items-center gap-3 rounded-box bg-base-100/90 px-3 py-2 text-xs font-semibold shadow-lg backdrop-blur">
        @unless (auth()->user()->hasRole('pemda'))
            <span class="flex items-center gap-1.5">
                <span class="inline-block h-3 w-3 rounded-full bg-blue-600 ring-2 ring-white"></span> Guru
            </span>
        @endunless
        <span class="flex items-center gap-1.5">
            <span class="inline-block h-3 w-3 rounded-full bg-green-600 ring-2 ring-white"></span> Sekolah
        </span>
        <span class="flex items-center gap-1.5">
            <span class="inline-block h-0 w-5 border-t-[3px] border-dashed border-amber-500"></span> Rute
        </span>
    </div>

    {{-- ===== PANEL REKOMENDASI (melayang, bisa dilipat) ===== --}}
    <div class="absolute left-3 top-3 z-20 w-[min(320px,calc(100%-1.5rem))]" x-data="{ buka: true }">
        <div class="card bg-base-100/95 shadow-xl backdrop-blur">
            <div class="card-body p-4">
                <div class="flex items-center justify-between gap-2">
                    <h2 class="card-title text-base"><x-ikon nama="spk" class="h-5 w-5 text-primary" /> Rekomendasi Penempatan</h2>
                    <button type="button" class="btn btn-ghost btn-xs btn-square shrink-0" x-on:click="buka = !buka" x-bind:aria-expanded="buka" aria-label="Lipat atau buka panel rekomendasi">
                        <span class="inline-flex transition-transform duration-200" x-bind:class="buka ? '' : '-rotate-90'"><x-ikon nama="panah-bawah" class="h-4 w-4" /></span>
                    </button>
                </div>

                <div x-show="buka" class="max-h-[46dvh] overflow-y-auto pr-1 lg:max-h-[calc(100dvh-19rem)]">
                    @if (auth()->user()->hasRole('pemda'))
                        {{-- Minimisasi data: eksekutif hanya melihat sebaran sekolah.
                             Data ringkasan ada di halaman Pemda & Distribusi. --}}
                        <div class="alert alert-info mt-2">
                            <span class="text-sm">
                                Mode ringkasan: peta menampilkan sebaran sekolah tanpa identitas guru.
                                Buka <a href="{{ route('pemda.dashboard') }}" class="link">Dashboard Pemda</a>
                                atau <a href="{{ route('distribusi.index') }}" class="link">Distribusi</a>
                                untuk angka agregat.
                            </span>
                        </div>
                    @else
                    <fieldset class="fieldset w-full">
                        <label class="fieldset-legend font-semibold" for="f-guruId">Pilih guru</label>
                        <select id="f-guruId" wire:model="guruId" class="select w-full"
                                @if($terkunci) disabled @endif>
                            <option value="">— Pilih guru —</option>
                            @foreach ($daftarGuru as $g)
                                <option value="{{ $g->id }}">{{ $g->nama }} — {{ $g->mapel }} ({{ $g->jenjang }})</option>
                            @endforeach
                        </select>
                        @if($terkunci)
                            <span class="text-xs opacity-60 mt-1">Terkunci: Anda hanya bisa melihat rekomendasi untuk diri sendiri.</span>
                        @endif
                        @error('guruId') <span class="text-error text-sm">{{ $message }}</span> @enderror
                    </fieldset>

                    <button wire:click="cari" wire:loading.attr="disabled"
                            class="btn btn-primary w-full mt-2">
                        <span wire:loading.remove>Cari Sekolah Terdekat</span>
                        <span wire:loading>Menghitung...</span>
                    </button>
                    @endif

                    {{-- Info guru terpilih --}}
                    @if ($infoGuru)
                        <div class="alert {{ $infoGuru['terlaluJauh'] ? 'alert-warning' : 'alert-info' }} mt-4">
                            <div class="text-sm">
                                <strong>{{ $infoGuru['nama'] }}</strong> ({{ $infoGuru['mapel'] }})<br>
                                Domisili: {{ $infoGuru['domisili'] }}<br>
                                Sekolah saat ini: <strong>{{ $infoGuru['sekolahSaatIni'] }}</strong>
                                ({{ $infoGuru['jarakSaatIni'] }} km dari rumah)
                                @if ($infoGuru['terlaluJauh']) <span class="font-semibold">— melebihi ambang jarak</span> @endif
                            </div>
                        </div>
                    @endif

                    {{-- Daftar rekomendasi --}}
                    @if ($pesanUsulan)
                        <div class="alert {{ $pesanUsulan['tipe'] === 'sukses' ? 'alert-success' : 'alert-error' }} mt-4">
                            <span class="text-sm">{{ $pesanUsulan['teks'] }}</span>
                        </div>
                    @endif
                    @if (!empty($hasil))
                        <h3 class="font-bold mt-4">{{ count($hasil) }} rekomendasi terdekat:</h3>
                        <ul class="space-y-2 mt-2">
                            @foreach ($hasil as $i => $k)
                                <li>
                                    <div class="card card-compact bg-base-200 w-full text-left transition hover:bg-base-300 cursor-pointer"
                                         onclick="terbangKe({{ $k['lat'] }}, {{ $k['lng'] }}); sorotPenanda({{ $k['id'] }})"
                                         onmouseenter="sorotPenanda({{ $k['id'] }})" onmouseleave="normalPenanda({{ $k['id'] }})">
                                        <div class="card-body">
                                            <strong>{{ $i + 1 }}. {{ $k['nama'] }}</strong>
                                            <div>
                                                <span class="badge badge-success">{{ $k['jarak'] }} km</span>
                                                <span class="badge badge-outline ml-1">butuh {{ $k['butuh'] }} guru</span>
                                            </div>
                                            @if ($k['hemat'] > 0.5)
                                                <span class="text-sm text-success">
                                                    Menghemat {{ $k['hemat'] }} km dibanding saat ini
                                                </span>
                                            @endif
                                            @if (auth()->user()->hasRole('guru', 'kepala_sekolah', 'kabid_ptk'))
                                                <button wire:click.stop="ajukanPenempatan({{ $k['id'] }})"
                                                        class="btn btn-sm btn-primary mt-1">
                                                    <x-ikon nama="usulan" class="h-4 w-4" /> Ajukan Penempatan
                                                </button>
                                            @endif
                                        </div>
                                    </div>
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
    </div>
</div>

{{-- ===== JavaScript peta (Leaflet) ===== --}}
<script>
    // Fungsi global: terbangkan peta ke koordinat (dipakai kartu kandidat)
    let petaAsn = null;
    function terbangKe(lat, lng) {
        if (petaAsn) petaAsn.flyTo([lat, lng], 15);
    }

    // Penanda sekolah terdaftar per id agar kartu kandidat bisa menyorotnya
    const penandaSekolah = {};
    function sorotPenanda(id) {
        const m = penandaSekolah[id];
        if (!m) return;
        m.setStyle({ fillColor: '#f59e0b', color: '#b45309', weight: 3 });
        m.setRadius(15);
        m.bringToFront();
    }
    function normalPenanda(id) {
        const m = penandaSekolah[id];
        if (!m) return;
        m.setStyle({ fillColor: '#16a34a', color: '#fff', weight: 2 });
        m.setRadius(11);
    }

    document.addEventListener('DOMContentLoaded', function () {
        // Inisialisasi peta (hanya sekali; wadahnya wire:ignore)
        // Titik awal: tengah Kab. Mandailing Natal (dari sebaran data sekolah)
        petaAsn = L.map('peta-asn', { zoomControl: false }).setView([0.79, 99.41], 10);
        L.control.zoom({ position: 'bottomright' }).addTo(petaAsn);
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
              .bindPopup('<b>' + g.nama + '</b><br>Mapel: ' + g.mapel + '<br>' + g.alamat);
        });

        // Tandai semua sekolah (hijau) — terdaftar per id untuk sorotan kartu
        const daftarSekolah = @json($markersSekolah);
        daftarSekolah.forEach(s => {
            const m = L.circleMarker([s.lat, s.lng], {
                radius: 11, color: '#fff', weight: 2, fillColor: '#16a34a', fillOpacity: 1
            }).addTo(petaAsn)
              .bindPopup('<b>' + s.nama + '</b><br>Butuh: ' + (s.butuh || '-'));
            if (s.id != null) penandaSekolah[s.id] = m;
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
