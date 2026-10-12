<?php

// Model Guru: merepresentasikan satu baris data guru ASN di tabel 'guru'.

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Guru extends Model
{
    // Nama tabel tidak mengikuti jamak Inggris, jadi disebut eksplisit
    protected $table = 'guru';

    // Kolom-kolom yang boleh diisi massal (create/update)
    protected $fillable = [
        'nip',
        'nama',
        'mapel',
        'jenjang',
        'domisili_alamat',
        'kecamatan',
        'desa_kelurahan',
        'status_pernikahan',
        'status_kepegawaian',
        'sudah_sertifikasi',
        'mapel_sertifikasi',
        'sekolah_id',
        'domisili_lat',
        'domisili_lng',
        'sekolah_saat_ini',
        'sekolah_saat_ini_lat',
        'sekolah_saat_ini_lng',
    ];

    /**
     * Status pernikahan (mengikuti kategori sipil Indonesia).
     */
    public const STATUS_PERNIKAHAN = [
        'belum_kawin' => 'Belum Kawin',
        'kawin' => 'Kawin',
        'cerai_hidup' => 'Cerai Hidup',
        'cerai_mati' => 'Cerai Mati',
    ];

    /**
     * Bobot prioritas keluarga dalam pemeringkatan rekomendasi.
     * Guru berkeluarga (kawin/cerai) diprioritaskan karena ada pasangan/anak
     * yang terdampak bila ditempatkan jauh dari rumah. Nilai dapat diubah
     * mengikuti kebijakan Dinas.
     */
    public const BOBOT_KELUARGA = 1.5;

    /**
     * Status kepegawaian.
     */
    public const STATUS_KEPEGAWAIAN = [
        'pns' => 'PNS',
        'pppk' => 'PPPK',
        'honorer' => 'Honorer',
    ];

    /**
     * Kategori daerah untuk rem pemerataan (sekolah 3T/terpencil).
     */
    public const KATEGORI_DAERAH_3T = ['terpencil', 'sangat_terpencil'];

    public function labelStatusKepegawaian(): string
    {
        return self::STATUS_KEPEGAWAIAN[$this->status_kepegawaian] ?? '-';
    }

    /**
     * Linearitas: bila sudah sertifikasi, mapel yang diampu harus sesuai
     * mapel sertifikat pendidik. Guru belum sertifikasi dianggap linear
     * terhadap mapelnya (menunggu sertifikasi).
     */
    public function linear(): bool
    {
        if (! $this->sudah_sertifikasi || ! $this->mapel_sertifikasi) {
            return true;
        }

        return strtolower(trim($this->mapel_sertifikasi)) === strtolower(trim($this->mapel));
    }

    /**
     * Apakah sekolah tempat bertugas termasuk daerah 3T/terpencil.
     */
    public function bertugasDiDaerah3T(): bool
    {
        return $this->sekolah
            && in_array($this->sekolah->kategori_daerah, self::KATEGORI_DAERAH_3T, true);
    }

    // Pastikan koordinat selalu dibaca sebagai angka desimal
    protected $casts = [
        'domisili_lat' => 'float',
        'domisili_lng' => 'float',
        'sekolah_saat_ini_lat' => 'float',
        'sekolah_saat_ini_lng' => 'float',
    ];

    /**
     * Relasi: guru bertugas di satu sekolah (administratif).
     * Dipakai untuk scope Kepala Sekolah.
     */
    public function sekolah()
    {
        return $this->belongsTo(Sekolah::class);
    }

    /**
     * Apakah guru berkeluarga (kawin/cerai) — dipakai untuk prioritas.
     */
    public function berkeluarga(): bool
    {
        return in_array($this->status_pernikahan, ['kawin', 'cerai_hidup', 'cerai_mati'], true);
    }

    /**
     * Bobot keluarga untuk pemeringkatan: berkeluarga diprioritaskan.
     */
    public function bobotKeluarga(): float
    {
        return $this->berkeluarga() ? self::BOBOT_KELUARGA : 1.0;
    }

    public function labelStatusPernikahan(): string
    {
        return self::STATUS_PERNIKAHAN[$this->status_pernikahan] ?? '-';
    }

    /**
     * NIP tersamar untuk export/cetak (PII): 8 digit depan + bintang + 4 digit belakang.
     * Contoh: 198501012010011001 → 19850101******1001.
     */
    public function nipTersamar(): string
    {
        $nip = (string) $this->nip;
        if (strlen($nip) <= 12) {
            return str_repeat('*', strlen($nip));
        }

        return substr($nip, 0, 8).str_repeat('*', strlen($nip) - 12).substr($nip, -4);
    }

    /**
     * MAP-001: koordinat sekolah tempat bertugas.
     * Sumber kebenaran tunggal = relasi `sekolah` (tabel sekolah).
     * Kolom denormalisasi `sekolah_saat_ini_lat/lng` hanya cache tampilan;
     * semua perhitungan jarak WAJIB lewat method ini.
     *
     * @return array{lat: float|null, lng: float|null}
     */
    public function koordinatSekolah(): array
    {
        $sekolah = $this->relationLoaded('sekolah') ? $this->getRelation('sekolah') : $this->sekolah;

        if ($sekolah && $sekolah->lat !== null && $sekolah->lng !== null) {
            return ['lat' => (float) $sekolah->lat, 'lng' => (float) $sekolah->lng];
        }

        // Fallback: cache denormalisasi (untuk data lama yang relasinya hilang)
        return [
            'lat' => $this->sekolah_saat_ini_lat !== null ? (float) $this->sekolah_saat_ini_lat : null,
            'lng' => $this->sekolah_saat_ini_lng !== null ? (float) $this->sekolah_saat_ini_lng : null,
        ];
    }

    /**
     * Jarak (km) dari domisili ke sekolah tempat bertugas saat ini.
     */
    public function jarakKeSekolahSaatIni(): ?float
    {
        $k = $this->koordinatSekolah();
        if ($k['lat'] === null || $k['lng'] === null) {
            return null;
        }

        return $this->jarakKe($k['lat'], $k['lng']);
    }

    /**
     * Hitung jarak (km) dari domisili guru ke sebuah titik koordinat.
     * Menggunakan rumus Haversine (jarak garis lurus di permukaan Bumi).
     */
    public function jarakKe(float $lat, float $lng): float
    {
        $r = 6371; // jari-jari Bumi (km)
        $dLat = deg2rad($lat - $this->domisili_lat);
        $dLng = deg2rad($lng - $this->domisili_lng);
        $a = sin($dLat / 2) ** 2
           + cos(deg2rad($this->domisili_lat)) * cos(deg2rad($lat))
           * sin($dLng / 2) ** 2;

        return 2 * $r * asin(sqrt($a));
    }
}
