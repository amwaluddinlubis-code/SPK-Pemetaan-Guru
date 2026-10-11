<?php
// Controller Sekolah dengan scope berbeda per peran:
//   kepala_sekolah → hanya sekolahnya (bisa ubah + atur kebutuhan guru)
//   ketua_pgri     → lihat se-kecamatan (read-only)
//   guru           → lihat semua (read-only, untuk rekomendasi)
//   kabid_ptk      → CRUD penuh
//   kadis          → CRUD penuh

namespace App\Http\Controllers;

use App\Models\KebutuhanGuru;
use App\Models\Sekolah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class SekolahController extends Controller
{
    /**
     * Kolom import Excel/CSV sekolah (urutan persis seperti template).
     */
    private const KOLOM_IMPORT = [
        'npsn', 'nama', 'alamat', 'kecamatan', 'lat', 'lng',
    ];
    /**
     * Query dasar yang sudah dibatasi sesuai scope peran pengguna.
     */
    private function queryScope()
    {
        $user = auth()->user();
        $query = Sekolah::with('kebutuhan');

        if ($user->role === 'kepala_sekolah') {
            // Kepala sekolah hanya melihat sekolahnya sendiri
            $query->where('id', $user->sekolah_id);
        } elseif ($user->role === 'ketua_pgri') {
            // Ketua PGRI hanya melihat se-kecamatan
            $query->where('kecamatan', $user->kecamatan);
        }
        // guru, kabid_ptk, kadis: semua data

        return $query;
    }

    public function index(Request $request)
    {
        $query = $this->queryScope();

        // Pencarian teks: nama, NPSN, alamat
        $kataKunci = trim($request->get('q', ''));
        if ($kataKunci !== '') {
            $query->where(function ($w) use ($kataKunci) {
                $w->where('nama', 'like', "%{$kataKunci}%")
                    ->orWhere('npsn', 'like', "%{$kataKunci}%")
                    ->orWhere('alamat', 'like', "%{$kataKunci}%");
            });
        }

        // Filter kecamatan (disembunyikan di view jika scope peran sudah tetap)
        $filterKecamatan = $request->get('kecamatan', '');
        if ($filterKecamatan !== '') {
            $query->where('kecamatan', $filterKecamatan);
        }

        // Sorting kolom: ?sort=nama&arah=asc (hanya kolom aman di bawah)
        $kolomBolehSort = ['nama', 'npsn', 'kecamatan'];
        $sort = in_array($request->get('sort'), $kolomBolehSort)
            ? $request->get('sort') : 'nama';
        $arah = $request->get('arah') === 'desc' ? 'desc' : 'asc';
        $query->orderBy($sort, $arah);

        $daftarSekolah = $query->paginate(15)->withQueryString();
        $daftarKecamatan = Sekolah::whereNotNull('kecamatan')
            ->distinct()->orderBy('kecamatan')->pluck('kecamatan');

        return view('sekolah.index', compact('daftarSekolah', 'daftarKecamatan'));
    }

    public function create()
    {
        return view('sekolah.form', [
            'sekolah' => new Sekolah(),
            'aksi' => 'Tambah',
            'daftarKecamatan' => $this->daftarKecamatanForm(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validasi($request);

        DB::transaction(function () use ($data) {
            $sekolah = Sekolah::create([
                'npsn' => $data['npsn'], 'nama' => $data['nama'],
                'alamat' => $data['alamat'], 'kecamatan' => $data['kecamatan'] ?? null,
                'lat' => $data['lat'], 'lng' => $data['lng'],
            ]);
            $this->simpanKebutuhan($sekolah, $data['kebutuhan'] ?? []);
        });

        return redirect()->route('sekolah.index')
            ->with('sukses', 'Data sekolah berhasil ditambahkan.');
    }

    public function edit(Sekolah $sekolah)
    {
        $this->otorisasiUbah($sekolah);
        $sekolah->load('kebutuhan');
        return view('sekolah.form', [
            'sekolah' => $sekolah,
            'aksi' => 'Ubah',
            'daftarKecamatan' => $this->daftarKecamatanForm(),
        ]);
    }

    public function update(Request $request, Sekolah $sekolah)
    {
        $this->otorisasiUbah($sekolah);
        $data = $this->validasi($request, $sekolah->id);

        DB::transaction(function () use ($sekolah, $data) {
            $sekolah->update([
                'npsn' => $data['npsn'], 'nama' => $data['nama'],
                'alamat' => $data['alamat'], 'kecamatan' => $data['kecamatan'] ?? null,
                'lat' => $data['lat'], 'lng' => $data['lng'],
            ]);
            // Hapus kebutuhan lama, simpan yang baru (cara paling sederhana & aman)
            $sekolah->kebutuhan()->delete();
            $this->simpanKebutuhan($sekolah, $data['kebutuhan'] ?? []);
        });

        return redirect()->route('sekolah.index')
            ->with('sukses', 'Data sekolah berhasil diperbarui.');
    }

    public function destroy(Sekolah $sekolah)
    {
        $sekolah->delete(); // kebutuhan ikut terhapus (cascade)

        return redirect()->route('sekolah.index')
            ->with('sukses', 'Data sekolah berhasil dihapus.');
    }

    /**
     * Daftar kecamatan unik dari data sekolah (untuk dropdown form).
     */
    private function daftarKecamatanForm()
    {
        return Sekolah::whereNotNull('kecamatan')
            ->distinct()->orderBy('kecamatan')->pluck('kecamatan');
    }

    /**
     * Kepala sekolah hanya boleh mengubah sekolahnya sendiri.
     */
    private function otorisasiUbah(Sekolah $sekolah): void
    {
        $user = auth()->user();
        if ($user->role === 'kepala_sekolah' && $sekolah->id !== $user->sekolah_id) {
            abort(403, 'Anda hanya boleh mengubah sekolah Anda sendiri.');
        }
    }

    private function validasi(Request $request, ?int $kecualiId = null): array
    {
        return $request->validate([
            'npsn'   => ['required', 'string', 'max:20', 'unique:sekolah,npsn' . ($kecualiId ? ',' . $kecualiId : '')],
            'nama'   => ['required', 'string', 'max:100'],
            'alamat' => ['required', 'string', 'max:255'],
            'kecamatan' => ['nullable', 'string', 'max:100'],
            'lat'    => ['required', 'numeric', 'between:-90,90'],
            'lng'    => ['required', 'numeric', 'between:-180,180'],
            'kebutuhan'        => ['nullable', 'array'],
            'kebutuhan.*.mapel'  => ['required_with:kebutuhan.*.jumlah', 'string', 'max:50'],
            'kebutuhan.*.jumlah' => ['required_with:kebutuhan.*.mapel', 'integer', 'min:1'],
        ]);
    }

    /**
     * Simpan daftar kebutuhan dari form ke tabel kebutuhan_guru.
     * Baris kosong (tanpa mapel) dilewati.
     */
    private function simpanKebutuhan(Sekolah $sekolah, array $daftar): void
    {
        foreach ($daftar as $baris) {
            if (empty($baris['mapel'])) {
                continue;
            }
            KebutuhanGuru::create([
                'sekolah_id' => $sekolah->id,
                'mapel'      => $baris['mapel'],
                'jumlah'     => (int) $baris['jumlah'],
            ]);
        }
    }

    // Tampilkan form import Excel/CSV (khusus kabid_ptk)
    public function importForm()
    {
        return view('sekolah.import', ['kolom' => self::KOLOM_IMPORT]);
    }

    // Unduh template Excel contoh (header + 2 baris contoh)
    public function unduhTemplate()
    {
        $spreadsheet = new Spreadsheet();
        $lembar = $spreadsheet->getActiveSheet();
        $lembar->fromArray([self::KOLOM_IMPORT], null, 'A1');
        $lembar->fromArray([
            ['10208250', 'SD NEGERI 076 PANYABUNGAN', 'Jl. Merdeka No.10', 'Panyabungan', 0.858, 99.56],
            ['10208088', 'SMP NEGERI 1 PANYABUNGAN', 'Jl. Sudirman No.5', 'Panyabungan', 0.8732, 99.7175],
        ], null, 'A2');
        foreach (range('A', 'F') as $kolom) {
            $lembar->getColumnDimension($kolom)->setAutoSize(true);
        }

        $berkasSementara = tempnam(sys_get_temp_dir(), 'tpl') . '.xlsx';
        (new Xlsx($spreadsheet))->save($berkasSementara);

        return response()->download($berkasSementara, 'template-import-sekolah.xlsx')
            ->deleteFileAfterSend();
    }

    // Proses file Excel/CSV: baris valid disimpan, baris gagal dilaporkan
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

        $npsnAda = Sekolah::pluck('npsn')->map(fn ($n) => trim((string) $n))->flip();
        $berhasil = 0;
        $gagal = [];
        $npsnDiBerkas = [];

        DB::beginTransaction();
        try {
            foreach (array_slice($lembar, 1) as $i => $baris) {
                $noBaris = $i + 2;
                $data = array_combine(self::KOLOM_IMPORT, array_pad($baris, 6, null));
                $data = array_map(fn ($v) => is_string($v) ? trim($v) : $v, $data);
                if (collect($data)->every(fn ($v) => $v === null || $v === '')) {
                    continue;
                }

                $error = null;
                $npsn = (string) ($data['npsn'] ?? '');
                if ($npsn === '' || $data['nama'] === '' || $data['nama'] === null) {
                    $error = 'NPSN dan nama wajib diisi.';
                } elseif (isset($npsnAda[$npsn]) || isset($npsnDiBerkas[$npsn])) {
                    $error = "NPSN {$npsn} sudah ada (duplikat).";
                } elseif (($data['lat'] ?? '') !== '' && ! is_numeric($data['lat'])) {
                    $error = 'Lat harus angka.';
                } elseif (($data['lng'] ?? '') !== '' && ! is_numeric($data['lng'])) {
                    $error = 'Lng harus angka.';
                }

                if ($error) {
                    $gagal[] = "Baris {$noBaris}: {$error}";
                    continue;
                }

                Sekolah::create([
                    'npsn' => $npsn, 'nama' => $data['nama'],
                    'alamat' => $data['alamat'] ?? null,
                    'kecamatan' => $data['kecamatan'] ?? null,
                    'lat' => $data['lat'] !== '' ? (float) $data['lat'] : null,
                    'lng' => $data['lng'] !== '' ? (float) $data['lng'] : null,
                ]);
                $npsnDiBerkas[$npsn] = true;
                $berhasil++;
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors(['berkas' => 'Gagal memproses: ' . $e->getMessage()]);
        }

        return redirect()->route('sekolah.index')->with([
            'sukses' => "Import selesai: {$berhasil} berhasil, " . count($gagal) . ' gagal.',
            'gagal_import' => $gagal,
        ]);
    }
}
