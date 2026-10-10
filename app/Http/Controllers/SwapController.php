<?php
// Controller Mutasi Silang (swap): mencari pasangan dua guru yang bisa
// bertukar sekolah sehingga KEDUANYA lebih dekat ke domisili (win-win),
// lalu memproses persetujuan perpindahan dengan database transaction.
//
// Alur peran (mirip Usulan):
//   - Generate rekomendasi : kabid_ptk
//   - Setujui              : kadis
//   - Tolak                : kabid_ptk, kadis
//   - Lihat daftar         : semua peran

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\RekomendasiSwap;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SwapController extends Controller
{
    // Daftar rekomendasi swap (semua peran boleh melihat)
    public function index(Request $request)
    {
        $query = RekomendasiSwap::with([
            'guruA.sekolah', 'guruB.sekolah',
            'sekolahTujuanA', 'sekolahTujuanB', 'diprosesOleh',
        ]);

        // Filter status via ?status=menunggu
        if ($request->filled('status') && array_key_exists($request->status, RekomendasiSwap::STATUS)) {
            $query->where('status', $request->status);
        }

        // Urutkan: yang menunggu dulu, lalu total penghematan terbesar
        $swaps = $query
            ->orderByRaw("FIELD(status, 'menunggu', 'disetujui', 'ditolak', 'batal_karena_konflik')")
            ->orderByDesc('total_hemat_km')
            ->paginate(20)
            ->withQueryString();

        return view('swap.index', [
            'swaps' => $swaps,
            'statusAktif' => $request->status,
        ]);
    }

    // Generate rekomendasi (kabid_ptk): cocokkan semua pasangan guru O(N^2)
    public function generate()
    {
        $gurus = Guru::with('sekolah')
            ->whereNotNull('domisili_lat')
            ->whereNotNull('domisili_lng')
            ->whereNotNull('sekolah_id')
            ->get();

        $baru = 0;

        DB::transaction(function () use ($gurus, &$baru) {
            $n = $gurus->count();

            for ($i = 0; $i < $n; $i++) {
                for ($j = $i + 1; $j < $n; $j++) {
                    $a = $gurus[$i];
                    $b = $gurus[$j];

                    // Syarat 1: mata pelajaran sama
                    if (strtolower(trim($a->mapel)) !== strtolower(trim($b->mapel))) {
                        continue;
                    }

                    // Syarat 2: sekolah asal berbeda
                    if ($a->sekolah_id == $b->sekolah_id) {
                        continue;
                    }

                    $sekA = $a->sekolah;
                    $sekB = $b->sekolah;
                    if (!$sekA || !$sekB) {
                        continue;
                    }

                    // Jarak sekarang vs jarak jika bertukar (Haversine)
                    $jarakA0 = $a->jarakKe((float) $sekA->lat, (float) $sekA->lng);
                    $jarakB0 = $b->jarakKe((float) $sekB->lat, (float) $sekB->lng);
                    $jarakA1 = $a->jarakKe((float) $sekB->lat, (float) $sekB->lng);
                    $jarakB1 = $b->jarakKe((float) $sekA->lat, (float) $sekA->lng);

                    // Syarat 3 (win-win): keduanya harus lebih dekat setelah tukar
                    if (!($jarakA1 < $jarakA0 && $jarakB1 < $jarakB0)) {
                        continue;
                    }

                    // Hindari duplikat: pasangan ini sudah ada (menunggu/disetujui)
                    $sudahAda = RekomendasiSwap::whereIn('status', ['menunggu', 'disetujui'])
                        ->where(function ($q) use ($a, $b) {
                            $q->where([['guru_a_id', $a->id], ['guru_b_id', $b->id]])
                                ->orWhere([['guru_a_id', $b->id], ['guru_b_id', $a->id]]);
                        })
                        ->exists();
                    if ($sudahAda) {
                        continue;
                    }

                    $hematA = $jarakA0 - $jarakA1;
                    $hematB = $jarakB0 - $jarakB1;

                    RekomendasiSwap::create([
                        'guru_a_id' => $a->id,
                        'guru_b_id' => $b->id,
                        'sekolah_tujuan_a_id' => $sekB->id,
                        'sekolah_tujuan_b_id' => $sekA->id,
                        'jarak_a_sekarang_km' => round($jarakA0, 2),
                        'jarak_b_sekarang_km' => round($jarakB0, 2),
                        'jarak_a_usulan_km' => round($jarakA1, 2),
                        'jarak_b_usulan_km' => round($jarakB1, 2),
                        'hemat_a_km' => round($hematA, 2),
                        'hemat_b_km' => round($hematB, 2),
                        'total_hemat_km' => round($hematA + $hematB, 2),
                    ]);
                    $baru++;
                }
            }
        });

        return back()->with(
            'sukses',
            "Generate selesai — ditemukan {$baru} pasangan swap baru yang saling menguntungkan."
        );
    }

    // Setujui swap (kadis): pindahkan kedua guru + batalkan rekomendasi berkonflik
    public function setujui(Request $request, RekomendasiSwap $swap)
    {
        if ($swap->status !== 'menunggu') {
            return back()->with('sukses', 'Rekomendasi ini sudah diproses sebelumnya.');
        }

        DB::transaction(function () use ($request, $swap) {
            $a = $swap->guruA;
            $b = $swap->guruB;
            $tujuanA = $swap->sekolahTujuanA; // sekolah B semula
            $tujuanB = $swap->sekolahTujuanB; // sekolah A semula

            // Pindahkan guru A ke sekolah tujuan A
            $a->update([
                'sekolah_id' => $tujuanA->id,
                'sekolah_saat_ini' => $tujuanA->nama,
                'sekolah_saat_ini_lat' => $tujuanA->lat,
                'sekolah_saat_ini_lng' => $tujuanA->lng,
            ]);

            // Pindahkan guru B ke sekolah tujuan B
            $b->update([
                'sekolah_id' => $tujuanB->id,
                'sekolah_saat_ini' => $tujuanB->nama,
                'sekolah_saat_ini_lat' => $tujuanB->lat,
                'sekolah_saat_ini_lng' => $tujuanB->lng,
            ]);

            $swap->update([
                'status' => 'disetujui',
                'diproses_oleh' => auth()->id(),
                'catatan_admin' => $request->input('catatan_admin'),
            ]);

            // Batalkan rekomendasi lain (masih menunggu) yang melibatkan A atau B,
            // agar tidak terjadi penempatan ganda
            RekomendasiSwap::where('status', 'menunggu')
                ->where('id', '!=', $swap->id)
                ->where(function ($q) use ($a, $b) {
                    $q->whereIn('guru_a_id', [$a->id, $b->id])
                        ->orWhereIn('guru_b_id', [$a->id, $b->id]);
                })
                ->update([
                    'status' => 'batal_karena_konflik',
                    'diproses_oleh' => auth()->id(),
                    'catatan_admin' => 'Otomatis dibatalkan: guru sudah dimutasi melalui swap lain.',
                ]);
        });

        return back()->with('sukses', 'Swap disetujui — kedua guru sudah dimutasi ke sekolah barunya.');
    }

    // Tolak swap (kabid_ptk / kadis)
    public function tolak(Request $request, RekomendasiSwap $swap)
    {
        if ($swap->status !== 'menunggu') {
            return back()->with('sukses', 'Rekomendasi ini sudah diproses sebelumnya.');
        }

        $swap->update([
            'status' => 'ditolak',
            'diproses_oleh' => auth()->id(),
            'catatan_admin' => $request->input('catatan_admin'),
        ]);

        return back()->with('sukses', 'Rekomendasi swap ditolak.');
    }
}
