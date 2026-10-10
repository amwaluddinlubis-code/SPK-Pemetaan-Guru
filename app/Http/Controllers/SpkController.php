<?php
// Controller SPK Penempatan (Skenario 1): merekomendasikan guru yang jarak
// tugasnya melebihi ambang batas ke sekolah terdekat yang kekurangan
// formasi untuk mata pelajarannya. Jarak dihitung langsung di MySQL
// dengan rumus Haversine agar cepat.
//
// Alur peran (mirip Swap & Usulan):
//   - Generate rekomendasi : kabid_ptk
//   - Setujui              : kadis
//   - Tolak                : kabid_ptk, kadis
//   - Lihat daftar         : semua peran

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\HasilSpk;
use App\Models\Sekolah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SpkController extends Controller
{
    // Ambang batas toleransi jarak (km): hanya guru yang jarak tugasnya
    // melebihi nilai ini yang diproses SPK
    public const AMBANG_KM = 15;

    // Jumlah rekomendasi sekolah tujuan per guru
    public const MAKS_REKOMENDASI = 3;

    // Daftar rekomendasi SPK (semua peran boleh melihat)
    public function index(Request $request)
    {
        $query = HasilSpk::with(['guru.sekolah', 'sekolahTujuan', 'diprosesOleh']);

        // Filter status via ?status=rekomendasi
        if ($request->filled('status') && array_key_exists($request->status, HasilSpk::STATUS)) {
            $query->where('status', $request->status);
        }

        // Urutkan: yang masih rekomendasi dulu, lalu jarak terdekat
        $spks = $query
            ->orderByRaw("FIELD(status, 'rekomendasi', 'disetujui', 'ditolak')")
            ->orderBy('jarak_km')
            ->paginate(20)
            ->withQueryString();

        return view('spk.index', [
            'spks' => $spks,
            'statusAktif' => $request->status,
            'ambangKm' => self::AMBANG_KM,
        ]);
    }

    // Generate rekomendasi SPK (kabid_ptk)
    public function generate()
    {
        $gurus = Guru::with('sekolah')
            ->whereNotNull('domisili_lat')
            ->whereNotNull('domisili_lng')
            ->whereNotNull('sekolah_id')
            ->get();

        $baru = 0;

        DB::transaction(function () use ($gurus, &$baru) {
            foreach ($gurus as $guru) {
                $sekSekarang = $guru->sekolah;
                if (!$sekSekarang) {
                    continue;
                }

                // Skenario 1: hanya proses guru yang jaraknya melebihi ambang batas
                $jarakSekarang = $guru->jarakKe((float) $sekSekarang->lat, (float) $sekSekarang->lng);
                if ($jarakSekarang <= self::AMBANG_KM) {
                    continue;
                }

                // Cari sekolah terdekat yang kekurangan formasi mapel guru ini.
                // Haversine dihitung langsung di MySQL (lebih cepat untuk data besar).
                // LEAST/GREATEST menjaga argumen acos tetap dalam [-1, 1].
                $kandidat = Sekolah::select('sekolah.*')
                    ->selectRaw(
                        '6371 * acos(LEAST(1, GREATEST(-1, ' .
                        'cos(radians(?)) * cos(radians(sekolah.lat)) * cos(radians(sekolah.lng) - radians(?)) + ' .
                        'sin(radians(?)) * sin(radians(sekolah.lat))' .
                        '))) AS jarak_km',
                        [$guru->domisili_lat, $guru->domisili_lng, $guru->domisili_lat]
                    )
                    ->join('kebutuhan_guru', 'kebutuhan_guru.sekolah_id', '=', 'sekolah.id')
                    ->where('kebutuhan_guru.mapel', $guru->mapel)
                    ->where('kebutuhan_guru.jumlah', '>', 0)
                    ->where('sekolah.id', '!=', $guru->sekolah_id)
                    ->orderBy('jarak_km')
                    ->limit(self::MAKS_REKOMENDASI)
                    ->get();

                foreach ($kandidat as $sek) {
                    // Hindari duplikat: rekomendasi aktif untuk pasangan ini sudah ada
                    $sudahAda = HasilSpk::whereIn('status', ['rekomendasi', 'disetujui'])
                        ->where('guru_id', $guru->id)
                        ->where('sekolah_tujuan_id', $sek->id)
                        ->exists();
                    if ($sudahAda) {
                        continue;
                    }

                    HasilSpk::create([
                        'guru_id' => $guru->id,
                        'sekolah_tujuan_id' => $sek->id,
                        'jarak_km' => round((float) $sek->jarak_km, 2),
                        'jarak_sekarang_km' => round($jarakSekarang, 2),
                    ]);
                    $baru++;
                }
            }
        });

        return back()->with(
            'sukses',
            "Generate SPK selesai — ditemukan {$baru} rekomendasi penempatan baru " .
            "(ambang jarak > " . self::AMBANG_KM . " km)."
        );
    }

    // Setujui penempatan (kadis): mutasi guru + tolak rekomendasi lain utk guru tsb
    public function setujui(Request $request, HasilSpk $spk)
    {
        if ($spk->status !== 'rekomendasi') {
            return back()->with('sukses', 'Rekomendasi ini sudah diproses sebelumnya.');
        }

        DB::transaction(function () use ($request, $spk) {
            $guru = $spk->guru;
            $tujuan = $spk->sekolahTujuan;

            // Mutasi guru ke sekolah tujuan
            $guru->update([
                'sekolah_id' => $tujuan->id,
                'sekolah_saat_ini' => $tujuan->nama,
                'sekolah_saat_ini_lat' => $tujuan->lat,
                'sekolah_saat_ini_lng' => $tujuan->lng,
            ]);

            $spk->update([
                'status' => 'disetujui',
                'diproses_oleh' => auth()->id(),
                'catatan_admin' => $request->input('catatan_admin'),
            ]);

            // Rekomendasi lain untuk guru yang sama otomatis ditolak,
            // agar tidak terjadi penempatan ganda
            HasilSpk::where('status', 'rekomendasi')
                ->where('id', '!=', $spk->id)
                ->where('guru_id', $guru->id)
                ->update([
                    'status' => 'ditolak',
                    'diproses_oleh' => auth()->id(),
                    'catatan_admin' => 'Otomatis ditolak: guru sudah ditempatkan via rekomendasi lain.',
                ]);
        });

        return back()->with('sukses', 'Penempatan disetujui — guru sudah dimutasi ke sekolah tujuan.');
    }

    // Tolak rekomendasi (kabid_ptk / kadis)
    public function tolak(Request $request, HasilSpk $spk)
    {
        if ($spk->status !== 'rekomendasi') {
            return back()->with('sukses', 'Rekomendasi ini sudah diproses sebelumnya.');
        }

        $spk->update([
            'status' => 'ditolak',
            'diproses_oleh' => auth()->id(),
            'catatan_admin' => $request->input('catatan_admin'),
        ]);

        return back()->with('sukses', 'Rekomendasi penempatan ditolak.');
    }
}
