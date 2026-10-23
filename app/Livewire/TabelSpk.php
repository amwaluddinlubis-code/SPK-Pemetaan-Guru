<?php

// Komponen Livewire: tabel SPK Penempatan dengan filter status dan pagination
// — semuanya tanpa reload halaman. Data kelayakan (sisa formasi, dampak asal,
// jarak jalan) dihitung untuk halaman aktif, sama seperti controller.

namespace App\Livewire;

use App\Http\Controllers\SpkController;
use App\Livewire\Concerns\DenganPerHalaman;
use App\Models\Guru;
use App\Models\HasilSpk;
use App\Models\KebutuhanGuru;
use App\Services\RoutingJalan;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class TabelSpk extends Component
{
    use DenganPerHalaman, WithPagination;

    #[Url]
    public int $perPage = 20;

    #[Url]
    public string $status = '';

    #[Url]
    public string $q = '';

    public function updatedQ(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function resetFilter(): void
    {
        $this->reset(['status', 'q']);
        $this->resetPage();
    }

    public function render()
    {
        $user = auth()->user();
        $query = HasilSpk::with(['guru.sekolah', 'sekolahTujuan', 'diprosesOleh']);

        if ($user->role === 'guru') {
            $query->where('hasil_spks.guru_id', $user->guru_id);
        } elseif ($user->role === 'kepala_sekolah') {
            $query->whereHas('guru', fn ($q) => $q->where('sekolah_id', $user->sekolah_id));
        } elseif ($user->role === 'ketua_pgri') {
            $query->whereHas('guru', fn ($q) => $q->where('kecamatan', $user->kecamatan));
        }

        if ($this->status !== '' && array_key_exists($this->status, HasilSpk::STATUS)) {
            $query->where('status', $this->status);
        }

        if ($this->q !== '') {
            $cari = '%'.$this->q.'%';
            $query->where(fn ($q) => $q
                ->whereHas('guru', fn ($g) => $g->where('nama', 'like', $cari))
                ->orWhereHas('sekolahTujuan', fn ($s) => $s->where('nama', 'like', $cari)));
        }

        $spks = $query
            ->orderByRaw("FIELD(status, 'rekomendasi', 'disetujui', 'ditolak')")
            // Rekomendasi dengan skor hibrida tertinggi tampil lebih dulu;
            // baris lama tanpa skor (NULL) jatuh ke urutan jarak terdekat.
            ->orderByRaw('skor IS NULL')
            ->orderByDesc('skor')
            ->orderBy('jarak_km')
            ->paginate($this->perPage);

        $koleksi = $spks->getCollection();

        return view('livewire.tabel-spk', [
            'spks' => $spks,
            'ambangKm' => SpkController::AMBANG_KM,
            'sisaKuota' => KebutuhanGuru::sisaDenganAlokasi(),
            'dampakAsal' => $this->petaDampakAsal($koleksi),
            'jarakJalan' => $this->petaJarakJalan($koleksi),
        ]);
    }

    /**
     * Hitung dampak ke sekolah asal per rekomendasi (salinan logika controller
     * agar daftar Livewire mandiri; aturan: lihat SpkController::petaDampakAsal).
     */
    private function petaDampakAsal($spks): array
    {
        $peta = [];
        $cacheHitung = [];
        foreach ($spks as $spk) {
            $guru = $spk->guru;
            if (! $guru || ! $guru->sekolah_id) {
                continue;
            }
            $kunci = $guru->sekolah_id.'|'.$guru->mapel;
            if (! array_key_exists($kunci, $cacheHitung)) {
                $jumlahMapel = Guru::where('sekolah_id', $guru->sekolah_id)
                    ->where('mapel', $guru->mapel)->count();
                $kurang = (int) KebutuhanGuru::where('sekolah_id', $guru->sekolah_id)
                    ->where('mapel', $guru->mapel)->sum('jumlah');
                $cacheHitung[$kunci] = ['jumlah' => $jumlahMapel, 'kurang' => $kurang];
            }
            $info = $cacheHitung[$kunci];
            if ($info['kurang'] > 0) {
                $peta[$spk->id] = "Sekolah asal kekurangan {$info['kurang']} guru {$guru->mapel}; "
                    .'mutasi ini memperparah kekurangan.';
            } elseif ($info['jumlah'] <= 1) {
                $peta[$spk->id] = "Satu-satunya guru {$guru->mapel} di sekolah asal; "
                    .'mutasi mengosongkan mapel ini.';
            }
        }

        return $peta;
    }

    /**
     * Ambil jarak jalan untuk koleksi rekomendasi (hybrid: cache → API → fallback).
     * Hanya untuk item yang ditampilkan (paginasi), bukan seluruh kandidat.
     */
    private function petaJarakJalan($spks): array
    {
        $routing = app(RoutingJalan::class);
        $hasil = [];

        foreach ($spks as $s) {
            $guru = $s->guru;
            $tujuan = $s->sekolahTujuan;
            if (! $guru || ! $tujuan || ! $guru->domisili_lat || ! $tujuan->lat) {
                continue;
            }
            $hasil[$s->id] = $routing->jarak(
                (float) $guru->domisili_lat, (float) $guru->domisili_lng,
                (float) $tujuan->lat, (float) $tujuan->lng
            );
        }

        return $hasil;
    }
}
