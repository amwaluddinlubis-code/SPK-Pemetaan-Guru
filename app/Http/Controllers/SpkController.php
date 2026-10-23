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

use App\Models\ActivityLog;
use App\Models\Guru;
use App\Models\HasilSpk;
use App\Models\KebutuhanGuru;
use App\Models\Sekolah;
use App\Services\AnalisisKebutuhanGuru;
use App\Services\Linieritas;
use App\Services\MesinSawTopsis;
use App\Services\PengaturanSpk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SpkController extends Controller
{
    // Ambang batas toleransi jarak (km): hanya guru yang jarak tugasnya
    // melebihi nilai ini yang diproses SPK
    public const AMBANG_KM = 15;

    // Jumlah rekomendasi sekolah tujuan per guru
    public const MAKS_REKOMENDASI = 3;

    // Daftar rekomendasi SPK — dibatasi scope peran (SEC-003):
    // guru hanya melihat rekomendasinya sendiri, kepala sekolah se-sekolah,
    // ketua PGRI se-kecamatan; kabid/kadis/pemda melihat semua.
    //
    // Halaman index dirender komponen Livewire TabelSpk
    // (filter status, pagination tanpa reload + data kelayakan per halaman).
    /**
     * Hitung dampak ke sekolah asal per rekomendasi.
     * Kembali: [hasil_spk_id => 'kurang N guru Mapel di sekolah asal', ...]
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
            // Guru yang pindah = satu guru lebih sedikit di sekolah asal
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

    // Generate rekomendasi SPK (kabid_ptk)
    //
    // Aturan kelayakan (akuntabilitas):
    //   1. Hanya guru yang jarak tugasnya > AMBANG_KM.
    //   2. Sekolah tujuan HARUS lebih dekat daripada sekolah saat ini
    //      (penghematan S = jarak_sekarang - jarak_tujuan > 0).
    //   3. Sisa formasi (kebutuhan - alokasi rekomendasi aktif) harus > 0,
    //      agar satu formasi tidak dijanjikan ke dua guru.
    //   4. Alternatif yang layak diperingkat dengan skor hibrida
    //      SAW + TOPSIS (MesinSawTopsis): penghematan jarak, jarak tujuan,
    //      sisa formasi, dan prioritas daerah tujuan.
    //   5. Prioritas keluarga: guru berkeluarga diproses lebih dulu
    //      (bobot 1.5×) agar tidak kehabisan formasi saat kuota terbatas.
    public function generate(MesinSawTopsis $penilai)
    {
        $baru = 0;

        try {
            // Upaya ulang 3x bila deadlock (MySQL 1213). Laravel hanya
            // mengulang untuk deadlock — RuntimeException aturan bisnis
            // tidak diulang.
            DB::transaction(function () use (&$baru, $penilai) {
                // 1. Baca kandidat & kunci SEMUA baris guru kandidat sekaligus
                //    dengan urutan id konsisten (dua proses generate saling
                //    menunggu — tidak ada snapshot ganda).
                $gurus = Guru::with('sekolah')
                    ->whereNotNull('domisili_lat')
                    ->whereNotNull('domisili_lng')
                    ->whereNotNull('sekolah_id')
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get()
                    // Prioritas keluarga: guru berkeluarga diproses dulu agar
                    // tidak kehabisan formasi saat kuota terbatas.
                    ->sortByDesc(fn ($g) => $g->bobotKeluarga())
                    ->values();

                // 2. Kunci baris kebutuhan untuk mapel kandidat (urutan id
                //    konsisten), lalu hitung ULANG sisa dari data terkini:
                //    jumlah - alokasi aktif (status 'rekomendasi').
                //    Tanpa ini, dua proses bersamaan memakai snapshot lama
                //    dan bisa mengalokasikan formasi yang sama dua kali.
                $sisaKebutuhan = $this->sisaKebutuhanTerkunci($gurus);

                foreach ($gurus as $guru) {
                    // Rem pemerataan: guru yang bertugas di daerah 3T/terpencil
                    // tidak ditarik keluar lewat SPK (sekolah 3T dilindungi).
                    if ($guru->bertugasDiDaerah3T()) {
                        continue;
                    }

                    // MAP-001: jarak ke sekolah saat ini lewat relasi (sumber tunggal)
                    $jarakSekarang = $guru->jarakKeSekolahSaatIni();
                    if ($jarakSekarang === null) {
                        continue;
                    }

                    // Skenario 1: hanya proses guru yang jaraknya melebihi ambang batas
                    if ($jarakSekarang <= self::AMBANG_KM) {
                        continue;
                    }

                    // Formasi yang dapat diisi guru ini (linieritas fase 2,
                    // Kepmen 222/O/2025): mapelnya sendiri (tingkat 1) dan —
                    // bila ia bersertifikat serta bidang studi serdiknya
                    // terisi — mapel linier kelompok dari serdiknya
                    // (tingkat 2). Mapel Agama tidak pernah masuk tingkat 2
                    // karena peta linieritas tidak memuatnya.
                    $mapelTier2 = [];
                    if ($guru->sudah_sertifikasi && ! empty($guru->bidang_studi_serdik)) {
                        $linier = Linieritas::mapelLinier($guru->bidang_studi_serdik, $guru->jenjang) ?? [];
                        $mapelTier2 = array_values(array_filter(
                            array_intersect($linier, AnalisisKebutuhanGuru::daftarKanonis()),
                            fn ($m) => strcasecmp((string) $m, (string) $guru->mapel) !== 0
                        ));
                    }

                    $kandidat = $this->kandidatSekolah($guru, [(string) $guru->mapel]);
                    if ($mapelTier2 !== []) {
                        $kandidat = $kandidat->concat($this->kandidatSekolah($guru, $mapelTier2));
                    }

                    $layak = [];
                    $duplikatDicek = [];
                    foreach ($kandidat as $sek) {
                        $mapelTujuan = (string) $sek->mapel_kebutuhan;
                        $jarakTujuan = (float) $sek->jarak_km;
                        $hemat = $jarakSekarang - $jarakTujuan;

                        // Syarat: tujuan harus lebih dekat (penghematan positif)
                        if ($hemat <= 0) {
                            continue;
                        }

                        // Hindari duplikat: rekomendasi aktif untuk pasangan
                        // guru-sekolah ini sudah ada (satu penempatan per
                        // sekolah per guru, lintas mapel).
                        if (! array_key_exists($sek->id, $duplikatDicek)) {
                            $duplikatDicek[$sek->id] = HasilSpk::whereIn('status', ['rekomendasi', 'disetujui'])
                                ->where('guru_id', $guru->id)
                                ->where('sekolah_tujuan_id', $sek->id)
                                ->exists();
                        }
                        if ($duplikatDicek[$sek->id]) {
                            continue;
                        }

                        // Sisa formasi pada saat penilaian menjadi salah
                        // satu kriteria; slot yang sudah habis gugur dulu.
                        // Kuncinya formasi yang diisi (mapel tujuan).
                        $kunci = $sek->id.'|'.strtolower(trim($mapelTujuan));
                        $sisaFormasi = (int) ($sisaKebutuhan[$kunci] ?? 0);
                        if ($sisaFormasi <= 0) {
                            continue;
                        }

                        $layak[] = [
                            'sekolah' => $sek,
                            'jarak_tujuan' => $jarakTujuan,
                            'hemat' => $hemat,
                            'mapel_tujuan' => $mapelTujuan,
                            'tingkat_linieritas' => strcasecmp($mapelTujuan, (string) $guru->mapel) === 0 ? 1 : 2,
                            // Kriteria MesinSawTopsis
                            'hemat_km' => $hemat,
                            'jarak_tujuan_km' => $jarakTujuan,
                            'sisa_formasi' => $sisaFormasi,
                            'prioritas_daerah' => MesinSawTopsis::nilaiDaerah($sek->kategori_daerah),
                            'kategori_daerah' => $sek->kategori_daerah ?? 'biasa',
                        ];
                    }

                    // Peringkat multikriteria: skor akhir adalah rata-rata
                    // SAW dan TOPSIS. Prioritas keluarga tetap diwujudkan
                    // lewat urutan pemrosesan guru (berkeluarga lebih dulu),
                    // bukan sebagai kriteria antar-sekolah bagi guru yang sama.
                    $layak = $penilai->peringkat($layak);

                    // Ambil 3 teratas yang sisa formasinya masih tersedia.
                    // Slot yang terpakai langsung dicadangkan agar guru berikutnya
                    // tidak memakai formasi yang sama (tidak over-booking).
                    $terpilih = 0;
                    $sekolahTerpilih = [];
                    foreach ($layak as $item) {
                        if ($terpilih >= self::MAKS_REKOMENDASI) {
                            break;
                        }
                        // Satu penempatan per sekolah per guru dalam sekali
                        // generate, walau sekolah punya dua formasi yang
                        // sama-sama dapat diisi guru ini.
                        if (isset($sekolahTerpilih[$item['sekolah']->id])) {
                            continue;
                        }
                        $kunci = $item['sekolah']->id.'|'.strtolower(trim($item['mapel_tujuan']));
                        if (($sisaKebutuhan[$kunci] ?? 0) <= 0) {
                            continue;
                        }
                        $sisaKebutuhan[$kunci]--;
                        $sekolahTerpilih[$item['sekolah']->id] = true;

                        HasilSpk::create([
                            'guru_id' => $guru->id,
                            'sekolah_asal_id' => $guru->sekolah_id,
                            'sekolah_tujuan_id' => $item['sekolah']->id,
                            'mapel_tujuan' => $item['mapel_tujuan'],
                            'tingkat_linieritas' => $item['tingkat_linieritas'],
                            'jarak_km' => round($item['jarak_tujuan'], 2),
                            'jarak_sekarang_km' => round($jarakSekarang, 2),
                            'hemat_km' => round($item['hemat'], 2),
                            'skor_saw' => round($item['skor_saw'], 4),
                            'skor_topsis' => round($item['skor_topsis'], 4),
                            'skor' => round($item['skor_hybrid'], 4),
                            'detail_skor' => $item['detail_skor'],
                        ]);
                        $terpilih++;
                        $baru++;
                    }
                }
            }, 3);
        } catch (\Throwable $e) {
            \Log::warning('Generate SPK gagal', ['error' => $e->getMessage()]);

            return back()->with('gagal', 'Generate SPK gagal — silakan coba lagi. Jika berlanjut, hubungi administrator.');
        }

        ActivityLog::catatAman('spk.generate', null, 'sukses', [
            'rekomendasi_baru' => $baru,
        ]);

        return back()->with(
            'sukses',
            "Generate SPK selesai — ditemukan {$baru} rekomendasi penempatan baru ".
            '(ambang jarak > '.self::AMBANG_KM.' km; alternatif layak diperingkat Greedy + SAW–TOPSIS).'
        );
    }

    /**
     * Kandidat sekolah untuk satu guru pada daftar mapel tertentu: sekolah
     * (selain sekolah asal) yang kekurangan formasi mapel-mapel itu, 20
     * terdekat dari domisili guru. Setiap baris membawa atribut
     * `mapel_kebutuhan` (formasi yang akan diisi) dan `jarak_km` (Haversine
     * dihitung di MySQL; LEAST/GREATEST menjaga argumen acos dalam [-1, 1]).
     */
    private function kandidatSekolah(Guru $guru, array $daftarMapel)
    {
        return Sekolah::select('sekolah.*')
            ->selectRaw('kebutuhan_guru.mapel AS mapel_kebutuhan')
            ->selectRaw(
                '6371 * acos(LEAST(1, GREATEST(-1, '.
                'cos(radians(?)) * cos(radians(sekolah.lat)) * cos(radians(sekolah.lng) - radians(?)) + '.
                'sin(radians(?)) * sin(radians(sekolah.lat))'.
                '))) AS jarak_km',
                [$guru->domisili_lat, $guru->domisili_lng, $guru->domisili_lat]
            )
            ->join('kebutuhan_guru', 'kebutuhan_guru.sekolah_id', '=', 'sekolah.id')
            // Pengaturan dinas: mode bawaan melarang rekomendasi lintas
            // jenjang (guru hanya ke sekolah sejenjangnya).
            ->when(! PengaturanSpk::bolehLintasJenjang(),
                fn ($q) => $q->where('sekolah.jenjang', $guru->jenjang))
            ->whereIn('kebutuhan_guru.mapel', $daftarMapel)
            ->where('kebutuhan_guru.jumlah', '>', 0)
            ->where('sekolah.id', '!=', $guru->sekolah_id)
            ->whereNotNull('sekolah.lat')
            ->whereNotNull('sekolah.lng')
            ->orderBy('jarak_km')
            ->limit(20)
            ->get();
    }

    /**
     * Hitung sisa kebutuhan per (sekolah, mapel) dari data terkini DI DALAM
     * transaksi: kunci baris kebutuhan yang relevan (urutan id konsisten),
     * lalu kurangi dengan alokasi aktif (rekomendasi berstatus 'rekomendasi').
     *
     * WAJIB dipanggil di dalam DB::transaction — di luar itu penguncian
     * tidak berlaku dan hasil bisa basi.
     */
    private function sisaKebutuhanTerkunci($gurus): array
    {
        $mapelKandidat = $gurus->map(fn ($g) => $g->mapel)->unique();
        // Sertakan mapel tingkat 2 (linier kelompok) yang dapat diisi guru
        // kandidat — sisa formasinya ikut dikunci & dihitung.
        foreach ($gurus as $g) {
            if ($g->sudah_sertifikasi && ! empty($g->bidang_studi_serdik)) {
                foreach (Linieritas::mapelLinier($g->bidang_studi_serdik, $g->jenjang) ?? [] as $m) {
                    if (in_array($m, AnalisisKebutuhanGuru::daftarKanonis(), true)) {
                        $mapelKandidat->push($m);
                    }
                }
            }
        }
        $mapelKandidat = $mapelKandidat->unique()->values();

        $sisa = [];
        $terkunci = KebutuhanGuru::whereIn('mapel', $mapelKandidat)
            ->orderBy('id')
            ->lockForUpdate()
            ->get();
        foreach ($terkunci as $k) {
            $sisa[$k->sekolah_id.'|'.strtolower(trim($k->mapel))] = (int) $k->jumlah;
        }

        // Alokasi aktif mengurangi formasi yang benar-benar diisi
        // (mapel_tujuan; rekomendasi lama memakai mapel guru).
        $alokasi = HasilSpk::where('status', 'rekomendasi')
            ->join('guru', 'guru.id', '=', 'hasil_spks.guru_id')
            ->selectRaw('hasil_spks.sekolah_tujuan_id, COALESCE(hasil_spks.mapel_tujuan, guru.mapel) as mapel, COUNT(*) as jml')
            ->groupBy('hasil_spks.sekolah_tujuan_id', DB::raw('COALESCE(hasil_spks.mapel_tujuan, guru.mapel)'))
            ->get();
        foreach ($alokasi as $a) {
            $kunci = $a->sekolah_tujuan_id.'|'.strtolower(trim($a->mapel));
            $sisa[$kunci] = ($sisa[$kunci] ?? 0) - (int) $a->jml;
        }

        return $sisa;
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

                // Formasi yang diisi: mapel_tujuan rekomendasi (pada
                // penempatan tingkat 2 berbeda dari mapel guru saat ini).
                $mapelIsi = $spk->mapel_tujuan ?: (string) $guru->mapel;

                // Validasi ulang: sisa formasi masih tersedia
                $sisa = KebutuhanGuru::where('sekolah_id', $tujuan->id)
                    ->where('mapel', $mapelIsi)
                    ->lockForUpdate()
                    ->value('jumlah') ?? 0;
                if ($sisa < 1) {
                    throw new \RuntimeException(
                        'Gagal: formasi di sekolah tujuan sudah terisi penuh.'
                    );
                }

                // Mutasi guru ke sekolah tujuan. Pada penempatan linier
                // kelompok (tingkat 2), bidang tugas guru di sekolah baru
                // menjadi mapel yang diisi — bidang studi sertifikasinya
                // tidak berubah.
                $mutasi = [
                    'sekolah_id' => $tujuan->id,
                    'sekolah_saat_ini' => $tujuan->nama,
                    'sekolah_saat_ini_lat' => $tujuan->lat,
                    'sekolah_saat_ini_lng' => $tujuan->lng,
                ];
                if (strcasecmp($mapelIsi, (string) $guru->mapel) !== 0) {
                    $mutasi['mapel'] = $mapelIsi;
                }
                $guru->update($mutasi);

                // Catat berkurangnya kebutuhan formasi
                KebutuhanGuru::kurangi($tujuan->id, $mapelIsi);

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

                ActivityLog::catatAman('spk.setujui', $spk, 'sukses', [
                    'status_sebelum' => 'rekomendasi',
                    'status_sesudah' => 'disetujui',
                    'guru_id' => $guru->id,
                    'sekolah_tujuan_id' => $tujuan->id,
                    'mapel_tujuan' => $mapelIsi,
                    'tingkat_linieritas' => $spk->tingkatLinieritas(),
                ]);
            }, 3);
        } catch (\RuntimeException $e) {
            return back()->with('gagal', $e->getMessage());
        } catch (\Throwable $e) {
            // Kegagalan teknis (mis. deadlock setelah retry): jangan bocorkan
            // detail SQL ke pengguna; catat untuk administrator.
            \Log::error('Persetujuan SPK gagal', ['spk_id' => $spk->id, 'error' => $e->getMessage()]);

            return back()->with('gagal', 'Persetujuan gagal karena kendala teknis — silakan coba lagi.');
        }

        $pesan = 'Penempatan disetujui — guru sudah dimutasi ke sekolah tujuan.';
        if ($spk->mapel_tujuan && strcasecmp($spk->mapel_tujuan, (string) $spk->guru->mapel) !== 0) {
            $pesan .= ' Bidang tugas guru di sekolah baru menjadi '.$spk->mapel_tujuan.' (linier kelompok).';
        }

        return back()->with('sukses', $pesan);
    }

    // Tolak rekomendasi (kabid_ptk / kadis)
    public function tolak(Request $request, HasilSpk $spk)
    {
        // Alasan penolakan wajib (D) — keputusan harus dapat diaudit.
        $data = $request->validate([
            'catatan_admin' => ['required', 'string', 'max:1000'],
        ]);

        // FLOW-001: tolak adalah transisi status yang harus atomik —
        // kunci baris dan validasi ulang agar tidak menimpa persetujuan
        // yang berjalan bersamaan.
        try {
            DB::transaction(function () use ($spk, $data) {
                $terkunci = HasilSpk::whereKey($spk->id)->lockForUpdate()->firstOrFail();
                if ($terkunci->status !== 'rekomendasi') {
                    throw new \RuntimeException('Rekomendasi ini sudah diproses sebelumnya.');
                }
                $terkunci->update([
                    'status' => 'ditolak',
                    'diproses_oleh' => auth()->id(),
                    'catatan_admin' => $data['catatan_admin'],
                ]);

                ActivityLog::catatAman('spk.tolak', $terkunci, 'sukses', [
                    'status_sebelum' => 'rekomendasi',
                    'status_sesudah' => 'ditolak',
                    'alasan' => $data['catatan_admin'],
                ]);
            });
        } catch (\RuntimeException $e) {
            return back()->with('sukses', $e->getMessage());
        } catch (\Throwable $e) {
            \Log::error('Penolakan SPK gagal', ['spk_id' => $spk->id, 'error' => $e->getMessage()]);

            return back()->with('gagal', 'Penolakan gagal karena kendala teknis — silakan coba lagi.');
        }

        return back()->with('sukses', 'Rekomendasi penempatan ditolak.');
    }

    /**
     * Telaahan staf — dokumen bahan keputusan PPK.
     * Berisi dasar hukum, data guru, analisis (jarak, kuota, keluarga,
     * kepegawaian, linearitas, dampak sekolah asal), dan saran.
     * (kabid_ptk, kadis)
     */
    public function telaahan(HasilSpk $spk)
    {
        $spk->load(['guru.sekolah', 'sekolahTujuan', 'diprosesOleh']);

        $guru = $spk->guru;
        $dampakAsal = $this->petaDampakAsal(collect([$spk]))[$spk->id] ?? null;

        return view('spk.telaahan', [
            'spk' => $spk,
            'guru' => $guru,
            'dampakAsal' => $dampakAsal,
            'tanggal' => now()->locale('id')->translatedFormat('d F Y'),
        ]);
    }
}
