<?php
// Komponen Livewire: PetaRekomendasi
// Menampilkan peta + panel rekomendasi penempatan yang interaktif
// (tanpa reload halaman). Dipakai di halaman Dashboard.

namespace App\Livewire;

use App\Models\Guru;
use App\Models\Sekolah;
use App\Models\UsulanPenempatan;
use Livewire\Component;

class PetaRekomendasi extends Component
{
    // Pilihan guru dari dropdown (id guru, atau '' jika belum pilih)
    public $guruId = '';

    // Hasil rekomendasi: array berisi info sekolah + jarak + kebutuhan
    public $hasil = [];

    // Info guru terpilih: nama, mapel, domisili, jarak saat ini
    public $infoGuru = null;

    // Data marker untuk peta (dikirim ke JavaScript sebagai JSON)
    public $markersGuru = [];
    public $markersSekolah = [];

    // Jika true, dropdown guru dikunci (untuk role 'guru': hanya dirinya sendiri)
    public $terkunci = false;

    // Pesan hasil pengajuan usulan dari panel rekomendasi ['tipe' => ..., 'teks' => ...]
    public $pesanUsulan = null;

    /**
     * Dijalankan sekali saat komponen pertama dimuat.
     * Siapkan data marker untuk peta.
     */
    public function mount(): void
    {
        // Role guru: dropdown dikunci ke datanya sendiri (scope pribadi)
        if (auth()->check() && auth()->user()->role === 'guru') {
            $this->guruId = (string) auth()->user()->guru_id;
            $this->terkunci = true;
        }

        $this->markersGuru = $this->daftarGuruPeta();
        $this->markersSekolah = $this->daftarSekolahPeta();
    }

    /**
     * Marker guru: role guru hanya melihat dirinya sendiri di peta.
     */
    private function daftarGuruPeta(): array
    {
        $query = Guru::query();
        if (auth()->check() && auth()->user()->role === 'guru') {
            $query->where('id', auth()->user()->guru_id);
        }
        return $query->get()->map(fn ($g) => [
            'id'     => $g->id,
            'nama'   => $g->nama,
            'mapel'  => $g->mapel,
            'alamat' => $g->domisili_alamat,
            'lat'    => (float) $g->domisili_lat,
            'lng'    => (float) $g->domisili_lng,
        ])->toArray();
    }

    private function daftarSekolahPeta(): array
    {
        return Sekolah::with('kebutuhan')->get()->map(fn ($s) => [
            'id'      => $s->id,
            'nama'    => $s->nama,
            'alamat'  => $s->alamat,
            'lat'     => (float) $s->lat,
            'lng'     => (float) $s->lng,
            'butuh'   => $s->kebutuhan->map(fn ($k) => $k->mapel . ' (' . $k->jumlah . ')')->join(', '),
        ])->toArray();
    }

    /**
     * Dijalankan saat tombol "Cari Sekolah Terdekat" diklik.
     * Logika inti: sekolah yang BUTUH mapel guru, diurutkan dari TERDEKAT.
     */
    public function cari(): void
    {
        // Pengaman: role guru tidak bisa mengintip guru lain walau memanipulasi request
        if (auth()->check() && auth()->user()->role === 'guru') {
            $this->guruId = (string) auth()->user()->guru_id;
        }

        $this->validate(['guruId' => ['required', 'exists:guru,id']],
            ['guruId.required' => 'Pilih dulu gurunya.']);

        $guru = Guru::findOrFail($this->guruId);

        // Jarak domisili → sekolah tempat bertugas SAAT INI (ini masalahnya)
        $jarakSaatIni = $guru->jarakKe(
            (float) $guru->sekolah_saat_ini_lat,
            (float) $guru->sekolah_saat_ini_lng
        );

        $this->infoGuru = [
            'nama'           => $guru->nama,
            'mapel'          => $guru->mapel,
            'domisili'       => $guru->domisili_alamat,
            'sekolahSaatIni' => $guru->sekolah_saat_ini,
            'jarakSaatIni'   => round($jarakSaatIni, 1),
            'terlaluJauh'    => $jarakSaatIni > 5,
            'lat'            => (float) $guru->domisili_lat,
            'lng'            => (float) $guru->domisili_lng,
        ];

        // Kumpulkan kandidat: sekolah yang butuh mapel guru ini
        $kandidat = [];
        foreach (Sekolah::with('kebutuhan')->get() as $sekolah) {
            $butuh = $sekolah->butuhMapel($guru->mapel);
            if ($butuh <= 0) {
                continue; // lewati: tidak butuh mapel ini
            }
            $jarak = $guru->jarakKe((float) $sekolah->lat, (float) $sekolah->lng);
            $kandidat[] = [
                'id'      => $sekolah->id,
                'nama'    => $sekolah->nama,
                'alamat'  => $sekolah->alamat,
                'lat'     => (float) $sekolah->lat,
                'lng'     => (float) $sekolah->lng,
                'jarak'   => round($jarak, 1),
                'butuh'   => $butuh,
                'hemat'   => round($jarakSaatIni - $jarak, 1),
            ];
        }

        // Urutkan dari jarak terdekat, ambil 5 teratas
        usort($kandidat, fn ($a, $b) => $a['jarak'] <=> $b['jarak']);
        $this->hasil = array_slice($kandidat, 0, 5);

        // Kirim event ke JavaScript: gambar garis domisili guru → 3 sekolah teratas
        $this->dispatch('rute-diperbarui',
            guru: [
                'lat' => (float) $guru->domisili_lat,
                'lng' => (float) $guru->domisili_lng,
            ],
            rute: array_map(fn ($k) => [
                'lat' => $k['lat'], 'lng' => $k['lng'],
            ], array_slice($this->hasil, 0, 3)));
    }

    public function render()
    {
        // Dropdown guru: role guru hanya melihat dirinya sendiri
        $queryGuru = Guru::orderBy('nama');
        if (auth()->check() && auth()->user()->role === 'guru') {
            $queryGuru->where('id', auth()->user()->guru_id);
        }

        return view('livewire.peta-rekomendasi', [
            'daftarGuru' => $queryGuru->get(['id', 'nama', 'mapel', 'jenjang']),
        ]);
    }

    /**
     * Buat usulan penempatan langsung dari hasil rekomendasi.
     * Boleh: guru (diri sendiri), kepala_sekolah (guru sekolahnya), kabid_ptk.
     */
    public function ajukanPenempatan(int $sekolahId): void
    {
        $this->pesanUsulan = null;
        $user = auth()->user();

        if (! $user || ! $user->hasRole('guru', 'kepala_sekolah', 'kabid_ptk')) {
            abort(403);
        }
        if (empty($this->guruId)) {
            $this->pesanUsulan = ['tipe' => 'error', 'teks' => 'Pilih dulu gurunya sebelum mengajukan.'];
            return;
        }

        $guru = Guru::findOrFail($this->guruId);
        // Kunci scope seperti method cari()
        if ($user->role === 'guru') {
            $guru = Guru::findOrFail($user->guru_id);
        } elseif ($user->role === 'kepala_sekolah' && (int) $guru->sekolah_id !== (int) $user->sekolah_id) {
            $this->pesanUsulan = ['tipe' => 'error', 'teks' => 'Anda hanya boleh mengajukan guru di sekolah Anda.'];
            return;
        }

        $sekolah = Sekolah::with('kebutuhan')->findOrFail($sekolahId);
        if ($sekolah->butuhMapel($guru->mapel) <= 0) {
            $this->pesanUsulan = ['tipe' => 'error', 'teks' => 'Sekolah ini tidak membutuhkan mapel ' . $guru->mapel . '.'];
            return;
        }
        if (UsulanPenempatan::where('guru_id', $guru->id)
                ->whereIn('status', UsulanPenempatan::AKTIF)->exists()) {
            $this->pesanUsulan = ['tipe' => 'error', 'teks' => 'Guru ini masih punya usulan yang berjalan.'];
            return;
        }

        UsulanPenempatan::create([
            'guru_id' => $guru->id, 'sekolah_id' => $sekolah->id,
            'alasan' => "Diajukan dari rekomendasi peta: jarak {$guru->domisili_alamat} ke {$sekolah->nama} "
                . round($guru->jarakKe((float) $sekolah->lat, (float) $sekolah->lng), 1) . ' km.',
            'status' => 'diajukan', 'diajukan_oleh' => $user->id,
        ]);

        $this->pesanUsulan = ['tipe' => 'sukses',
            'teks' => "Usulan {$guru->nama} → {$sekolah->nama} terkirim. Pantau di menu Usulan."];
    }
}
