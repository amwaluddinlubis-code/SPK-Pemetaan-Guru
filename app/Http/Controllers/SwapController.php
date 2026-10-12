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

use App\Models\ActivityLog;
use App\Models\Guru;
use App\Models\RekomendasiSwap;
use App\Models\UsulanPenempatan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SwapController extends Controller
{
    // Daftar rekomendasi swap (semua peran boleh melihat)
    /**
     * Halaman index dirender komponen Livewire TabelSwap
     * (filter status, pagination tanpa reload).
     */
    public function index()
    {
        return view('swap.index');
    }

    // Generate rekomendasi (kabid_ptk): cocokkan semua pasangan guru O(N^2)
    public function generate()
    {
        $baru = 0;

        try {
            // Upaya ulang 3x bila deadlock. Seluruh pembacaan kandidat,
            // penguncian, dan validasi dilakukan DI DALAM transaksi agar
            // dua proses bersamaan tidak memakai snapshot lama.
            DB::transaction(function () use (&$baru) {
                // 1. Baca kandidat & kunci semua baris guru (urutan id
                //    konsisten → dua proses generate saling menunggu).
                $gurus = Guru::with('sekolah')
                    ->whereNotNull('domisili_lat')
                    ->whereNotNull('domisili_lng')
                    ->whereNotNull('sekolah_id')
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get();

                // 2. Hitung ULANG dari data terkini (setelah lock): guru yang
                //    sudah punya rekomendasi menunggu tidak dicarikan pasangan
                //    baru — satu guru hanya boleh ada di satu rekomendasi aktif.
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
                            // Bobot = total penghematan × bobot keluarga tiap guru
                            'bobot' => $hematA * $a->bobotKeluarga()
                                     + $hematB * $b->bobotKeluarga(),
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
            }, 3);
        } catch (\Throwable $e) {
            \Log::warning('Generate swap gagal', ['error' => $e->getMessage()]);

            return back()->with('gagal', 'Generate swap gagal — silakan coba lagi. Jika berlanjut, hubungi administrator.');
        }

        ActivityLog::catatAman('swap.generate', null, 'sukses', [
            'pasangan_baru' => $baru,
        ]);

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

                ActivityLog::catatAman('swap.setujui', $swap, 'sukses', [
                    'status_sebelum' => 'menunggu',
                    'status_sesudah' => 'disetujui',
                    'guru_a_id' => $a->id,
                    'guru_b_id' => $b->id,
                ]);
            }, 3);
        } catch (\RuntimeException $e) {
            return back()->with('gagal', $e->getMessage());
        } catch (\Throwable $e) {
            \Log::error('Persetujuan swap gagal', ['swap_id' => $swap->id, 'error' => $e->getMessage()]);

            return back()->with('gagal', 'Persetujuan gagal karena kendala teknis — silakan coba lagi.');
        }

        return back()->with('sukses', 'Swap disetujui — kedua guru sudah dimutasi ke sekolah barunya.');
    }

    // Tolak swap (kabid_ptk / kadis)
    public function tolak(Request $request, RekomendasiSwap $swap)
    {
        // Alasan penolakan wajib (D) — keputusan harus dapat diaudit.
        $data = $request->validate([
            'catatan_admin' => ['required', 'string', 'max:1000'],
        ]);

        // FLOW-001: transisi status atomik — kunci dan validasi ulang.
        try {
            DB::transaction(function () use ($swap, $data) {
                $terkunci = RekomendasiSwap::whereKey($swap->id)->lockForUpdate()->firstOrFail();
                if ($terkunci->status !== 'menunggu') {
                    throw new \RuntimeException('Rekomendasi ini sudah diproses sebelumnya.');
                }
                $terkunci->update([
                    'status' => 'ditolak',
                    'diproses_oleh' => auth()->id(),
                    'catatan_admin' => $data['catatan_admin'],
                ]);

                ActivityLog::catatAman('swap.tolak', $terkunci, 'sukses', [
                    'status_sebelum' => 'menunggu',
                    'status_sesudah' => 'ditolak',
                    'alasan' => $data['catatan_admin'],
                ]);
            });
        } catch (\RuntimeException $e) {
            return back()->with('sukses', $e->getMessage());
        } catch (\Throwable $e) {
            \Log::error('Penolakan swap gagal', ['swap_id' => $swap->id, 'error' => $e->getMessage()]);

            return back()->with('gagal', 'Penolakan gagal karena kendala teknis — silakan coba lagi.');
        }

        return back()->with('sukses', 'Rekomendasi swap ditolak.');
    }
}
