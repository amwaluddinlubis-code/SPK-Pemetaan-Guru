<?php

// Model KebutuhanGuru: satu baris = "sekolah X butuh N guru mapel Y".

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class KebutuhanGuru extends Model
{
    // Nama tabel tidak mengikuti jamak Inggris, jadi disebut eksplisit
    protected $table = 'kebutuhan_guru';

    protected $fillable = [
        'sekolah_id',
        'mapel',
        'jumlah',
    ];

    /**
     * Setiap kebutuhan milik SATU sekolah.
     */
    public function sekolah()
    {
        return $this->belongsTo(Sekolah::class);
    }

    /**
     * Kurangi sisa kebutuhan formasi saat penempatan disetujui.
     * Tidak akan turun di bawah nol. Mengembalikan sisa kebutuhan.
     */
    public static function kurangi(int $sekolahId, string $mapel, int $jumlah = 1): int
    {
        $butuh = self::where('sekolah_id', $sekolahId)
            ->where('mapel', $mapel)
            ->lockForUpdate()
            ->first();

        if (! $butuh) {
            return 0;
        }

        $butuh->jumlah = max(0, $butuh->jumlah - $jumlah);
        $butuh->save();

        return $butuh->jumlah;
    }

    /**
     * Sisa kebutuhan formasi per (sekolah, mapel), sudah dikurangi
     * alokasi dari rekomendasi SPK yang masih berstatus 'rekomendasi'.
     * Dipakai saat generate agar satu formasi tidak dijanjikan ke
     * dua guru berbeda.
     *
     * @return array<string, int> kunci: "sekolah_id|mapel"
     */
    public static function sisaDenganAlokasi(): array
    {
        $sisa = [];
        foreach (self::all() as $k) {
            $sisa[$k->sekolah_id.'|'.strtolower(trim($k->mapel))] = (int) $k->jumlah;
        }

        // Alokasi mengurangi formasi yang benar-benar diisi (mapel_tujuan;
        // rekomendasi lama memakai mapel guru).
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
}
