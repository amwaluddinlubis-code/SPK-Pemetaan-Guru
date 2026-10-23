<?php

namespace App\Services;

use App\Models\Pengaturan;
use App\Models\Sekolah;
use App\Models\WilayahKabupaten;
use App\Models\WilayahKecamatan;
use App\Models\WilayahProvinsi;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Konteks Pemda: wilayah kerja resmi aplikasi (provinsi atau kabupaten/kota)
 * yang dipilih administrator di halaman Pengaturan Pemda.
 *
 * Selama konteks aktif, data di luar wilayah terpilih disembunyikan lewat
 * global scope pada model Sekolah, Guru, KebutuhanGuru, dan
 * UsulanPenempatan (didaftarkan di AppServiceProvider). Pencocokan wilayah
 * memakai NAMA kecamatan (huruf besar) karena tabel sekolah/guru menyimpan
 * nama kecamatan, bukan kode Kemendagri.
 */
class KonteksPemda
{
    private const KUNCI_CACHE = 'konteks_pemda';

    /**
     * Varian ejaan nama kecamatan antara data Dapodik dan referensi
     * Kemendagri (kunci = ejaan yang muncul di data sekolah/guru,
     * nilai = ejaan baku untuk pencocokan).
     */
    private const VARIAN_EJAAN = ['MERAPI' => 'MARAPI'];

    /** Bentuk kanonik nama wilayah untuk pencocokan: huruf besar + ejaan baku. */
    public static function namaKanonik(string $nama): string
    {
        $n = strtoupper(trim((string) preg_replace('/\s+/', ' ', $nama)));

        return strtr($n, self::VARIAN_EJAAN);
    }

    /** Ekspresi SQL yang menghasilkan bentuk kanonik dari kolom nama kecamatan. */
    private static function ekspresiKanonik(string $kolom): string
    {
        $e = "UPPER(TRIM({$kolom}))";
        foreach (self::VARIAN_EJAAN as $dari => $ke) {
            $e = "REPLACE({$e}, '{$dari}', '{$ke}')";
        }

        return $e;
    }

    /** @var array{level: ?string, kode_provinsi: ?string, kode_kabupaten: ?string}|null */
    private static ?array $memo = null;

    /** Id instance aplikasi pemilik memo (memo diatur ulang bila aplikasi berganti). */
    private static ?int $memoAppId = null;

    /** Kosongkan memo & cache (dipanggil setiap konteks disimpan/dilepas). */
    public static function segarkan(): void
    {
        self::$memo = null;
        self::$memoAppId = null;
        Cache::forget(self::KUNCI_CACHE);
    }

    /**
     * Data mentah konteks dari tabel pengaturan.
     *
     * @return array{level: ?string, kode_provinsi: ?string, kode_kabupaten: ?string}
     */
    public static function data(): array
    {
        // Memo hanya berlaku untuk satu instance aplikasi; aplikasi baru
        // (permintaan berikutnya / tes berikutnya) membaca ulang dari awal.
        $appId = spl_object_id(app());
        if (self::$memoAppId !== $appId) {
            self::$memo = null;
            self::$memoAppId = $appId;
        }

        if (self::$memo !== null) {
            return self::$memo;
        }

        $kosong = ['level' => null, 'kode_provinsi' => null, 'kode_kabupaten' => null];

        try {
            if (! Schema::hasTable('pengaturan')) {
                // Tabel belum ada (migrasi awal): jangan di-memo agar bacaan
                // berikutnya setelah tabel terbuat tidak basi.
                return $kosong;
            }

            return self::$memo = Cache::remember(self::KUNCI_CACHE, 3600, fn () => [
                'level' => Pengaturan::ambil('pemda_level'),
                'kode_provinsi' => Pengaturan::ambil('pemda_kode_provinsi'),
                'kode_kabupaten' => Pengaturan::ambil('pemda_kode_kabupaten'),
            ]);
        } catch (\Throwable) {
            // Database belum siap: anggap belum diatur, tanpa memo.
            return $kosong;
        }
    }

    /** Konteks dianggap aktif bila level & provinsi terisi (kabupaten wajib pada level kabupaten). */
    public static function aktif(): bool
    {
        $d = self::data();

        if ($d['level'] === 'provinsi') {
            return ! empty($d['kode_provinsi']);
        }

        if ($d['level'] === 'kabupaten') {
            return ! empty($d['kode_provinsi']) && ! empty($d['kode_kabupaten']);
        }

        return false;
    }

    public static function level(): ?string
    {
        return self::data()['level'];
    }

    public static function kodeProvinsi(): ?string
    {
        return self::data()['kode_provinsi'];
    }

    public static function kodeKabupaten(): ?string
    {
        return self::data()['kode_kabupaten'];
    }

    /** Model provinsi konteks (null bila belum diatur / kode tak dikenal). */
    public static function provinsi(): ?WilayahProvinsi
    {
        return self::kodeProvinsi()
            ? WilayahProvinsi::find(self::kodeProvinsi())
            : null;
    }

    /** Model kabupaten konteks pada level kabupaten. */
    public static function kabupaten(): ?WilayahKabupaten
    {
        return self::level() === 'kabupaten' && self::kodeKabupaten()
            ? WilayahKabupaten::find(self::kodeKabupaten())
            : null;
    }

    /**
     * Nama resmi pemda, mis. "Pemerintah Kabupaten Mandailing Natal"
     * atau "Pemerintah Provinsi Sumatera Utara".
     */
    public static function namaPemda(): ?string
    {
        if (! self::aktif()) {
            return null;
        }

        try {
            return self::susunNamaPemda();
        } catch (\Throwable) {
            return null;
        }
    }

    private static function susunNamaPemda(): ?string
    {

        if (self::level() === 'kabupaten') {
            $kab = self::kabupaten();
            if ($kab === null) {
                return null;
            }
            $nama = strtoupper($kab->nama);
            if (str_starts_with($nama, 'KAB. ')) {
                return 'Pemerintah Kabupaten '.Str::title(substr($nama, 5));
            }
            if (str_starts_with($nama, 'KOTA ')) {
                return 'Pemerintah Kota '.Str::title(substr($nama, 5));
            }

            return 'Pemerintah '.Str::title($kab->nama);
        }

        $prov = self::provinsi();

        return $prov ? 'Pemerintah Provinsi '.Str::title($prov->nama) : null;
    }

    /**
     * Nama wilayah konteks bentuk pendek, mis. "Kab. Mandailing Natal",
     * "Kota Medan", atau "Sumatera Utara" (provinsi). Untuk lencana & chip.
     */
    public static function namaWilayah(): ?string
    {
        if (! self::aktif()) {
            return null;
        }

        try {
            if (self::level() === 'kabupaten') {
                $kab = self::kabupaten();
                if ($kab === null) {
                    return null;
                }
                $nama = strtoupper($kab->nama);
                if (str_starts_with($nama, 'KAB. ')) {
                    return 'Kab. '.Str::title(substr($nama, 5));
                }
                if (str_starts_with($nama, 'KOTA ')) {
                    return 'Kota '.Str::title(substr($nama, 5));
                }

                return Str::title($kab->nama);
            }

            $prov = self::provinsi();

            return $prov ? Str::title($prov->nama) : null;
        } catch (\Throwable) {
            return null;
        }
    }

    /** Seluruh kecamatan (model) di dalam konteks; kosong bila konteks tidak aktif. */
    public static function daftarKecamatan(): Collection
    {
        if (! self::aktif()) {
            return collect();
        }

        if (self::level() === 'kabupaten') {
            return WilayahKecamatan::where('kabupaten_kode', self::kodeKabupaten())
                ->orderBy('nama')->get();
        }

        return WilayahKecamatan::whereHas('kabupaten', fn ($q) => $q->where('provinsi_kode', self::kodeProvinsi()))
            ->orderBy('nama')->get();
    }

    /** Nama-nama kecamatan konteks dalam bentuk kanonik (untuk pencocokan data). */
    public static function namaKecamatanUpper(): array
    {
        return self::daftarKecamatan()
            ->map(fn (WilayahKecamatan $k) => self::namaKanonik($k->nama))
            ->unique()->values()->all();
    }

    /**
     * Peta NAMA KECAMATAN => kode Kemendagri untuk memuat daftar desa dari
     * API wilayah. Saat konteks aktif dibatasi pada kecamatan konteks; bila
     * belum ada konteks, dibatasi pada kecamatan yang benar-benar dipakai
     * data sekolah agar petanya tetap kecil.
     */
    public static function petaKodeKecamatan(): Collection
    {
        if (self::aktif()) {
            $dasar = self::daftarKecamatan()
                ->mapWithKeys(fn (WilayahKecamatan $k) => [strtoupper(trim($k->nama)) => $k->kode]);
        } else {
            $namaDipakai = Sekolah::withoutGlobalScopes()
                ->whereNotNull('kecamatan')->distinct()->pluck('kecamatan')
                ->map(fn ($n) => self::namaKanonik((string) $n))->all();

            if ($namaDipakai === []) {
                return collect();
            }

            $dasar = WilayahKecamatan::whereIn(DB::raw(self::ekspresiKanonik('nama')), $namaDipakai)
                ->get()
                ->mapWithKeys(fn (WilayahKecamatan $k) => [strtoupper(trim($k->nama)) => $k->kode]);
        }

        // Daftarkan juga kunci dengan ejaan varian (mis. MERAPI) agar
        // pencarian dari nama kecamatan versi data sekolah tetap ketemu.
        $peta = $dasar->collect();
        foreach ($dasar as $nama => $kode) {
            $varian = strtr($nama, array_flip(self::VARIAN_EJAAN));
            if ($varian !== $nama && ! $peta->has($varian)) {
                $peta->put($varian, $kode);
            }
        }

        return $peta;
    }

    /** Simpan konteks baru (sudah divalidasi controller terhadap tabel wilayah). */
    public static function simpan(string $level, string $kodeProvinsi, ?string $kodeKabupaten): void
    {
        Pengaturan::simpan('pemda_level', $level);
        Pengaturan::simpan('pemda_kode_provinsi', $kodeProvinsi);
        Pengaturan::simpan('pemda_kode_kabupaten', $level === 'kabupaten' ? $kodeKabupaten : null);
        self::segarkan();
    }

    /** Lepas konteks: aplikasi kembali menampilkan semua wilayah. */
    public static function hapus(): void
    {
        Pengaturan::simpan('pemda_level', null);
        Pengaturan::simpan('pemda_kode_provinsi', null);
        Pengaturan::simpan('pemda_kode_kabupaten', null);
        self::segarkan();
    }

    // ------------------------------------------------------------------
    // Pembatas query (dipakai global scope di AppServiceProvider)
    // ------------------------------------------------------------------

    /** Batasi query sekolah pada kecamatan konteks. */
    public static function batasiSekolah(Builder $query): void
    {
        if (! self::aktif()) {
            return;
        }

        $query->whereIn(DB::raw(self::ekspresiKanonik('kecamatan')), self::namaKecamatanUpper());
    }

    /** Batasi query guru: sekolah penempatan ATAU domisili di dalam konteks. */
    public static function batasiGuru(Builder $query): void
    {
        if (! self::aktif()) {
            return;
        }

        $nama = self::namaKecamatanUpper();
        $query->where(function (Builder $q) use ($nama) {
            $q->whereHas('sekolah', fn (Builder $s) => $s->whereIn(DB::raw(self::ekspresiKanonik('kecamatan')), $nama))
                ->orWhereIn(DB::raw(self::ekspresiKanonik('guru.kecamatan')), $nama);
        });
    }

    /** Batasi query baris yang terhubung ke sekolah (kebutuhan, usulan, dst.). */
    public static function batasiLewatSekolah(Builder $query): void
    {
        if (! self::aktif()) {
            return;
        }

        $nama = self::namaKecamatanUpper();
        $query->whereHas('sekolah', fn (Builder $s) => $s->whereIn(DB::raw(self::ekspresiKanonik('kecamatan')), $nama));
    }
}
