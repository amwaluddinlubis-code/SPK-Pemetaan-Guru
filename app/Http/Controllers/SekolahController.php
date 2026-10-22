<?php

// Controller Sekolah dengan scope berbeda per peran:
//   kepala_sekolah → hanya sekolahnya (bisa ubah + atur kebutuhan guru)
//   ketua_pgri     → lihat se-kecamatan (read-only)
//   guru           → lihat semua (read-only, untuk rekomendasi)
//   kabid_ptk      → CRUD penuh
//   kadis          → CRUD penuh

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\KebutuhanGuru;
use App\Models\Sekolah;
use App\Services\AnalisisKebutuhanGuru;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class SekolahController extends Controller
{
    /**
     * Halaman analisis kebutuhan guru: hitung kebutuhan dari rombel (Dapodik)
     * × JP Permendikdasmen 13/2025, bandingkan dengan guru eksisting.
     * Read-only untuk semua peran yang boleh melihat data sekolah.
     */
    public function analisis(Sekolah $sekolah)
    {
        $sekolah->load(['kebutuhan', 'guru']);
        $jenjang = $sekolah->jenjangEfektif();
        $rombel = (int) ($sekolah->rombel ?? 0);

        $eksisting = $this->eksistingPerLabel($sekolah, $jenjang);
        $baris = $rombel > 0
            ? AnalisisKebutuhanGuru::hitung($jenjang, $rombel, $eksisting)
            : [];

        return view('sekolah.analisis', [
            'sekolah' => $sekolah,
            'jenjang' => $jenjang,
            'rombel' => $rombel,
            'baris' => $baris,
            'bebanMengajar' => AnalisisKebutuhanGuru::BEBAN_MENGAJAR_JP,
        ]);
    }

    /**
     * Terapkan hasil analisis sebagai kebutuhan guru resmi sekolah:
     * hapus baris kebutuhan lama, simpan hasil hitungan. Dicatat di audit.
     */
    public function terapkanAnalisis(Sekolah $sekolah)
    {
        $this->otorisasiUbah($sekolah);
        $sekolah->load('guru');
        $jenjang = $sekolah->jenjangEfektif();
        $rombel = (int) ($sekolah->rombel ?? 0);

        if ($rombel <= 0) {
            return redirect()->route('sekolah.analisis', $sekolah)
                ->with('galat', 'Isi jumlah rombel sekolah dulu sebelum menerapkan hasil analisis.');
        }

        $eksisting = $this->eksistingPerLabel($sekolah, $jenjang);
        $baris = AnalisisKebutuhanGuru::hitung($jenjang, $rombel, $eksisting);

        DB::transaction(function () use ($sekolah, $baris) {
            $sekolah->kebutuhan()->delete();
            foreach ($baris as $b) {
                if ($b['kebutuhan'] <= 0) {
                    continue;
                }
                KebutuhanGuru::create([
                    'sekolah_id' => $sekolah->id,
                    'mapel' => $b['label'],
                    'jumlah' => $b['kebutuhan'],
                ]);
            }

            ActivityLog::catatAman('sekolah.terapkan-analisis-kebutuhan', $sekolah, 'sukses', [
                'jenjang' => $sekolah->jenjangEfektif(),
                'rombel' => (int) $sekolah->rombel,
                'baris' => count($baris),
            ]);
        });

        return redirect()->route('sekolah.index')
            ->with('sukses', 'Hasil analisis kebutuhan berhasil diterapkan ke '.$sekolah->nama.'.');
    }

    /**
     * Hitung guru eksisting per label kanonis.
     * SD: guru yang mapelnya bukan PJOK/Agama dihitung sebagai guru kelas.
     *
     * @return array<string, int>
     */
    private function eksistingPerLabel(Sekolah $sekolah, string $jenjang): array
    {
        $eksisting = [];
        foreach ($sekolah->guru as $guru) {
            $label = AnalisisKebutuhanGuru::kanonis((string) $guru->mapel);
            if ($label === '') {
                continue;
            }
            if ($jenjang === 'SD' && ! in_array($label, ['PJOK', 'Agama'], true)) {
                $label = AnalisisKebutuhanGuru::LABEL_GURU_KELAS;
            }
            $eksisting[$label] = ($eksisting[$label] ?? 0) + 1;
        }

        return $eksisting;
    }

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

    /**
     * Halaman index dirender komponen Livewire TabelSekolah
     * (pencarian, filter, sorting, pagination tanpa reload).
     */
    public function index()
    {
        return view('sekolah.index');
    }

    public function create()
    {
        return view('sekolah.form', [
            'sekolah' => new Sekolah,
            'aksi' => 'Tambah',
            'daftarKecamatan' => $this->daftarKecamatanForm(),
        ]);
    }

    /**
     * Export Excel data sekolah (mengikuti filter & scope yang aktif).
     */
    public function export(Request $request)
    {
        $query = $this->queryScope();
        $kataKunci = trim($request->get('q', ''));
        if ($kataKunci !== '') {
            $query->where(function ($w) use ($kataKunci) {
                $w->where('nama', 'like', "%{$kataKunci}%")
                    ->orWhere('npsn', 'like', "%{$kataKunci}%")
                    ->orWhere('alamat', 'like', "%{$kataKunci}%");
            });
        }
        if (($kec = $request->get('kecamatan', '')) !== '') {
            $query->where('kecamatan', $kec);
        }
        $data = $query->with('kebutuhan')->orderBy('nama')->get();

        $spreadsheet = new Spreadsheet;
        $lembar = $spreadsheet->getActiveSheet();
        $lembar->fromArray([[
            'NPSN', 'Nama Sekolah', 'Alamat', 'Kecamatan', 'Desa/Kelurahan',
            'Kategori Daerah', 'Kebutuhan Guru',
        ]], null, 'A1');
        $baris = 2;
        foreach ($data as $s) {
            $lembar->fromArray([[
                $s->npsn, $s->nama, $s->alamat, $s->kecamatan, $s->desa_kelurahan,
                $s->labelKategoriDaerah(),
                $s->kebutuhan->map(fn ($k) => $k->mapel.' ('.$k->jumlah.')')->join(', '),
            ]], null, "A{$baris}");
            $baris++;
        }
        foreach (range('A', 'G') as $kolom) {
            $lembar->getColumnDimension($kolom)->setAutoSize(true);
        }

        $namaFile = 'data-sekolah-'.date('Ymd-His').'.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            (new Xlsx($spreadsheet))->save('php://output');
        }, $namaFile, ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
    }

    /**
     * Tampilan cetak/PDF data sekolah (lewat dialog cetak browser).
     */
    public function cetak(Request $request)
    {
        $query = $this->queryScope();
        $kataKunci = trim($request->get('q', ''));
        if ($kataKunci !== '') {
            $query->where(function ($w) use ($kataKunci) {
                $w->where('nama', 'like', "%{$kataKunci}%")
                    ->orWhere('npsn', 'like', "%{$kataKunci}%");
            });
        }
        if (($kec = $request->get('kecamatan', '')) !== '') {
            $query->where('kecamatan', $kec);
        }

        return view('sekolah.cetak', ['data' => $query->with('kebutuhan')->orderBy('nama')->get()]);
    }

    /** Kolom template import sekolah (urutan tetap). */
    private const KOLOM_IMPORT = [
        'npsn', 'nama', 'alamat', 'desa_kelurahan', 'kecamatan',
        'kategori_daerah', 'lat', 'lng',
    ];

    public function importForm()
    {
        return view('sekolah.import', ['kolom' => self::KOLOM_IMPORT]);
    }

    /**
     * Unduh template Excel contoh (header + 2 baris contoh).
     */
    public function unduhTemplate()
    {
        $spreadsheet = new Spreadsheet;
        $lembar = $spreadsheet->getActiveSheet();
        $lembar->fromArray([self::KOLOM_IMPORT], null, 'A1');
        $lembar->fromArray([[
            '20100001', 'SD NEGERI 001 CONTOH', 'Jl. Contoh No.1', 'Desa Contoh',
            'Panyabungan', 'biasa', 0.85, 99.55,
        ]], null, 'A2');
        $lembar->fromArray([[
            '20100002', 'SD NEGERI 002 CONTOH', 'Jl. Contoh No.2', '',
            'Panyabungan', 'terpencil', 0.86, 99.56,
        ]], null, 'A3');
        foreach (range('A', 'H') as $kolom) {
            $lembar->getColumnDimension($kolom)->setAutoSize(true);
        }

        return response()->streamDownload(function () use ($spreadsheet) {
            (new Xlsx($spreadsheet))->save('php://output');
        }, 'template-import-sekolah.xlsx', ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
    }

    /**
     * Import sekolah dari Excel/CSV. Validasi per baris setara validasi form.
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

        $npsnAda = Sekolah::pluck('npsn')->map(fn ($n) => trim((string) $n))->flip();
        $kategoriValid = ['biasa', 'terpencil', 'sangat_terpencil'];

        $berhasil = 0;
        $gagal = [];
        $npsnDiBerkas = [];

        DB::beginTransaction();
        try {
            foreach (array_slice($lembar, 1) as $i => $baris) {
                $noBaris = $i + 2;
                $data = array_combine(self::KOLOM_IMPORT, array_pad($baris, 8, null));
                $data = array_map(fn ($v) => is_string($v) ? trim($v) : $v, $data);
                if (collect($data)->every(fn ($v) => $v === null || $v === '')) {
                    continue; // lewati baris kosong
                }

                $error = $this->validasiBarisImport($data, $npsnAda, $npsnDiBerkas, $kategoriValid);
                if ($error) {
                    $gagal[] = "Baris {$noBaris}: {$error}";

                    continue;
                }

                Sekolah::create([
                    'npsn' => $data['npsn'], 'nama' => $data['nama'],
                    'alamat' => $data['alamat'],
                    'desa_kelurahan' => ($data['desa_kelurahan'] ?? '') === '' ? null : $data['desa_kelurahan'],
                    'kecamatan' => ($data['kecamatan'] ?? '') === '' ? null : $data['kecamatan'],
                    'kategori_daerah' => ($data['kategori_daerah'] ?? '') === ''
                        ? 'biasa' : strtolower($data['kategori_daerah']),
                    'lat' => $data['lat'], 'lng' => $data['lng'],
                ]);
                $npsnDiBerkas[trim((string) $data['npsn'])] = true;
                $npsnAda[trim((string) $data['npsn'])] = true;
                $berhasil++;
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            // Jangan tampilkan detail exception/SQL kepada pengguna.
            return back()->withErrors(['berkas' => 'Import dibatalkan karena terjadi kesalahan saat menyimpan data. Periksa file dan coba lagi.']);
        }

        ActivityLog::catatAman('sekolah.import', null, 'sukses', [
            'berhasil' => $berhasil,
            'gagal' => count($gagal),
        ]);

        return redirect()->route('sekolah.index')->with([
            'sukses' => "Import selesai: {$berhasil} sekolah ditambahkan.",
            'import_gagal' => $gagal,
        ]);
    }

    /**
     * Form import rombel dari CSV ekspor Dapodik per kecamatan.
     */
    public function importRombelForm()
    {
        return view('sekolah.import-rombel');
    }

    /**
     * Import jumlah rombel dari CSV ekspor Dapodik (tombol "Ekspor CSV" di
     * halaman progres per kecamatan). Kolom yang dipakai: NPSN (kunci) dan
     * Rombel. Sekolah yang NPSN-nya tidak dikenal dilaporkan, tidak dibuat.
     */
    public function importRombel(Request $request)
    {
        $request->validate([
            'berkas' => ['required', 'file', 'max:5120', 'mimes:csv,txt'],
        ]);

        $path = $request->file('berkas')->getRealPath();
        try {
            $pembaca = IOFactory::createReader('Csv');
            $pembaca->setDelimiter($this->deteksiDelimiterCsv($path));
            $pembaca->setEnclosure('"');
            $lembar = $pembaca->load($path)->getActiveSheet()->toArray(null, true, true, false);
        } catch (\Throwable $e) {
            return back()->withErrors(['berkas' => 'File tidak bisa dibaca sebagai CSV.']);
        }

        $header = array_map(fn ($h) => strtolower(trim((string) $h)), $lembar[0] ?? []);
        $idxNpsn = array_search('npsn', $header, true);
        $idxRombel = array_search('rombel', $header, true);
        if ($idxNpsn === false || $idxRombel === false) {
            return back()->withErrors(['berkas' => 'Kolom NPSN dan Rombel tidak ditemukan. Pakai file CSV dari tombol "Ekspor CSV" di halaman progres Dapodik per kecamatan.']);
        }

        $petaSekolah = Sekolah::pluck('id', 'npsn')
            ->mapWithKeys(fn ($id, $npsn) => [trim((string) $npsn) => $id]);

        $berhasil = 0;
        $gagal = [];
        $npsnDiproses = [];

        DB::beginTransaction();
        try {
            foreach (array_slice($lembar, 1) as $i => $baris) {
                $noBaris = $i + 2;
                if (collect($baris)->every(fn ($v) => $v === null || trim((string) $v) === '')) {
                    continue; // lewati baris kosong
                }
                $npsn = trim((string) ($baris[$idxNpsn] ?? ''));
                $rombelMentah = trim((string) ($baris[$idxRombel] ?? ''));

                if ($npsn === '') {
                    $gagal[] = "Baris {$noBaris}: NPSN kosong.";

                    continue;
                }
                if (! ctype_digit($rombelMentah) || (int) $rombelMentah > 300) {
                    $gagal[] = "Baris {$noBaris} (NPSN {$npsn}): jumlah rombel tidak valid.";

                    continue;
                }
                if (isset($npsnDiproses[$npsn])) {
                    $gagal[] = "Baris {$noBaris} (NPSN {$npsn}): duplikat di file, yang dipakai baris pertama.";

                    continue;
                }
                $npsnDiproses[$npsn] = true;

                $sekolahId = $petaSekolah[$npsn] ?? null;
                if (! $sekolahId) {
                    $gagal[] = "Baris {$noBaris} (NPSN {$npsn}): tidak dikenal di database.";

                    continue;
                }

                Sekolah::where('id', $sekolahId)->update(['rombel' => (int) $rombelMentah]);
                $berhasil++;
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            // Jangan tampilkan detail exception/SQL kepada pengguna.
            return back()->withErrors(['berkas' => 'Import dibatalkan karena terjadi kesalahan saat menyimpan data. Periksa file dan coba lagi.']);
        }

        ActivityLog::catatAman('sekolah.import-rombel', null, 'sukses', [
            'berhasil' => $berhasil,
            'gagal' => count($gagal),
        ]);

        return redirect()->route('sekolah.index')->with([
            'sukses' => "Import rombel selesai: {$berhasil} sekolah diperbarui.",
            'import_gagal' => $gagal,
        ]);
    }

    /**
     * Tebak delimiter CSV — ekspor Dapodik/Excel Indonesia umumnya ';'.
     */
    private function deteksiDelimiterCsv(string $path): string
    {
        $awal = (string) file_get_contents($path, false, null, 0, 4096);
        $barisPertama = (string) strtok($awal, "\n");

        return substr_count($barisPertama, ';') >= substr_count($barisPertama, ',') ? ';' : ',';
    }

    /**
     * Validasi satu baris import — setara aturan form tambah sekolah.
     */
    private function validasiBarisImport(array $data, $npsnAda, array $npsnDiBerkas, array $kategoriValid): ?string
    {
        $npsn = trim((string) ($data['npsn'] ?? ''));
        if ($npsn === '' || strlen($npsn) > 20) {
            return 'NPSN wajib diisi (maks 20 karakter).';
        }
        if (isset($npsnAda[$npsn]) || isset($npsnDiBerkas[$npsn])) {
            return "NPSN {$npsn} sudah ada.";
        }
        if (trim((string) ($data['nama'] ?? '')) === '') {
            return 'Nama sekolah wajib diisi.';
        }
        if (trim((string) ($data['alamat'] ?? '')) === '') {
            return 'Alamat wajib diisi.';
        }
        $kategori = strtolower(trim((string) ($data['kategori_daerah'] ?? '')));
        if ($kategori !== '' && ! in_array($kategori, $kategoriValid, true)) {
            return 'Kategori daerah harus: biasa / terpencil / sangat_terpencil.';
        }
        foreach (['lat', 'lng'] as $kolom) {
            $nilai = $data[$kolom] ?? null;
            if (! is_numeric($nilai)) {
                return "Kolom {$kolom} harus angka.";
            }
        }
        $lat = (float) $data['lat'];
        $lng = (float) $data['lng'];
        if ($lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
            return 'Koordinat di luar rentang wajar.';
        }

        return null;
    }

    public function store(Request $request)
    {
        $data = $this->validasi($request);

        DB::transaction(function () use ($data) {
            $sekolah = Sekolah::create([
                'npsn' => $data['npsn'], 'nama' => $data['nama'],
                'jenjang' => $data['jenjang'] ?? null, 'rombel' => $data['rombel'] ?? null,
                'alamat' => $data['alamat'], 'kecamatan' => $data['kecamatan'] ?? null,
                'desa_kelurahan' => $data['desa_kelurahan'] ?? null,
                'kategori_daerah' => $data['kategori_daerah'] ?? 'biasa',
                'lat' => $data['lat'], 'lng' => $data['lng'],
            ]);
            $this->simpanKebutuhan($sekolah, $data['kebutuhan'] ?? []);

            ActivityLog::catatAman('sekolah.tambah', $sekolah, 'sukses', [
                'npsn' => $data['npsn'],
            ]);
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
                'jenjang' => $data['jenjang'] ?? null, 'rombel' => $data['rombel'] ?? null,
                'alamat' => $data['alamat'], 'kecamatan' => $data['kecamatan'] ?? null,
                'desa_kelurahan' => $data['desa_kelurahan'] ?? null,
                'kategori_daerah' => $data['kategori_daerah'] ?? 'biasa',
                'lat' => $data['lat'], 'lng' => $data['lng'],
            ]);
            // Hapus kebutuhan lama, simpan yang baru (cara paling sederhana & aman)
            $sekolah->kebutuhan()->delete();
            $this->simpanKebutuhan($sekolah, $data['kebutuhan'] ?? []);

            ActivityLog::catatAman('sekolah.ubah', $sekolah, 'sukses');
        });

        return redirect()->route('sekolah.index')
            ->with('sukses', 'Data sekolah berhasil diperbarui.');
    }

    public function destroy(Sekolah $sekolah)
    {
        $sekolahId = $sekolah->id;
        $sekolah->delete(); // kebutuhan ikut terhapus (cascade)

        ActivityLog::catatAman('sekolah.hapus', null, 'sukses', [
            'sekolah_id' => $sekolahId,
        ]);

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
            'npsn' => ['required', 'string', 'max:20', 'unique:sekolah,npsn'.($kecualiId ? ','.$kecualiId : '')],
            'nama' => ['required', 'string', 'max:100'],
            'jenjang' => ['nullable', 'in:SD,SMP'],
            'rombel' => ['nullable', 'integer', 'min:0', 'max:300'],
            'alamat' => ['required', 'string', 'max:255'],
            'kecamatan' => ['nullable', 'string', 'max:100'],
            'desa_kelurahan' => ['nullable', 'string', 'max:100'],
            'kategori_daerah' => ['nullable', 'in:biasa,terpencil,sangat_terpencil'],
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
            'kebutuhan' => ['nullable', 'array'],
            'kebutuhan.*.mapel' => ['required_with:kebutuhan.*.jumlah', 'string', 'max:50'],
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
                'mapel' => $baris['mapel'],
                'jumlah' => (int) $baris['jumlah'],
            ]);
        }
    }
}
