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
use App\Models\UsulanPenempatan;
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
        $baru = 0;

        DB::transaction(function () use (&$baru) {
            // Kunci guru sebelum membaca swap aktif agar dua proses generate
            // tidak sama-sama memilih guru yang belum tercatat sebagai terpakai.
            $gurus = Guru::with('sekolah')
                ->whereNotNull('domisili_lat')
                ->whereNotNull('domisili_lng')
                ->whereNotNull('sekolah_id')
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            // Baca status aktif setelah memperoleh lock, bukan sebelum transaksi.
            $terpakai = RekomendasiSwap::where('status', 'menunggu')
                ->get(['guru_a_id', 'guru_b_id'])
                ->flatMap(fn ($r) => [$r->guru_a_id, $r->guru_b_id])
                ->flip()
                ->toArray();

            $n = $gurus->count();

            // Guru yang punya usulan aktif tidak boleh ikut swap (konflik alur)
            $usulanAktif = UsulanPenempatan::whereIn('status', UsulanPenempatan::AKTIF)
                ->pluck('guru_id')->flip()->toArray();

            // Tahap 1: kumpulkan SEMUA pasangan win-win yang valid beserta bobotnya
            // (bobot = total penghematan kedua guru).
            $kandidat = [];
            for ($i = 0; $i < $n; $i++) {
                for ($j = $i + 1; $j < $n; $j++) {
                    $a = $gurus[$i];
                    $b = $gurus[$j];

                    // Syarat 0: belum punya rekomendasi aktif & tanpa usulan aktif
                    if (isset($terpakai[$a->id]) || isset($terpakai[$b->id])) {
                        continue;
                    }
                    if (isset($usulanAktif[$a->id]) || isset($usulanAktif[$b->id])) {
                        continue;
                    }

                    // Syarat 1: mata pelajaran sama
                    if (strtolower(trim($a->mapel)) !== strtolower(trim($b->mapel))) {
                        continue;
                    }

                    // Syarat 1b (administratif): jenjang sama — mutasi lintas
                    // jenjang (mis. SD↔SMP) butuh persetujuan kepegawaian khusus
                    if (strtoupper(trim($a->jenjang)) !== strtoupper(trim($b->jenjang))) {
                        continue;
                    }

                    // Syarat 2: sekolah asal berbeda
                    if ($a->sekolah_id == $b->sekolah_id) {
                        continue;
                    }

                    $sekA = $a->sekolah;
                    $sekB = $b->sekolah;
                    if (! $sekA || ! $sekB) {
                        continue;
                    }

                    // Syarat 2b: koordinat lengkap dan masuk akal
                    foreach ([$a, $b, $sekA, $sekB] as $o) {
                        $lat = (float) ($o->lat ?? $o->domisili_lat);
                        $lng = (float) ($o->lng ?? $o->domisili_lng);
                        if ($lat == 0.0 || $lng == 0.0) {
                            continue 2; // lewati pasangan ini saja
                        }
                    }

                    // Jarak sekarang vs jarak jika bertukar (Haversine).
                    // MAP-001: jarak ke sekolah saat ini lewat relasi (sumber tunggal).
                    $jarakA0 = $a->jarakKeSekolahSaatIni();
                    $jarakB0 = $b->jarakKeSekolahSaatIni();
                    if ($jarakA0 === null || $jarakB0 === null) {
                        continue;
                    }
                    $jarakA1 = $a->jarakKe((float) $sekB->lat, (float) $sekB->lng);
                    $jarakB1 = $b->jarakKe((float) $sekA->lat, (float) $sekA->lng);

                    // Syarat 3 (win-win): keduanya harus lebih dekat setelah tukar
                    if (! ($jarakA1 < $jarakA0 && $jarakB1 < $jarakB0)) {
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

                    $kandidat[] = [
                        'a' => $a, 'b' => $b,
                        'sekA' => $sekA, 'sekB' => $sekB,
                        'jarakA0' => $jarakA0, 'jarakB0' => $jarakB0,
                        'jarakA1' => $jarakA1, 'jarakB1' => $jarakB1,
                        'hematA' => $hematA, 'hematB' => $hematB,
                        'bobot' => $hematA + $hematB,
                    ];
                }
            }

            // Tahap 2: greedy maximum-weight matching — ambil pasangan dengan
            // total penghematan terbesar dulu, lewati yang gurunya sudah terpakai.
            // (Optimal untuk matching umum butuh algoritma Blossom; greedy cukup
            // untuk skala ini dan mudah dijelaskan.)
            usort($kandidat, fn ($x, $y) => $y['bobot'] <=> $x['bobot']);

            foreach ($kandidat as $k) {
                $a = $k['a'];
                $b = $k['b'];
                if (isset($terpakai[$a->id]) || isset($terpakai[$b->id])) {
                    continue;
                }
                $terpakai[$a->id] = true;
                $terpakai[$b->id] = true;

                RekomendasiSwap::create([
                    'guru_a_id' => $a->id,
                    'guru_b_id' => $b->id,
                    'sekolah_tujuan_a_id' => $k['sekB']->id,
                    'sekolah_tujuan_b_id' => $k['sekA']->id,
                    'jarak_a_sekarang_km' => round($k['jarakA0'], 2),
                    'jarak_b_sekarang_km' => round($k['jarakB0'], 2),
                    'jarak_a_usulan_km' => round($k['jarakA1'], 2),
                    'jarak_b_usulan_km' => round($k['jarakB1'], 2),
                    'hemat_a_km' => round($k['hematA'], 2),
                    'hemat_b_km' => round($k['hematB'], 2),
                    'total_hemat_km' => round($k['bobot'], 2),
                ]);
                $baru++;
            }
        });

        return back()->with(
            'sukses',
            "Generate selesai — ditemukan {$baru} pasangan swap baru yang saling menguntungkan."
        );
    }

    // Setujui swap (kadis): pindahkan kedua guru + batalkan rekomendasi berkonflik
    //
    // Keamanan persetujuan:
    //   - Baris rekomendasi & kedua guru dikunci (lockForUpdate) dalam urutan
    //     id yang konsisten (cegah deadlock & persetujuan ganda).
    //   - Validasi ulang: kedua guru masih di sekolah asal saat rekomendasi
    //     dibuat — jika salah satunya sudah pindah, persetujuan dibatalkan
    //     (tidak ada mutasi parsial).
    public function setujui(Request $request, RekomendasiSwap $swap)
    {
        try {
            DB::transaction(function () use ($request, $swap) {
                $swap = RekomendasiSwap::whereKey($swap->id)->lockForUpdate()->firstOrFail();

                if ($swap->status !== 'menunggu') {
                    throw new \RuntimeException('Rekomendasi ini sudah diproses sebelumnya.');
                }

                // Kunci kedua guru dengan urutan id konsisten (anti-deadlock)
                $ids = collect([$swap->guru_a_id, $swap->guru_b_id])->sort()->values();
                $terkunci = Guru::whereIn('id', $ids)->lockForUpdate()
                    ->get()->keyBy('id');
                $a = $terkunci[$swap->guru_a_id] ?? null;
                $b = $terkunci[$swap->guru_b_id] ?? null;
                if (! $a || ! $b) {
                    throw new \RuntimeException('Gagal: data guru tidak ditemukan.');
                }

                $tujuanA = $swap->sekolahTujuanA; // sekolah B semula
                $tujuanB = $swap->sekolahTujuanB; // sekolah A semula

                // Validasi ulang: keduanya masih di sekolah asal
                if ((int) $a->sekolah_id !== (int) $tujuanB->id
                    || (int) $b->sekolah_id !== (int) $tujuanA->id) {
                    throw new \RuntimeException(
                        'Gagal: salah satu guru sudah berpindah sekolah. Rekomendasi kedaluwarsa.'
                    );
                }

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
        } catch (\RuntimeException $e) {
            return back()->with('gagal', $e->getMessage());
        }

        return back()->with('sukses', 'Swap disetujui — kedua guru sudah dimutasi ke sekolah barunya.');
    }

    // Tolak swap (kabid_ptk / kadis)
    public function tolak(Request $request, RekomendasiSwap $swap)
    {
        $sudahDiproses = false;

        DB::transaction(function () use ($request, $swap, &$sudahDiproses) {
            // Baca ulang status di bawah lock yang sama-sama dipakai persetujuan.
            $terkunci = RekomendasiSwap::whereKey($swap->id)->lockForUpdate()->firstOrFail();

            if ($terkunci->status !== 'menunggu') {
                $sudahDiproses = true;

                return;
            }

            $terkunci->update([
                'status' => 'ditolak',
                'diproses_oleh' => auth()->id(),
                'catatan_admin' => $request->input('catatan_admin'),
            ]);
        });

        if ($sudahDiproses) {
            return back()->with('sukses', 'Rekomendasi ini sudah diproses sebelumnya.');
        }

        return back()->with('sukses', 'Rekomendasi swap ditolak.');
    }
}
