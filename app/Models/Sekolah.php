<?php

// Model Sekolah: merepresentasikan satu sekolah di tabel 'sekolah'.

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sekolah extends Model
{
    // Nama tabel tidak mengikuti jamak Inggris, jadi disebut eksplisit
    protected $table = 'sekolah';

    protected $fillable = [
        'npsn',
        'nama',
        'jenjang',
        'rombel',
        'alamat',
        'kecamatan',
        'desa_kelurahan',
        'kategori_daerah',
        'lat',
        'lng',
        'koordinat_terverifikasi_lat',
        'koordinat_terverifikasi_lng',
        'koordinat_terverifikasi_pada',
        'koordinat_terverifikasi_oleh',
        'koordinat_sumber',
        'koordinat_verifikasi_catatan',
    ];

    /** Pilihan sumber verifikasi koordinat (untuk formulir verifikasi). */
    public const SUMBER_KOORDINAT = [
        'gps_lapangan' => 'GPS di lokasi sekolah',
        'konfirmasi_sekolah' => 'Konfirmasi pihak sekolah',
        'peta_dapodik' => 'Peta/titik Dapodik',
        'citra_satelit' => 'Citra satelit / peta digital',
        'konfirmasi_massal' => 'Konfirmasi operator (massal)',
    ];

    /**
     * Kategori daerah sekolah.
     */
    public const KATEGORI_DAERAH = [
        'biasa' => 'Biasa',
        'terpencil' => 'Terpencil',
        'sangat_terpencil' => 'Sangat Terpencil (3T)',
    ];

    public function labelKategoriDaerah(): string
    {
        return self::KATEGORI_DAERAH[$this->kategori_daerah] ?? '-';
    }

    public function isDaerah3T(): bool
    {
        return in_array($this->kategori_daerah, ['terpencil', 'sangat_terpencil'], true);
    }

    protected $casts = [
        'lat' => 'float',
        'lng' => 'float',
        'rombel' => 'integer',
        'koordinat_terverifikasi_lat' => 'float',
        'koordinat_terverifikasi_lng' => 'float',
        'koordinat_terverifikasi_pada' => 'datetime',
    ];

    /** Petugas yang memverifikasi koordinat sekolah ini. */
    public function verifikatorKoordinat()
    {
        return $this->belongsTo(User::class, 'koordinat_terverifikasi_oleh');
    }

    /**
     * Koordinat dianggap terverifikasi bila titik terverifikasi pernah
     * dicatat DAN masih sama dengan titik koordinat saat ini — mengubah
     * titik otomatis membatalkan statusnya tanpa perlu penanda terpisah.
     */
    public function koordinatTerverifikasi(): bool
    {
        if ($this->lat === null || $this->lng === null
            || $this->koordinat_terverifikasi_lat === null
            || $this->koordinat_terverifikasi_lng === null) {
            return false;
        }

        return abs($this->koordinat_terverifikasi_lat - $this->lat) < 0.000001
            && abs($this->koordinat_terverifikasi_lng - $this->lng) < 0.000001;
    }

    /** Scope: hanya sekolah yang koordinatnya terverifikasi. */
    public function scopeKoordinatTerverifikasi($query)
    {
        return $query->whereNotNull('koordinat_terverifikasi_lat')
            ->whereNotNull('koordinat_terverifikasi_lng')
            ->whereRaw('ABS(koordinat_terverifikasi_lat - lat) < 0.000001')
            ->whereRaw('ABS(koordinat_terverifikasi_lng - lng) < 0.000001');
    }

    /** Label status verifikasi koordinat untuk tampilan. */
    public function labelStatusKoordinat(): string
    {
        if ($this->lat === null || $this->lng === null) {
            return 'Koordinat belum diisi';
        }

        return $this->koordinatTerverifikasi() ? 'Koordinat terverifikasi' : 'Koordinat belum terverifikasi';
    }

    /**
     * Jenjang efektif: kolom 'jenjang' bila terisi, jika tidak ditebak dari
     * nama sekolah (mengandung SMP/MTs → SMP, selain itu SD).
     */
    public function jenjangEfektif(): string
    {
        if (! empty($this->jenjang)) {
            return strtoupper($this->jenjang);
        }
        $nama = strtoupper((string) $this->nama);
        if (str_contains($nama, 'SMP') || str_contains($nama, 'MTS')) {
            return 'SMP';
        }

        return 'SD';
    }

    /**
     * Guru-guru yang saat ini bertugas di sekolah ini.
     */
    public function guru()
    {
        return $this->hasMany(Guru::class);
    }

    /**
     * Satu sekolah punya BANYAK kebutuhan guru (per mata pelajaran).
     * Contoh: SDN 1 butuh 2 guru Matematika + 1 guru IPA.
     */
    public function kebutuhan()
    {
        return $this->hasMany(KebutuhanGuru::class);
    }

    /**
     * Shortcut: berapa guru yang dibutuhkan untuk satu mapel tertentu?
     * Mengembalikan 0 jika tidak ada kebutuhan untuk mapel itu.
     */
    public function butuhMapel(string $mapel): int
    {
        return (int) $this->kebutuhan->where('mapel', $mapel)->sum('jumlah');
    }
}
