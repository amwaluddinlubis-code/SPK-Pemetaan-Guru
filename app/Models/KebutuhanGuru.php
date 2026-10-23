<?php

// Model KebutuhanGuru: satu baris = "sekolah X butuh N guru mapel Y".

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class KebutuhanGuru extends Model
{
    // Nama tabel tidak mengikuti jamak Inggris, jadi disebut eksplisit
    protected $table = 'kebutuhan_guru';

    protected $fillable = [
        'sekolah_id',
        'mapel',
        'jumlah',
        'jumlah_target',
    ];

    protected $casts = [
        'jumlah' => 'integer',
        'jumlah_target' => 'integer',
    ];

    /**
     * Backward-compatible form/model attribute. `jumlah` is now the target
     * requirement; the legacy column is retained for safe rollback.
     */
    public function getJumlahAttribute(): int
    {
        return (int) ($this->attributes['jumlah_target'] ?? $this->attributes['jumlah'] ?? 0);
    }

    public function setJumlahAttribute(int|string $jumlah): void
    {
        $this->attributes['jumlah'] = $jumlah;
        $this->attributes['jumlah_target'] = $jumlah;
    }

    /**
     * Setiap kebutuhan milik SATU sekolah.
     */
    public function sekolah()
    {
        return $this->belongsTo(Sekolah::class);
    }

    /**
     * Formasi tersedia per sekolah/mapel = target - guru yang sudah bertugas
     * - reservasi SPK dan usulan aktif. Saat dipanggil dalam transaksi,
     * `$kunci` mengunci semua baris target terkait sebelum menghitung.
     *
     * @return array<string, int> kunci: "sekolah_id|mapel"
     */
    public static function sisaDenganAlokasi(
        array $mapel = [],
        ?int $kecualikanSpkId = null,
        ?int $kecualikanUsulanId = null,
        bool $kunci = false,
    ): array {
        $mapel = collect($mapel)
            ->map(fn (string $nama): string => strtolower(trim($nama)))
            ->filter()
            ->unique()
            ->values();

        $kebutuhanQuery = self::query()->orderBy('id');
        if ($mapel->isNotEmpty()) {
            $kebutuhanQuery->whereIn('mapel', $mapel->all());
        }
        if ($kunci) {
            $kebutuhanQuery->lockForUpdate();
        }

        $target = [];
        foreach ($kebutuhanQuery->get(['id', 'sekolah_id', 'mapel', 'jumlah_target']) as $baris) {
            $kunciFormasi = $baris->sekolah_id.'|'.strtolower(trim($baris->mapel));
            $target[$kunciFormasi] = ($target[$kunciFormasi] ?? 0) + (int) $baris->jumlah_target;
        }

        if ($target === []) {
            return [];
        }

        $tersedia = $target;
        $guruTerisi = Guru::whereNotNull('sekolah_id')
            ->when($mapel->isNotEmpty(), fn ($query) => $query->whereIn(DB::raw('LOWER(TRIM(mapel))'), $mapel->all()))
            ->selectRaw('sekolah_id, LOWER(TRIM(mapel)) AS mapel, COUNT(*) AS jumlah')
            ->groupBy('sekolah_id', DB::raw('LOWER(TRIM(mapel))'))
            ->get();

        foreach ($guruTerisi as $baris) {
            $kunciFormasi = $baris->sekolah_id.'|'.$baris->mapel;
            if (array_key_exists($kunciFormasi, $tersedia)) {
                $tersedia[$kunciFormasi] -= (int) $baris->jumlah;
            }
        }

        // Satu guru hanya menahan satu slot untuk kombinasi tujuan/mapel,
        // sekalipun ada catatan aktif lama dari lebih dari satu alur.
        $reservasi = [];
        $spkQuery = HasilSpk::where('status', 'rekomendasi')
            ->join('guru', 'guru.id', '=', 'hasil_spks.guru_id')
            ->selectRaw('hasil_spks.id, hasil_spks.guru_id, hasil_spks.sekolah_tujuan_id, LOWER(TRIM(COALESCE(hasil_spks.mapel_tujuan, guru.mapel))) AS mapel');
        if ($kecualikanSpkId !== null) {
            $spkQuery->where('hasil_spks.id', '!=', $kecualikanSpkId);
        }
        if ($mapel->isNotEmpty()) {
            $spkQuery->whereIn(DB::raw('LOWER(TRIM(COALESCE(hasil_spks.mapel_tujuan, guru.mapel)))'), $mapel->all());
        }
        foreach ($spkQuery->get() as $alokasi) {
            self::catatReservasi($reservasi, $tersedia, (int) $alokasi->sekolah_tujuan_id, (string) $alokasi->mapel, (int) $alokasi->guru_id);
        }

        $usulanQuery = UsulanPenempatan::whereIn('usulan_penempatan.status', UsulanPenempatan::AKTIF)
            ->join('guru', 'guru.id', '=', 'usulan_penempatan.guru_id')
            ->selectRaw('usulan_penempatan.id, usulan_penempatan.guru_id, usulan_penempatan.sekolah_id, LOWER(TRIM(guru.mapel)) AS mapel');
        if ($kecualikanUsulanId !== null) {
            $usulanQuery->where('usulan_penempatan.id', '!=', $kecualikanUsulanId);
        }
        if ($mapel->isNotEmpty()) {
            $usulanQuery->whereIn(DB::raw('LOWER(TRIM(guru.mapel))'), $mapel->all());
        }
        foreach ($usulanQuery->get() as $alokasi) {
            self::catatReservasi($reservasi, $tersedia, (int) $alokasi->sekolah_id, (string) $alokasi->mapel, (int) $alokasi->guru_id);
        }

        foreach ($reservasi as $kunciFormasi => $guru) {
            $tersedia[$kunciFormasi] -= count($guru);
        }

        return array_map(fn (int $jumlah): int => max(0, $jumlah), $tersedia);
    }

    /**
     * @param array<string, array<int, true>> $reservasi
     * @param array<string, int> $tersedia
     */
    private static function catatReservasi(array &$reservasi, array $tersedia, int $sekolahId, string $mapel, int $guruId): void
    {
        $kunciFormasi = $sekolahId.'|'.strtolower(trim($mapel));
        if (array_key_exists($kunciFormasi, $tersedia)) {
            $reservasi[$kunciFormasi][$guruId] = true;
        }
    }
}
