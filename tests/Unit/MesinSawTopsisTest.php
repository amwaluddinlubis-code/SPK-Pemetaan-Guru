<?php

// Tes unit mesin peringkat hibrida SAW + TOPSIS.
// Nilai acuan pada test pertama dihitung manual dari rumus baku:
// SAW = jumlah (bobot x normalisasi benefit/cost);
// TOPSIS = D- / (D+ + D-) dari matriks ternormalisasi vektor terbobot;
// hibrida = rata-rata keduanya.

namespace Tests\Unit;

use App\Services\MesinSawTopsis;
use PHPUnit\Framework\TestCase;

class MesinSawTopsisTest extends TestCase
{
    public function test_bobot_kriteria_berjumlah_satu_dan_nilai_daerah_valid(): void
    {
        $total = array_sum(array_column(MesinSawTopsis::KRITERIA, 'bobot'));
        $this->assertEqualsWithDelta(1.0, $total, 0.000001);
        $this->assertSame(1, MesinSawTopsis::nilaiDaerah('biasa'));
        $this->assertSame(2, MesinSawTopsis::nilaiDaerah('terpencil'));
        $this->assertSame(3, MesinSawTopsis::nilaiDaerah('sangat_terpencil'));
        $this->assertSame(1, MesinSawTopsis::nilaiDaerah(null));
        $this->assertSame(1, MesinSawTopsis::nilaiDaerah('tidak-dikenal'));
    }

    public function test_peringkat_dan_skor_sesuai_hitung_manual(): void
    {
        $hasil = (new MesinSawTopsis)->peringkat([
            ['kode' => 'A', 'hemat_km' => 30, 'jarak_tujuan_km' => 5, 'sisa_formasi' => 1, 'prioritas_daerah' => 1],
            ['kode' => 'B', 'hemat_km' => 25, 'jarak_tujuan_km' => 10, 'sisa_formasi' => 4, 'prioritas_daerah' => 3],
            ['kode' => 'C', 'hemat_km' => 20, 'jarak_tujuan_km' => 15, 'sisa_formasi' => 2, 'prioritas_daerah' => 2],
        ]);

        // B menang karena keunggulan formasi + daerah mengalahkan
        // keunggulan jarak A; C terakhir pada hampir semua kriteria.
        $this->assertSame(['B', 'A', 'C'], array_column($hasil, 'kode'));
        $this->assertEqualsWithDelta(0.808333, $hasil[0]['skor_saw'], 0.00001);
        $this->assertEqualsWithDelta(0.701654, $hasil[0]['skor_topsis'], 0.00001);
        $this->assertEqualsWithDelta(0.754994, $hasil[0]['skor_hybrid'], 0.00001);
        $this->assertEqualsWithDelta(0.745833, $hasil[1]['skor_saw'], 0.00001);
        $this->assertEqualsWithDelta(0.484424, $hasil[1]['skor_topsis'], 0.00001);
        $this->assertEqualsWithDelta(0.615128, $hasil[1]['skor_hybrid'], 0.00001);
        $this->assertSame(1, $hasil[0]['peringkat_ke']);
        $this->assertSame('greedy-saw-topsis-v1', $hasil[0]['detail_skor']['versi']);
    }

    public function test_alternatif_dominan_skor_satu_dan_terdominasi_mendekati_nol(): void
    {
        $hasil = (new MesinSawTopsis)->peringkat([
            ['kode' => 'baik', 'hemat_km' => 20, 'jarak_tujuan_km' => 5, 'sisa_formasi' => 3, 'prioritas_daerah' => 3],
            ['kode' => 'buruk', 'hemat_km' => 10, 'jarak_tujuan_km' => 10, 'sisa_formasi' => 1, 'prioritas_daerah' => 1],
        ]);

        $this->assertSame('baik', $hasil[0]['kode']);
        $this->assertEqualsWithDelta(1.0, $hasil[0]['skor_hybrid'], 0.000001);
        $this->assertEqualsWithDelta(0.0, $hasil[1]['skor_topsis'], 0.000001);
        $this->assertLessThan(0.30, $hasil[1]['skor_hybrid']);
    }

    public function test_kasus_tepi_tunggal_dan_identik_tidak_membagi_nol(): void
    {
        $tunggal = (new MesinSawTopsis)->peringkat([
            ['kode' => 'satu', 'hemat_km' => 12, 'jarak_tujuan_km' => 8, 'sisa_formasi' => 1, 'prioritas_daerah' => 1],
        ]);
        $this->assertEqualsWithDelta(1.0, $tunggal[0]['skor_hybrid'], 0.000001);

        $identik = (new MesinSawTopsis)->peringkat([
            ['kode' => 'x', 'hemat_km' => 12, 'jarak_tujuan_km' => 8, 'sisa_formasi' => 1, 'prioritas_daerah' => 1],
            ['kode' => 'y', 'hemat_km' => 12, 'jarak_tujuan_km' => 8, 'sisa_formasi' => 1, 'prioritas_daerah' => 1],
        ]);
        $this->assertEqualsWithDelta(1.0, $identik[0]['skor_hybrid'], 0.000001);
        $this->assertEqualsWithDelta(1.0, $identik[1]['skor_hybrid'], 0.000001);
        $this->assertSame([], (new MesinSawTopsis)->peringkat([]));
    }
}
