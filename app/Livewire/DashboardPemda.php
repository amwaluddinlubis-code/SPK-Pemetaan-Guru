<?php

// Komponen Livewire halaman penuh: Dashboard Eksekutif Pemda.
// Read-only, agregat se-kabupaten: rekap guru, sekolah, kebutuhan,
// dan status usulan/SPK/swap. Tidak ada aksi ubah data di sini.

namespace App\Livewire;

use App\Models\Guru;
use App\Models\HasilSpk;
use App\Models\KebutuhanGuru;
use App\Models\RekomendasiSwap;
use App\Models\Sekolah;
use App\Models\UsulanPenempatan;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Eksekutif Pemda — PetaPenempatan ASN')]
class DashboardPemda extends Component
{
    public function render()
    {
        // --- Kartu ringkasan ---
        $totalGuru = Guru::count();
        $totalSekolah = Sekolah::count();
        $totalKebutuhan = (int) KebutuhanGuru::sum('jumlah');

        // Yang masih menunggu keputusan (perlu perhatian pimpinan)
        $menungguUsulan = UsulanPenempatan::whereIn('status', UsulanPenempatan::AKTIF)->count();
        $menungguSpk = HasilSpk::where('status', 'rekomendasi')->count();
        $menungguSwap = RekomendasiSwap::where('status', 'menunggu')->count();
        $totalMenunggu = $menungguUsulan + $menungguSpk + $menungguSwap;

        // Konteks kartu KPI (Redesign R2): komposisi nyata di balik angka
        $labelStatus = Guru::STATUS_KEPEGAWAIAN;
        $guruPerStatus = Guru::query()
            ->selectRaw('status_kepegawaian, count(*) as jml')
            ->groupBy('status_kepegawaian')
            ->pluck('jml', 'status_kepegawaian')
            ->map(fn ($jml, $status) => ($labelStatus[$status] ?? $status).' '.number_format((int) $jml))
            ->implode(' · ');
        $sekolahPerJenjang = $this->konteksSekolahPerJenjang();
        $kebutuhanSekolah = KebutuhanGuru::where('jumlah_target', '>', 0)->distinct()->count('sekolah_id');
        $kebutuhanMapel = KebutuhanGuru::where('jumlah_target', '>', 0)->distinct()->count('mapel');
        $kebutuhanCakupan = 'di '.number_format($kebutuhanSekolah).' sekolah · '.number_format($kebutuhanMapel).' mapel';

        // --- Distribusi per kecamatan ---
        $daftarKecamatan = Sekolah::whereNotNull('kecamatan')
            ->distinct()->orderBy('kecamatan')->pluck('kecamatan');

        $perKecamatan = $daftarKecamatan->map(function ($kec) {
            $idSekolah = Sekolah::where('kecamatan', $kec)->pluck('id');

            return [
                'kecamatan' => $kec,
                'guru' => Guru::where('kecamatan', $kec)->count(),
                'sekolah' => $idSekolah->count(),
                'kebutuhan' => (int) KebutuhanGuru::whereIn('sekolah_id', $idSekolah)->sum('jumlah'),
            ];
        });

        // --- Distribusi per jenjang: guru terdata vs kebutuhan ---
        // Jenjang sekolah disimpulkan dari awalan nama (data referensi Kemendikdasmen).
        $perJenjang = collect(['SD', 'SMP', 'SMA'])->map(function ($jenjang) {
            $idSekolah = Sekolah::where('nama', 'like', $jenjang.'%')->pluck('id');
            $jmlGuru = Guru::where('jenjang', $jenjang)->count();
            $jmlKebutuhan = (int) KebutuhanGuru::whereIn('sekolah_id', $idSekolah)->sum('jumlah');

            return [
                'jenjang' => $jenjang,
                'guru' => $jmlGuru,
                'sekolah' => $idSekolah->count(),
                'kebutuhan' => $jmlKebutuhan,
                // Defisit = kebutuhan yang belum terpenuhi
                'defisit' => max(0, $jmlKebutuhan - $jmlGuru),
            ];
        });

        // --- Rekap status usulan / SPK / swap ---
        $rekapUsulan = UsulanPenempatan::select('status', DB::raw('count(*) as jumlah'))
            ->groupBy('status')->pluck('jumlah', 'status');
        $rekapSpk = HasilSpk::select('status', DB::raw('count(*) as jumlah'))
            ->groupBy('status')->pluck('jumlah', 'status');
        $rekapSwap = RekomendasiSwap::select('status', DB::raw('count(*) as jumlah'))
            ->groupBy('status')->pluck('jumlah', 'status');

        return view('livewire.dashboard-pemda', compact(
            'totalGuru', 'totalSekolah', 'totalKebutuhan',
            'menungguUsulan', 'menungguSpk', 'menungguSwap', 'totalMenunggu',
            'guruPerStatus', 'sekolahPerJenjang', 'kebutuhanCakupan',
            'perKecamatan', 'perJenjang',
            'rekapUsulan', 'rekapSpk', 'rekapSwap'
        ));
    }

    /** Ringkasan sekolah per jenjang untuk konteks KPI, mis. "SD 400 · SMP 81". */
    private function konteksSekolahPerJenjang(): string
    {
        $urutan = ['TK' => 0, 'SD' => 1, 'SMP' => 2, 'SMA' => 3, 'SMK' => 4];
        $perJenjang = Sekolah::query()
            ->selectRaw('jenjang, count(*) as jml')
            ->groupBy('jenjang')
            ->pluck('jml', 'jenjang');

        $ringkasan = $perJenjang
            ->sortBy(fn ($jml, $jenjang) => $urutan[$jenjang] ?? 9)
            ->map(fn ($jml, $jenjang) => $jenjang.' '.number_format((int) $jml))
            ->implode(' · ');

        return $ringkasan !== '' ? $ringkasan : 'terdata di sistem';
    }
}
