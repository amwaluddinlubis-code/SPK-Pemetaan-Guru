<?php

// Impor massal guru dari berkas persiapan P3K (perintah AMWAL,
// 23 Okt 2026): membaca sheet "Siap Impor" & "Sekolah Baru" dari berkas
// Impor_Guru_P3K_2024.xlsx, menambahkan sekolah yang belum ada,
// mengimpor seluruh guru, dan membuatkan akun login (username &
// password bawaan = NIP) bagi guru yang ber-NIP.
//
// Dengan --ganti-semua, seluruh data guru lama (beserta hasil SPK,
// usulan, dan akun gurunya) dihapus lebih dulu — dipakai saat mengganti
// data contoh dengan data asli. Berkas sumber TIDAK diubah.

namespace App\Console\Commands;

use App\Models\Guru;
use App\Models\HasilSpk;
use App\Models\Sekolah;
use App\Models\User;
use App\Models\UsulanPenempatan;
use App\Services\AkunGuru;
use App\Services\AnalisisKebutuhanGuru;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class ImporGuruP3k extends Command
{
    protected $signature = 'guru:impor-p3k
        {berkas : Path berkas persiapan impor (.xlsx)}
        {--ganti-semua : Hapus seluruh data guru lama sebelum impor}';

    protected $description = 'Impor guru massal dari berkas persiapan P3K + buatkan akun NIP';

    public function handle(): int
    {
        $path = $this->argument('berkas');
        if (! is_file($path)) {
            $this->error("Berkas tidak ditemukan: {$path}");

            return self::FAILURE;
        }

        try {
            $wb = IOFactory::load($path);
        } catch (\Throwable $e) {
            $this->error('Berkas tidak bisa dibaca sebagai Excel: '.$e->getMessage());

            return self::FAILURE;
        }

        // --- 1. Sekolah baru (sheet "Sekolah Baru") ---
        $sekolahBaru = 0;
        if ($wb->sheetNameExists('Sekolah Baru')) {
            $rows = $wb->getSheetByName('Sekolah Baru')->toArray(null, true, true, false);
            foreach (array_slice($rows, 1) as $r) {
                $nama = trim((string) ($r[1] ?? ''));
                if ($nama === '') {
                    continue;
                }
                $ada = Sekolah::withoutGlobalScopes()->where('nama', $nama)->exists();
                if (! $ada) {
                    Sekolah::withoutGlobalScopes()->create([
                        'npsn' => null,
                        'nama' => $nama,
                        'jenjang' => trim((string) ($r[2] ?? '')) ?: 'SD',
                        'kecamatan' => trim((string) ($r[3] ?? '')) ?: null,
                        'rombel' => 0,
                        'kategori_daerah' => 'biasa',
                    ]);
                    $sekolahBaru++;
                }
            }
        }
        $this->info("Sekolah baru ditambahkan: {$sekolahBaru}");

        // Peta nama sekolah → id (tanpa scope konteks pemda).
        $petaSekolah = Sekolah::withoutGlobalScopes()->pluck('id', 'nama');

        // --- 2. Hapus guru lama bila diminta ---
        if ($this->option('ganti-semua')) {
            $jumlahLama = Guru::withoutGlobalScopes()->count();
            DB::transaction(function () {
                $ids = Guru::withoutGlobalScopes()->pluck('id');
                HasilSpk::whereIn('guru_id', $ids)->delete();
                UsulanPenempatan::whereIn('guru_id', $ids)->delete();
                User::whereIn('guru_id', $ids)->where('role', 'guru')->delete();
                Guru::withoutGlobalScopes()->delete();
            });
            $this->warn("Data guru lama dihapus: {$jumlahLama} guru (beserta hasil SPK, usulan & akun gurunya).");
        }

        // --- 3. Impor guru (sheet "Siap Impor") ---
        $sheet = $wb->getSheetByName('Siap Impor') ?? $wb->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, false);
        $header = array_map(fn ($h) => trim((string) $h), $rows[0] ?? []);
        $kol = array_flip($header);
        $ambil = fn (array $r, string $nama) => isset($kol[$nama]) ? trim((string) ($r[$kol[$nama]] ?? '')) : '';

        $statusPeta = [
            'pns' => 'pns',
            'pppk' => 'pppk',
            'pppk paruh waktu' => 'pppk_paruh_waktu',
            'honorer' => 'honorer',
        ];

        $diimpor = $dilewati = $akunDibuat = $tanpaNip = 0;
        $nipTerlihat = [];

        DB::transaction(function () use (
            $rows, $ambil, $petaSekolah, $statusPeta,
            &$diimpor, &$dilewati, &$akunDibuat, &$tanpaNip, &$nipTerlihat
        ) {
            foreach (array_slice($rows, 1) as $r) {
                $nama = $ambil($r, 'NAMA');
                if ($nama === '') {
                    continue;
                }
                $nip = $ambil($r, 'NIP') ?: null;

                if ($nip !== null) {
                    if (isset($nipTerlihat[$nip])
                        || Guru::withoutGlobalScopes()->where('nip', $nip)->exists()) {
                        $dilewati++;

                        continue;
                    }
                    $nipTerlihat[$nip] = true;
                }

                $namaSekolah = $ambil($r, 'SEKOLAH (SPK)');
                $tanggal = $ambil($r, 'TANGGAL_LAHIR');
                if (is_numeric($tanggal)) {
                    $tanggal = ExcelDate::excelToDateTimeObject((float) $tanggal)->format('Y-m-d');
                }

                $guru = Guru::withoutGlobalScopes()->create([
                    'nip' => $nip,
                    'nama' => $nama,
                    'tempat_lahir' => $ambil($r, 'TEMPAT_LAHIR') ?: null,
                    'tanggal_lahir' => $tanggal ?: null,
                    'mapel' => AnalisisKebutuhanGuru::kanonis($ambil($r, 'MAPEL')),
                    'jenjang' => strtoupper($ambil($r, 'JENJANG')) ?: 'SD',
                    'pendidikan_terakhir' => $ambil($r, 'PENDIDIKAN TERAKHIR (perkiraan)') ?: null,
                    'status_kepegawaian' => $statusPeta[strtolower($ambil($r, 'STATUS_KEPEGAWAIAN'))] ?? 'pppk',
                    'status_pernikahan' => 'belum_kawin',
                    'sudah_sertifikasi' => false,
                    'kecamatan' => $ambil($r, 'KECAMATAN') ?: null,
                    'sekolah_id' => $namaSekolah !== '' ? ($petaSekolah[$namaSekolah] ?? null) : null,
                    'sekolah_saat_ini' => $namaSekolah,
                ]);
                $diimpor++;
                if ($nip === null) {
                    $tanpaNip++;
                }

                if (AkunGuru::buatkan($guru)) {
                    $akunDibuat++;
                }
            }
        });

        $this->info("Guru diimpor: {$diimpor} | dilewati (NIP ganda): {$dilewati} | tanpa NIP: {$tanpaNip} | akun login dibuat: {$akunDibuat}");

        return self::SUCCESS;
    }
}
