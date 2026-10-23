<?php

// Controller Guru dengan scope berbeda per peran:
//   guru           → hanya data dirinya sendiri (boleh ubah)
//   kepala_sekolah → CRUD guru di sekolahnya sendiri
//   ketua_pgri     → lihat se-kecamatan (read-only)
//   kabid_ptk      → CRUD penuh (administrator)
//   kadis          → lihat semua (reviewer, read-only)

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Guru;
use App\Models\Sekolah;
use App\Services\KonteksPemda;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class GuruController extends Controller
{
    // Aturan validasi dipakai ulang di store & update
    private function aturanValidasi(): array
    {
        return [
            'nip' => ['required', 'string', 'max:30'],
            'nama' => ['required', 'string', 'max:100'],
            'mapel' => ['required', 'string', 'max:50'],
            'jenjang' => ['required', 'in:SD,SMP,SMA'],
            'domisili_alamat' => ['required', 'string', 'max:255'],
            'kecamatan' => ['nullable', 'string', 'max:100'],
            'desa_kelurahan' => ['nullable', 'string', 'max:100'],
            'status_pernikahan' => ['nullable', 'in:belum_kawin,kawin,cerai_hidup,cerai_mati'],
            'status_kepegawaian' => ['nullable', 'in:pns,pppk,honorer'],
            'sudah_sertifikasi' => ['nullable', 'boolean'],
            'mapel_sertifikasi' => ['nullable', 'string', 'max:50'],
            'sekolah_id' => ['nullable', 'exists:sekolah,id'],
            'domisili_lat' => ['required', 'numeric', 'between:-90,90'],
            'domisili_lng' => ['required', 'numeric', 'between:-180,180'],
            'sekolah_saat_ini' => ['required', 'string', 'max:100'],
            'sekolah_saat_ini_lat' => ['nullable', 'numeric', 'between:-90,90'],
            'sekolah_saat_ini_lng' => ['nullable', 'numeric', 'between:-180,180'],
        ];
    }

    /**
     * Query dasar yang sudah dibatasi sesuai scope peran pengguna.
     */
    private function queryScope()
    {
        $user = auth()->user();
        $query = Guru::with('sekolah');

        if ($user->role === 'guru') {
            $query->where('id', $user->guru_id);
        } elseif ($user->role === 'kepala_sekolah') {
            // Hanya guru yang bertugas di sekolahnya
            $query->where('sekolah_id', $user->sekolah_id);
        } elseif ($user->role === 'ketua_pgri') {
            $query->where('kecamatan', $user->kecamatan);
        }
        // kabid_ptk, kadis: semua data

        return $query;
    }

    /**
     * Halaman index dirender komponen Livewire TabelGuru
     * (pencarian, filter, sorting, pagination tanpa reload).
     */
    /**
     * Daftar kecamatan unik dari data sekolah (untuk filter & dropdown form).
     */
    private function daftarKecamatan()
    {
        return Sekolah::whereNotNull('kecamatan')
            ->distinct()->orderBy('kecamatan')->pluck('kecamatan');
    }

    /**
     * Peta NAMA KECAMATAN (huruf besar) => kode Kemendagri untuk kecamatan
     * se-Kab. Mandailing Natal (1213) — jembatan nama kecamatan di form ke
     * API wilayah yang berbasis kode (untuk memuat daftar desa).
     */
    private function petaKodeKecamatan()
    {
        return KonteksPemda::petaKodeKecamatan();
    }

    public function create()
    {
        return view('guru.form', [
            'guru' => new Guru,
            'aksi' => 'Tambah',
            'daftarSekolah' => Sekolah::orderBy('nama')->get(),
            'daftarKecamatan' => $this->daftarKecamatan(),
            'petaKodeKecamatan' => $this->petaKodeKecamatan(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->aturanValidasi());
        $request->validate(['nip' => ['unique:guru,nip']]);

        // Kepala sekolah: guru baru otomatis masuk sekolahnya
        if (auth()->user()->role === 'kepala_sekolah') {
            $data['sekolah_id'] = auth()->user()->sekolah_id;
        }

        $guru = Guru::create($data);

        ActivityLog::catatAman('guru.tambah', $guru, 'sukses', [
            'kolom' => array_keys($data),
        ]);

        return redirect()->route('guru.index')
            ->with('sukses', 'Data guru berhasil ditambahkan.');
    }

    public function edit(Guru $guru)
    {
        $this->otorisasiAkses($guru);

        return view('guru.form', [
            'guru' => $guru,
            'aksi' => 'Ubah',
            'daftarSekolah' => Sekolah::orderBy('nama')->get(),
            'daftarKecamatan' => $this->daftarKecamatan(),
            'petaKodeKecamatan' => $this->petaKodeKecamatan(),
        ]);
    }

    public function update(Request $request, Guru $guru)
    {
        $this->otorisasiAkses($guru);

        $data = $request->validate($this->aturanValidasi());
        $request->validate(['nip' => ['unique:guru,nip,'.$guru->id]]);

        // SEC-002: batasi kolom yang boleh diubah per peran. Kepemilikan record
        // bukan berarti boleh mengubah atribut administratif.
        //   guru           → data pribadi saja (tanpa NIP, mapel, jenjang, sekolah)
        //   kepala_sekolah → semua kecuali NIP (identitas kepegawaian milik kabid)
        //   kabid_ptk      → semua kolom
        $kolomBoleh = match (auth()->user()->role) {
            'guru' => ['nama', 'domisili_alamat', 'desa_kelurahan', 'kecamatan',
                'status_pernikahan', 'domisili_lat', 'domisili_lng'],
            'kepala_sekolah' => array_diff(
                array_keys($this->aturanValidasi()), ['nip']),
            default => array_keys($this->aturanValidasi()),
        };
        $data = array_intersect_key($data, array_flip($kolomBoleh));

        // Kepala sekolah tidak bisa memindahkan guru ke sekolah lain
        if (auth()->user()->role === 'kepala_sekolah') {
            $data['sekolah_id'] = auth()->user()->sekolah_id;
        }

        $guru->update($data);

        ActivityLog::catatAman('guru.ubah', $guru, 'sukses', [
            'kolom' => array_keys($data),
        ]);

        return redirect()->route('guru.index')
            ->with('sukses', 'Data guru berhasil diperbarui.');
    }

    public function destroy(Guru $guru)
    {
        $this->otorisasiAkses($guru);
        $guruId = $guru->id;
        $guru->delete();

        ActivityLog::catatAman('guru.hapus', null, 'sukses', [
            'guru_id' => $guruId,
        ]);

        return redirect()->route('guru.index')
            ->with('sukses', 'Data guru berhasil dihapus.');
    }

    // ================= IMPORT EXCEL (khusus kabid_ptk) =================

    /**
     * Nama kolom file import (baris pertama). Kolom bertanda * wajib diisi.
     *   nip*, nama*, mapel*, jenjang*, domisili_alamat*, kecamatan,
     *   domisili_lat*, domisili_lng*, sekolah_saat_ini*,
     *   sekolah_saat_ini_lat, sekolah_saat_ini_lng, npsn, status_pernikahan
     */
    private const KOLOM_IMPORT = [
        'nip', 'nama', 'mapel', 'jenjang', 'domisili_alamat', 'kecamatan',
        'domisili_lat', 'domisili_lng', 'sekolah_saat_ini',
        'sekolah_saat_ini_lat', 'sekolah_saat_ini_lng', 'npsn',
        'status_pernikahan', 'status_kepegawaian', 'sudah_sertifikasi',
        'mapel_sertifikasi',
    ];

    public function importForm()
    {
        return view('guru.import', ['kolom' => self::KOLOM_IMPORT]);
    }

    /**
     * Unduh template Excel contoh (header + 2 baris contoh).
     */
    /**
     * Export Excel data guru (mengikuti filter & scope yang aktif).
     */
    public function export(Request $request)
    {
        $query = $this->queryScope();
        $kataKunci = trim($request->get('q', ''));
        if ($kataKunci !== '') {
            $query->where(function ($w) use ($kataKunci) {
                $w->where('nama', 'like', "%{$kataKunci}%")
                    ->orWhere('nip', 'like', "%{$kataKunci}%")
                    ->orWhere('mapel', 'like', "%{$kataKunci}%")
                    ->orWhere('domisili_alamat', 'like', "%{$kataKunci}%")
                    ->orWhere('sekolah_saat_ini', 'like', "%{$kataKunci}%");
            });
        }
        if (($kec = $request->get('kecamatan', '')) !== '') {
            $query->where('kecamatan', $kec);
        }
        $data = $query->orderBy('nama')->get();

        $spreadsheet = new Spreadsheet;
        $lembar = $spreadsheet->getActiveSheet();
        $lembar->fromArray([[
            'NIP', 'Nama', 'Mapel', 'Jenjang', 'Status Pernikahan',
            'Status Kepegawaian', 'Sertifikasi', 'Domisili', 'Kecamatan',
            'Sekolah Saat Ini',
        ]], null, 'A1');
        $baris = 2;
        foreach ($data as $g) {
            $lembar->fromArray([[
                $g->nipTersamar(), $g->nama, $g->mapel, $g->jenjang,
                $g->labelStatusPernikahan(), $g->labelStatusKepegawaian(),
                $g->sudah_sertifikasi ? 'Ya ('.($g->mapel_sertifikasi ?? '-').')' : 'Belum',
                $g->domisili_alamat, $g->kecamatan, $g->sekolah_saat_ini,
            ]], null, "A{$baris}");
            $baris++;
        }
        foreach (range('A', 'J') as $kolom) {
            $lembar->getColumnDimension($kolom)->setAutoSize(true);
        }

        $namaFile = 'data-guru-'.date('Ymd-His').'.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            (new Xlsx($spreadsheet))->save('php://output');
        }, $namaFile, ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
    }

    /**
     * Tampilan cetak/PDF data guru (lewat dialog cetak browser).
     */
    public function cetak(Request $request)
    {
        $query = $this->queryScope();
        $kataKunci = trim($request->get('q', ''));
        if ($kataKunci !== '') {
            $query->where(function ($w) use ($kataKunci) {
                $w->where('nama', 'like', "%{$kataKunci}%")
                    ->orWhere('nip', 'like', "%{$kataKunci}%")
                    ->orWhere('mapel', 'like', "%{$kataKunci}%");
            });
        }
        if (($kec = $request->get('kecamatan', '')) !== '') {
            $query->where('kecamatan', $kec);
        }

        return view('guru.cetak', ['data' => $query->orderBy('nama')->get()]);
    }

    public function unduhTemplate()
    {
        $spreadsheet = new Spreadsheet;
        $lembar = $spreadsheet->getActiveSheet();
        $lembar->fromArray([self::KOLOM_IMPORT], null, 'A1');
        $lembar->fromArray([
            ['198501012010011001', 'Siti Aminah', 'Matematika', 'SD',
                'Jl. Merdeka No.10, Panyabungan', 'Panyabungan',
                0.855, 99.555, 'SD NEGERI 076 PANYABUNGAN', 0.858, 99.56, '10208250', 'kawin', 'pns', 1, 'Matematika'],
            ['198703152012022002', 'Budi Hartono', 'IPA', 'SMP',
                'Jl. Sudirman No.5, Panyabungan', 'Panyabungan',
                0.87, 99.57, 'SMP NEGERI 1 PANYABUNGAN', 0.8732, 99.7175, '10208088', 'belum_kawin', 'pppk', 0, ''],
        ], null, 'A2');
        foreach (range('A', 'P') as $kolom) {
            $lembar->getColumnDimension($kolom)->setAutoSize(true);
        }

        $namaBerkas = 'template-import-guru.xlsx';
        $berkasSementara = tempnam(sys_get_temp_dir(), 'tpl').'.xlsx';
        (new Xlsx($spreadsheet))->save($berkasSementara);

        return response()->download($berkasSementara, $namaBerkas)->deleteFileAfterSend();
    }

    /**
     * Proses file Excel/CSV: baris valid disimpan, baris gagal dilaporkan.
     */
    public function import(Request $request)
    {
        $request->validate([
            'berkas' => ['required', 'file', 'max:5120', 'mimes:xlsx,xls,csv'],
        ]);

        try {
            $lembar = IOFactory::load($request->file('berkas')->getRealPath())
                ->getActiveSheet()->toArray(null, true, true, false);
        } catch (\Throwable $e) {
            return back()->withErrors(['berkas' => 'File tidak bisa dibaca sebagai Excel/CSV.']);
        }

        $header = array_map(fn ($h) => strtolower(trim((string) $h)), $lembar[0] ?? []);
        if ($header !== self::KOLOM_IMPORT) {
            return back()->withErrors(['berkas' => 'Header tidak sesuai template. Unduh template dulu lewat tombol di bawah.']);
        }

        // Peta pembantu: NIP yang sudah ada + NPSN→sekolah_id + kecamatan valid
        $nipAda = Guru::pluck('nip')->map(fn ($n) => trim((string) $n))->flip();
        $sekolahOlehNpsn = Sekolah::pluck('id', 'npsn');
        $kecamatanValid = Sekolah::whereNotNull('kecamatan')->distinct()
            ->pluck('kecamatan')->mapWithKeys(fn ($k) => [strtolower($k) => $k]);

        $berhasil = 0;
        $gagal = [];
        $nipDiBerkas = [];

        DB::beginTransaction();
        try {
            foreach (array_slice($lembar, 1) as $i => $baris) {
                $noBaris = $i + 2; // +1 header, +1 basis-1
                $data = array_combine(self::KOLOM_IMPORT, array_pad($baris, 13, null));
                $data = array_map(fn ($v) => is_string($v) ? trim($v) : $v, $data);
                if (collect($data)->every(fn ($v) => $v === null || $v === '')) {
                    continue; // lewati baris kosong
                }

                $error = $this->validasiBarisImport($data, $nipAda, $nipDiBerkas, $sekolahOlehNpsn, $kecamatanValid);
                if ($error) {
                    $gagal[] = "Baris {$noBaris}: {$error}";

                    continue;
                }

                Guru::create([
                    'nip' => $data['nip'], 'nama' => $data['nama'],
                    'mapel' => $data['mapel'], 'jenjang' => strtoupper($data['jenjang']),
                    'domisili_alamat' => $data['domisili_alamat'],
                    'status_pernikahan' => ($data['status_pernikahan'] ?? '') === ''
                        ? 'belum_kawin' : strtolower($data['status_pernikahan']),
                    'status_kepegawaian' => ($data['status_kepegawaian'] ?? '') === ''
                        ? 'pns' : strtolower($data['status_kepegawaian']),
                    'sudah_sertifikasi' => in_array(strtolower(trim((string) ($data['sudah_sertifikasi'] ?? ''))), ['1', 'ya'], true),
                    'mapel_sertifikasi' => ($data['mapel_sertifikasi'] ?? '') === ''
                        ? null : trim($data['mapel_sertifikasi']),
                    'kecamatan' => $data['kecamatan'] === ''
                        ? null : $kecamatanValid[strtolower($data['kecamatan'])],
                    'sekolah_id' => $data['npsn'] === ''
                        ? null : $sekolahOlehNpsn[trim((string) $data['npsn'])],
                    'domisili_lat' => $data['domisili_lat'], 'domisili_lng' => $data['domisili_lng'],
                    'sekolah_saat_ini' => $data['sekolah_saat_ini'],
                    'sekolah_saat_ini_lat' => $this->angkaAtauNull($data['sekolah_saat_ini_lat']),
                    'sekolah_saat_ini_lng' => $this->angkaAtauNull($data['sekolah_saat_ini_lng']),
                ]);
                $nipDiBerkas[trim((string) $data['nip'])] = true;
                $nipAda[trim((string) $data['nip'])] = true;
                $berhasil++;
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->withErrors(['berkas' => 'Import dibatalkan: '.$e->getMessage()]);
        }

        return redirect()->route('guru.index')->with([
            'sukses' => "Import selesai: {$berhasil} guru ditambahkan"
                .(count($gagal) ? ', '.count($gagal).' baris gagal.' : '.'),
            'import_gagal' => $gagal,
        ]);
    }

    /**
     * Validasi satu baris import. Mengembalikan pesan error atau null jika valid.
     */
    private function validasiBarisImport(array $d, $nipAda, array $nipDiBerkas, $sekolahOlehNpsn, $kecamatanValid): ?string
    {
        $wajib = ['nip', 'nama', 'mapel', 'jenjang', 'domisili_alamat',
            'domisili_lat', 'domisili_lng', 'sekolah_saat_ini'];
        foreach ($wajib as $k) {
            if ($d[$k] === null || $d[$k] === '') {
                return "kolom '{$k}' wajib diisi.";
            }
        }
        if (strlen((string) $d['nip']) > 30) {
            return 'NIP maksimal 30 karakter.';
        }
        $nip = trim((string) $d['nip']);
        if (isset($nipAda[$nip]) || isset($nipDiBerkas[$nip])) {
            return "NIP '{$nip}' sudah terdata (duplikat).";
        }
        if (strlen((string) $d['nama']) > 100 || strlen((string) $d['mapel']) > 50
            || strlen((string) $d['domisili_alamat']) > 255
            || strlen((string) $d['sekolah_saat_ini']) > 100) {
            return 'ada teks yang melebihi batas panjang kolom.';
        }
        if (! in_array(strtoupper($d['jenjang']), ['SD', 'SMP', 'SMA'], true)) {
            return "jenjang '{$d['jenjang']}' harus SD/SMP/SMA.";
        }
        if ($d['kecamatan'] !== '' && ! isset($kecamatanValid[strtolower($d['kecamatan'])])) {
            return "kecamatan '{$d['kecamatan']}' tidak dikenal (pakai nama kecamatan Madina).";
        }
        foreach (['domisili_lat' => [-90, 90], 'domisili_lng' => [-180, 180]] as $k => [$min, $maks]) {
            if (! is_numeric($d[$k]) || $d[$k] < $min || $d[$k] > $maks) {
                return "kolom '{$k}' harus angka antara {$min}–{$maks}.";
            }
        }
        foreach (['sekolah_saat_ini_lat' => [-90, 90], 'sekolah_saat_ini_lng' => [-180, 180]] as $k => [$min, $maks]) {
            if ($d[$k] !== null && $d[$k] !== '' && (! is_numeric($d[$k]) || $d[$k] < $min || $d[$k] > $maks)) {
                return "kolom '{$k}' harus angka antara {$min}–{$maks} atau kosong.";
            }
        }
        if ($d['npsn'] !== '' && ! isset($sekolahOlehNpsn[trim((string) $d['npsn'])])) {
            return "NPSN '{$d['npsn']}' tidak ditemukan di data sekolah.";
        }
        if ($d['status_pernikahan'] !== null && $d['status_pernikahan'] !== ''
            && ! array_key_exists(strtolower($d['status_pernikahan']), Guru::STATUS_PERNIKAHAN)) {
            return "kolom 'status_pernikahan' harus salah satu: "
                .implode(', ', array_keys(Guru::STATUS_PERNIKAHAN)).'.';
        }
        if ($d['status_kepegawaian'] !== null && $d['status_kepegawaian'] !== ''
            && ! array_key_exists(strtolower($d['status_kepegawaian']), Guru::STATUS_KEPEGAWAIAN)) {
            return "kolom 'status_kepegawaian' harus salah satu: "
                .implode(', ', array_keys(Guru::STATUS_KEPEGAWAIAN)).'.';
        }
        if ($d['sudah_sertifikasi'] !== null && $d['sudah_sertifikasi'] !== ''
            && ! in_array(strtolower(trim((string) $d['sudah_sertifikasi'])), ['0', '1', 'ya', 'tidak'], true)) {
            return "kolom 'sudah_sertifikasi' harus 0/1 atau ya/tidak.";
        }

        return null;
    }

    private function angkaAtauNull($nilai): ?float
    {
        return ($nilai === null || $nilai === '') ? null : (float) $nilai;
    }

    /**
     * Batasan akses per peran untuk ubah/hapus:
     *   guru           → hanya dirinya sendiri
     *   kepala_sekolah → hanya guru di sekolahnya
     *   kabid_ptk      → semua (diatur middleware)
     */
    private function otorisasiAkses(Guru $guru): void
    {
        $user = auth()->user();
        if ($user->role === 'guru' && $guru->id !== $user->guru_id) {
            abort(403, 'Anda hanya boleh mengelola data diri sendiri.');
        }
        if ($user->role === 'kepala_sekolah' && $guru->sekolah_id !== $user->sekolah_id) {
            abort(403, 'Anda hanya boleh mengelola guru di sekolah Anda.');
        }
    }
}
