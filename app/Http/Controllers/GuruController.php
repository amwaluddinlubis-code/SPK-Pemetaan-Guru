<?php
// Controller Guru dengan scope berbeda per peran:
//   guru           → hanya data dirinya sendiri (boleh ubah)
//   kepala_sekolah → CRUD guru di sekolahnya sendiri
//   ketua_pgri     → lihat se-kecamatan (read-only)
//   kabid_ptk      → CRUD penuh (administrator)
//   kadis          → lihat semua (reviewer, read-only)

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\Sekolah;
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
            'nip'                  => ['required', 'string', 'max:30'],
            'nama'                 => ['required', 'string', 'max:100'],
            'mapel'                => ['required', 'string', 'max:50'],
            'jenjang'              => ['required', 'in:SD,SMP,SMA'],
            'domisili_alamat'      => ['required', 'string', 'max:255'],
            'kecamatan'            => ['nullable', 'string', 'max:100'],
            'sekolah_id'           => ['nullable', 'exists:sekolah,id'],
            'domisili_lat'         => ['required', 'numeric', 'between:-90,90'],
            'domisili_lng'         => ['required', 'numeric', 'between:-180,180'],
            'sekolah_saat_ini'     => ['required', 'string', 'max:100'],
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

    public function index(Request $request)
    {
        $query = $this->queryScope();

        // Pencarian teks: nama, NIP, mapel, alamat domisili, sekolah saat ini
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

        // Filter kecamatan (disembunyikan di view jika scope peran sudah tetap)
        $filterKecamatan = $request->get('kecamatan', '');
        if ($filterKecamatan !== '') {
            $query->where('kecamatan', $filterKecamatan);
        }

        // Sorting kolom: ?sort=nama&arah=asc (hanya kolom aman di bawah)
        $kolomBolehSort = ['nama', 'nip', 'mapel', 'kecamatan'];
        $sort = in_array($request->get('sort'), $kolomBolehSort)
            ? $request->get('sort') : 'nama';
        $arah = $request->get('arah') === 'desc' ? 'desc' : 'asc';
        $query->orderBy($sort, $arah);

        $daftarGuru = $query->paginate(15)->withQueryString();
        $daftarKecamatan = $this->daftarKecamatan();

        return view('guru.index', compact('daftarGuru', 'daftarKecamatan'));
    }

    /**
     * Daftar kecamatan unik dari data sekolah (untuk filter & dropdown form).
     */
    private function daftarKecamatan()
    {
        return Sekolah::whereNotNull('kecamatan')
            ->distinct()->orderBy('kecamatan')->pluck('kecamatan');
    }

    public function create()
    {
        return view('guru.form', [
            'guru' => new Guru(),
            'aksi' => 'Tambah',
            'daftarSekolah' => Sekolah::orderBy('nama')->get(),
            'daftarKecamatan' => $this->daftarKecamatan(),
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

        Guru::create($data);

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
        ]);
    }

    public function update(Request $request, Guru $guru)
    {
        $this->otorisasiAkses($guru);

        $data = $request->validate($this->aturanValidasi());
        $request->validate(['nip' => ['unique:guru,nip,' . $guru->id]]);

        // Kepala sekolah tidak bisa memindahkan guru ke sekolah lain
        if (auth()->user()->role === 'kepala_sekolah') {
            $data['sekolah_id'] = auth()->user()->sekolah_id;
        }

        $guru->update($data);

        return redirect()->route('guru.index')
            ->with('sukses', 'Data guru berhasil diperbarui.');
    }

    public function destroy(Guru $guru)
    {
        $this->otorisasiAkses($guru);
        $guru->delete();

        return redirect()->route('guru.index')
            ->with('sukses', 'Data guru berhasil dihapus.');
    }

    // ================= IMPORT EXCEL (khusus kabid_ptk) =================

    /**
     * Nama kolom file import (baris pertama). Kolom bertanda * wajib diisi.
     *   nip*, nama*, mapel*, jenjang*, domisili_alamat*, kecamatan,
     *   domisili_lat*, domisili_lng*, sekolah_saat_ini*,
     *   sekolah_saat_ini_lat, sekolah_saat_ini_lng, npsn
     */
    private const KOLOM_IMPORT = [
        'nip', 'nama', 'mapel', 'jenjang', 'domisili_alamat', 'kecamatan',
        'domisili_lat', 'domisili_lng', 'sekolah_saat_ini',
        'sekolah_saat_ini_lat', 'sekolah_saat_ini_lng', 'npsn',
    ];

    public function importForm()
    {
        return view('guru.import', ['kolom' => self::KOLOM_IMPORT]);
    }

    /**
     * Unduh template Excel contoh (header + 2 baris contoh).
     */
    public function unduhTemplate()
    {
        $spreadsheet = new Spreadsheet();
        $lembar = $spreadsheet->getActiveSheet();
        $lembar->fromArray([self::KOLOM_IMPORT], null, 'A1');
        $lembar->fromArray([
            ['198501012010011001', 'Siti Aminah', 'Matematika', 'SD',
             'Jl. Merdeka No.10, Panyabungan', 'Panyabungan',
             0.855, 99.555, 'SD NEGERI 076 PANYABUNGAN', 0.858, 99.56, '10208250'],
            ['198703152012022002', 'Budi Hartono', 'IPA', 'SMP',
             'Jl. Sudirman No.5, Panyabungan', 'Panyabungan',
             0.87, 99.57, 'SMP NEGERI 1 PANYABUNGAN', 0.8732, 99.7175, '10208088'],
        ], null, 'A2');
        foreach (range('A', 'L') as $kolom) {
            $lembar->getColumnDimension($kolom)->setAutoSize(true);
        }

        $namaBerkas = 'template-import-guru.xlsx';
        $berkasSementara = tempnam(sys_get_temp_dir(), 'tpl') . '.xlsx';
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
            return back()->withErrors(['berkas' =>
                'Header tidak sesuai template. Unduh template dulu lewat tombol di bawah.']);
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
                $data = array_combine(self::KOLOM_IMPORT, array_pad($baris, 12, null));
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
            return back()->withErrors(['berkas' => 'Import dibatalkan: ' . $e->getMessage()]);
        }

        return redirect()->route('guru.index')->with([
            'sukses' => "Import selesai: {$berhasil} guru ditambahkan"
                . (count($gagal) ? ', ' . count($gagal) . ' baris gagal.' : '.'),
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
