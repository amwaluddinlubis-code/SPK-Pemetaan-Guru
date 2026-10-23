{{-- Tabel Data Sekolah (Livewire): pencarian, filter, sorting, pagination tanpa reload. --}}
<div>
<x-halaman judul="Data Sekolah" ikon="sekolah">
    <x-slot:aksi>
        @if (auth()->user()->hasRole('kabid_ptk', 'kadis'))
            @if (auth()->user()->hasRole('kabid_ptk'))
                <a href="{{ route('sekolah.import') }}" class="btn btn-secondary"><x-ikon nama="unggah" class="h-5 w-5" /> Import Excel</a>
                <a href="{{ route('sekolah.import-rombel') }}" class="btn btn-secondary"><x-ikon nama="unggah" class="h-5 w-5" /> Import Rombel</a>
                <form method="POST" action="{{ route('sekolah.analisis.massal') }}" class="inline-flex"
                      onsubmit="return confirm('Terapkan analisis kebutuhan guru ke SEMUA sekolah yang rombelnya terisi? Baris kebutuhan lama tiap sekolah akan diganti hasil hitungan rombel × JP.')">
                    @csrf
                    <button type="submit" class="btn btn-secondary"><x-ikon nama="spk" class="h-5 w-5" /> Terapkan Analisis Massal</button>
                </form>
            @endif
            <a href="{{ route('sekolah.export', request()->only(['q', 'kecamatan'])) }}" class="btn btn-accent"><x-ikon nama="unduh" class="h-5 w-5" /> Export Excel</a>
            <a href="{{ route('sekolah.cetak', request()->only(['q', 'kecamatan'])) }}" target="_blank" class="btn btn-neutral"><x-ikon nama="cetak" class="h-5 w-5" /> Cetak/PDF</a>
            @if (auth()->user()->hasRole('kabid_ptk'))
                <form method="POST" action="{{ route('sekolah.verifikasi-massal') }}" class="inline-flex"
                      onsubmit="return confirm('Tandai SEMUA sekolah berkoordinat yang belum terverifikasi sebagai TERVERIFIKASI? Hanya lakukan setelah titik-titik koordinat dikonfirmasi. Sekolah yang belum terverifikasi tidak dapat menjadi tujuan rekomendasi SPK.')">
                    @csrf
                    <input type="hidden" name="koordinat_sumber" value="konfirmasi_massal">
                    <button type="submit" class="btn btn-secondary"><x-ikon nama="centang" class="h-5 w-5" /> Verifikasi Koordinat Massal</button>
                </form>
                <a href="{{ route('sekolah.create') }}" class="btn btn-primary"><x-ikon nama="tambah" class="h-5 w-5" /> Tambah Sekolah</a>
            @endif
        @endif
    </x-slot:aksi>
</x-halaman>

{{-- Rincian baris yang gagal saat import terakhir --}}
@if (session('import_gagal'))
    <x-alert tipe="warning" class="mb-4">
        <div class="text-sm">
            <strong><x-ikon nama="peringatan" class="h-5 w-5 inline align-[-3px]" /> Baris yang gagal diimpor:</strong>
            <ul class="list-disc list-inside mt-1">
                @foreach (session('import_gagal') as $pesan)
                    <li>{{ $pesan }}</li>
                @endforeach
            </ul>
        </div>
    </x-alert>
@endif

<x-chip-konteks jenis="sekolah" />

<div>
    <x-kartu class="mb-4">
        <div class="card-body py-3">
            <div class="flex flex-wrap gap-2">
                <input aria-label="Cari nama, NPSN, alamat…" type="text" wire:model.live.debounce.500ms="q"
                       placeholder="Cari nama, NPSN, alamat…"
                       class="input flex-1 min-w-52">
                @if (! auth()->user()->hasRole('kepala_sekolah', 'ketua_pgri'))
                    <x-searchable-select wire-model="kecamatan" :selected="$kecamatan"
                        :options="collect($daftarKecamatan)->map(fn ($k) => ['value' => $k, 'label' => $k])->all()"
                        placeholder="— Semua Kecamatan —" class="w-full max-w-xs" />
                @endif
                @if ($q !== '' || $kecamatan !== '')
                    <button wire:click="resetFilter" class="btn btn-ghost">Reset</button>
                @endif
            </div>
            @if ($q !== '' || $kecamatan !== '')
                <p class="text-sm opacity-60 mt-2">
                    Menampilkan {{ $daftarSekolah->total() }} hasil
                    @if ($q !== '') untuk “{{ $q }}” @endif
                    @if ($kecamatan !== '') di Kec. {{ $kecamatan }} @endif
                </p>
            @endif
        </div>
    </x-kartu>

    <x-kartu class="overflow-hidden">
        <div class="card-body p-0 overflow-x-auto min-w-0">
            <table class="table table-zebra tabel-baku">
                <thead>
                    <tr>
                        <th><button wire:click="urutkan('nama')" class="link py-1">Nama / NPSN {{ $sort === 'nama' ? ($arah === 'asc' ? '▲' : '▼') : '' }}</button></th>
                        <th>Alamat</th>
                        <th><button wire:click="urutkan('kecamatan')" class="link py-1">Kecamatan {{ $sort === 'kecamatan' ? ($arah === 'asc' ? '▲' : '▼') : '' }}</button></th>
                        <th>Desa/Kelurahan</th>
                        <th>Kategori Daerah</th>
                        <th>Kebutuhan Guru</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($daftarSekolah as $s)
                        <tr>
                            <td>
                                <strong>{{ $s->nama }}</strong><br>
                                <span class="text-sm opacity-60">NPSN: {{ $s->npsn }}</span><br>
                                @if ($s->lat === null || $s->lng === null)
                                    <x-lencana-status nada="netral" teks="Tanpa koordinat" />
                                @elseif ($s->koordinatTerverifikasi())
                                    <x-lencana-status nada="sukses" teks="Terverifikasi" :judul="'Diverifikasi '.$s->koordinat_terverifikasi_pada?->format('d/m/Y').' oleh '.($s->verifikatorKoordinat?->name ?? 'petugas')" />
                                @else
                                    <x-lencana-status nada="peringatan" teks="Belum terverifikasi" judul="Koordinat estimasi — sekolah ini belum dapat menjadi tujuan rekomendasi SPK" />
                                @endif
                            </td>
                            <td class="text-sm">{{ $s->alamat }}</td>
                            <td class="text-sm">{{ $s->kecamatan ?? '—' }}</td>
                            <td class="text-sm">{{ $s->desa_kelurahan ?? '—' }}</td>
                            <td>
                                @if ($s->isDaerah3T())
                                    <x-lencana-status nada="peringatan" :teks="$s->labelKategoriDaerah()" judul="Rem pemerataan: guru di sekolah ini dilindungi dari mutasi keluar lewat SPK" />
                                @else
                                    <span class="text-sm opacity-60">{{ $s->labelKategoriDaerah() }}</span>
                                @endif
                            </td>
                            <td>
                                @forelse ($s->kebutuhan as $k)
                                    <span class="badge badge-outline mr-1 mb-1 whitespace-nowrap">{{ $k->mapel }} ({{ $k->jumlah }})</span>
                                @empty
                                    <span class="text-sm opacity-50">—</span>
                                @endforelse
                            </td>
                            <td class="text-right whitespace-nowrap">
                                <a href="{{ route('sekolah.analisis', $s) }}" class="btn btn-sm btn-info gap-1"><x-ikon nama="kualitas" class="h-4 w-4" /> Analisis</a>
                                @if (auth()->user()->hasRole('kepala_sekolah', 'kabid_ptk'))
                                    <a href="{{ route('sekolah.edit', $s) }}" class="btn btn-sm btn-warning gap-1"><x-ikon nama="pensil" class="h-4 w-4" /> Ubah</a>
                                @endif
                                @if (auth()->user()->hasRole('kabid_ptk'))
                                    <x-hapus :aksi="route('sekolah.destroy', $s)" tanya="Hapus {{ $s->nama }} beserta kebutuhan gurunya?" />
                                @endif
                            </td>
                        </tr>
                    @empty
                        @if ($konteksKosong)
                            <x-kosong pesan="Belum ada data sekolah di wilayah {{ $namaPemdaKonteks }}. Data di luar konteks Pemda disembunyikan — atur konteks lewat menu Sistem → Pengaturan Pemda." kolom="7" />
                        @else
                            <x-kosong pesan="Tidak ada data sekolah yang cocok." kolom="7" />
                        @endif
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-kartu>

    <div class="mt-4 flex flex-wrap items-center justify-between gap-2">
        <x-info-halaman :data="$daftarSekolah" />
        <div class="flex flex-wrap items-center gap-3">
            {{ $daftarSekolah->links() }}
            <x-per-halaman />
        </div>
    </div>
</div>

</div>
