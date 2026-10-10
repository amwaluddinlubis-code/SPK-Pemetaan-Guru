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
use App\Models\KebutuhanGuru;
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
            // SPK-003: sisa formasi per (sekolah, mapel) untuk kolom penjelasan
            'sisaKuota' => KebutuhanGuru::sisaDenganAlokasi(),
        ]);
    }

    // Generate rekomendasi SPK (kabid_ptk)
    //
    // Aturan kelayakan (akuntabilitas):
    //   1. Hanya guru yang jarak tugasnya > AMBANG_KM.
    //   2. Sekolah tujuan HARUS lebih dekat daripada sekolah saat ini
    //      (penghematan S = jarak_sekarang - jarak_tujuan > 0).
    //   3. Sisa formasi (kebutuhan - alokasi rekomendasi aktif) harus > 0,
    //      agar satu formasi tidak dijanjikan ke dua guru.
    //   4. Urutkan berdasarkan penghematan terbesar, bukan jarak tujuan.
    public function generate()
    {
        $gurus = Guru::with('sekolah')
            ->whereNotNull('domisili_lat')
            ->whereNotNull('domisili_lng')
            ->whereNotNull('sekolah_id')
            ->get();

        // Sisa kebutuhan per (sekolah, mapel), sudah dikurangi alokasi aktif
        $sisaKebutuhan = KebutuhanGuru::sisaDenganAlokasi();

        $baru = 0;

        DB::transaction(function () use ($gurus, $sisaKebutuhan, &$baru) {
            foreach ($gurus as $guru) {
                // MAP-001: jarak ke sekolah saat ini lewat relasi (sumber tunggal)
                $jarakSekarang = $guru->jarakKeSekolahSaatIni();
                if ($jarakSekarang === null) {
                    continue;
                }

                // Skenario 1: hanya proses guru yang jaraknya melebihi ambang batas
                if ($jarakSekarang <= self::AMBANG_KM) {
                    continue;
                }

                // Cari kandidat sekolah yang kekurangan formasi mapel guru ini.
                // Haversine dihitung langsung di MySQL (lebih cepat untuk data besar).
                // LEAST/GREATEST menjaga argumen acos tetap dalam [-1, 1].
                // Ambil lebih banyak dulu; filter hemat & kapasitas di bawah.
                $kandidat = Sekolah::select('sekolah.*')
                    ->selectRaw(
                        '6371 * acos(LEAST(1, GREATEST(-1, '.
                        'cos(radians(?)) * cos(radians(sekolah.lat)) * cos(radians(sekolah.lng) - radians(?)) + '.
                        'sin(radians(?)) * sin(radians(sekolah.lat))'.
                        '))) AS jarak_km',
                        [$guru->domisili_lat, $guru->domisili_lng, $guru->domisili_lat]
                    )
                    ->join('kebutuhan_guru', 'kebutuhan_guru.sekolah_id', '=', 'sekolah.id')
                    ->where('kebutuhan_guru.mapel', $guru->mapel)
                    ->where('kebutuhan_guru.jumlah', '>', 0)
                    ->where('sekolah.id', '!=', $guru->sekolah_id)
                    ->whereNotNull('sekolah.lat')
                    ->whereNotNull('sekolah.lng')
                    ->orderBy('jarak_km')
                    ->limit(20)
                    ->get();

                $layak = [];
                foreach ($kandidat as $sek) {
                    $jarakTujuan = (float) $sek->jarak_km;
                    $hemat = $jarakSekarang - $jarakTujuan;

                    // Syarat: tujuan harus lebih dekat (penghematan positif)
                    if ($hemat <= 0) {
                        continue;
                    }

                    // Hindari duplikat: rekomendasi aktif untuk pasangan ini sudah ada
                    $sudahAda = HasilSpk::whereIn('status', ['rekomendasi', 'disetujui'])
                        ->where('guru_id', $guru->id)
                        ->where('sekolah_tujuan_id', $sek->id)
                        ->exists();
                    if ($sudahAda) {
                        continue;
                    }

                    $layak[] = [
                        'sekolah' => $sek,
                        'jarak_tujuan' => $jarakTujuan,
                        'hemat' => $hemat,
                    ];
                }

                // Urutkan berdasarkan penghematan terbesar
                usort($layak, fn ($a, $b) => $b['hemat'] <=> $a['hemat']);

                // Ambil 3 teratas yang sisa formasinya masih tersedia.
                // Slot yang terpakai langsung dicadangkan agar guru berikutnya
                // tidak memakai formasi yang sama (tidak over-booking).
                $terpilih = 0;
                foreach ($layak as $item) {
                    if ($terpilih >= self::MAKS_REKOMENDASI) {
                        break;
                    }
                    $kunci = $item['sekolah']->id.'|'.strtolower(trim($guru->mapel));
                    if (($sisaKebutuhan[$kunci] ?? 0) <= 0) {
                        continue;
                    }
                    $sisaKebutuhan[$kunci]--;

                    HasilSpk::create([
                        'guru_id' => $guru->id,
                        'sekolah_asal_id' => $guru->sekolah_id,
                        'sekolah_tujuan_id' => $item['sekolah']->id,
                        'jarak_km' => round($item['jarak_tujuan'], 2),
                        'jarak_sekarang_km' => round($jarakSekarang, 2),
                        'hemat_km' => round($item['hemat'], 2),
                    ]);
                    $terpilih++;
                    $baru++;
                }
            }
        });

        return back()->with(
            'sukses',
            "Generate SPK selesai — ditemukan {$baru} rekomendasi penempatan baru ".
            '(ambang jarak > '.self::AMBANG_KM.' km, hanya yang menghemat jarak).'
        );
    }

    // Setujui penempatan (kadis): mutasi guru + tolak rekomendasi lain utk guru tsb
    //
    // Keamanan persetujuan:
    //   - Baris rekomendasi dikunci (lockForUpdate) agar tidak bisa
    //     disetujui dua kali oleh dua pejabat bersamaan.
    //   - Status, sekolah asal guru, dan sisa formasi divalidasi ULANG
    //     di dalam transaction (data bisa berubah sejak rekomendasi dibuat).
    //   - Sisa kebutuhan formasi dikurangi saat penempatan disetujui.
    public function setujui(Request $request, HasilSpk $spk)
    {
        try {
            DB::transaction(function () use ($request, $spk) {
                $spk = HasilSpk::whereKey($spk->id)->lockForUpdate()->firstOrFail();

                if ($spk->status !== 'rekomendasi') {
                    throw new \RuntimeException('Rekomendasi ini sudah diproses sebelumnya.');
                }

                $guru = Guru::whereKey($spk->guru_id)->lockForUpdate()->firstOrFail();
                $tujuan = $spk->sekolahTujuan;

                // Validasi ulang: guru masih bertugas di sekolah asal
                if ((int) $guru->sekolah_id !== (int) $spk->sekolah_asal_id) {
                    throw new \RuntimeException(
                        'Gagal: guru sudah berpindah sekolah setelah rekomendasi dibuat.'
                    );
                }

                // Validasi ulang: sisa formasi masih tersedia
                $sisa = KebutuhanGuru::where('sekolah_id', $tujuan->id)
                    ->where('mapel', $guru->mapel)
                    ->lockForUpdate()
                    ->value('jumlah') ?? 0;
                if ($sisa < 1) {
                    throw new \RuntimeException(
                        'Gagal: formasi di sekolah tujuan sudah terisi penuh.'
                    );
                }

                // Mutasi guru ke sekolah tujuan
                $guru->update([
                    'sekolah_id' => $tujuan->id,
                    'sekolah_saat_ini' => $tujuan->nama,
                    'sekolah_saat_ini_lat' => $tujuan->lat,
                    'sekolah_saat_ini_lng' => $tujuan->lng,
                ]);

                // Catat berkurangnya kebutuhan formasi
                KebutuhanGuru::kurangi($tujuan->id, $guru->mapel);

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
        } catch (\RuntimeException $e) {
            return back()->with('gagal', $e->getMessage());
        }

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
