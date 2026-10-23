<?php

namespace Tests\Unit;

use App\Services\AnalisisKebutuhanGuru;
use PHPUnit\Framework\TestCase;

class AnalisisKebutuhanGuruTest extends TestCase
{
    /**
     * SD 6 rombel (contoh: SDN 107 Sopobatu): guru kelas 6, PJOK 1, Agama Islam 1.
     */
    public function test_sd_enam_rombel(): void
    {
        $baris = AnalisisKebutuhanGuru::hitung('SD', 6);
        $perLabel = $this->indeks($baris);

        $this->assertSame(6, $perLabel['Guru Kelas']['kebutuhan']);
        $this->assertSame(1, $perLabel['PJOK']['kebutuhan']);   // ceil(6*3/24)
        $this->assertSame(1, $perLabel['Agama Islam']['kebutuhan']);  // ceil(6*3/24)
        $this->assertNull($perLabel['Guru Kelas']['jp_per_minggu']);
        $this->assertSame(3, $perLabel['PJOK']['jp_per_minggu']);
    }

    /**
     * SMP 10 rombel (contoh: SMP Muhammadiyah 31 Panyabungan): total 16.
     */
    public function test_smp_sepuluh_rombel(): void
    {
        $baris = AnalisisKebutuhanGuru::hitung('SMP', 10);
        $perLabel = $this->indeks($baris);

        $this->assertSame(3, $perLabel['Bahasa Indonesia']['kebutuhan']); // ceil(50/24)
        $this->assertSame(2, $perLabel['Matematika']['kebutuhan']);       // ceil(40/24)
        $this->assertSame(2, $perLabel['IPA']['kebutuhan']);
        $this->assertSame(2, $perLabel['IPS']['kebutuhan']);             // ceil(30/24)
        $this->assertSame(2, $perLabel['Bahasa Inggris']['kebutuhan']);
        $this->assertSame(1, $perLabel['Agama Islam']['kebutuhan']);           // ceil(20/24)
        $this->assertSame(1, $perLabel['Informatika']['kebutuhan']);
        $this->assertSame(16, array_sum(array_column($baris, 'kebutuhan')));
        // Pemisahan label agama: analisis massal hanya menghitung Agama
        // Islam; Agama Kristen diisi manual per sekolah.
        $this->assertArrayNotHasKey('Agama', $perLabel);
        $this->assertArrayNotHasKey('Agama Kristen', $perLabel);
    }

    /**
     * Selisih = eksisting − kebutuhan (negatif = kurang).
     */
    public function test_selisih_eksisting(): void
    {
        $baris = AnalisisKebutuhanGuru::hitung('SMP', 10, ['Matematika' => 1, 'IPA' => 5]);
        $perLabel = $this->indeks($baris);

        $this->assertSame(-1, $perLabel['Matematika']['selisih']);
        $this->assertSame(3, $perLabel['IPA']['selisih']);
        $this->assertSame(0, $perLabel['IPS']['eksisting']);
    }

    /**
     * Rombel nol → kebutuhan nol, tidak error.
     */
    public function test_rombel_nol(): void
    {
        $baris = AnalisisKebutuhanGuru::hitung('SD', 0);

        foreach ($baris as $b) {
            $this->assertSame(0, $b['kebutuhan']);
        }
    }

    /**
     * Normalisasi alias nama mapel ke label kanonis.
     */
    public function test_kanonis_alias(): void
    {
        $this->assertSame('IPA', AnalisisKebutuhanGuru::kanonis('Ilmu Pengetahuan Alam'));
        $this->assertSame('PJOK', AnalisisKebutuhanGuru::kanonis('penjaskes'));
        $this->assertSame('Agama Islam', AnalisisKebutuhanGuru::kanonis('PAI'));
        $this->assertSame('Guru Kelas', AnalisisKebutuhanGuru::kanonis('wali kelas'));
        $this->assertSame('Fisika', AnalisisKebutuhanGuru::kanonis('Fisika'));
        $this->assertSame('', AnalisisKebutuhanGuru::kanonis('  '));
    }

    /**
     * @param  array<int, array{label: string, jp_per_minggu: ?int, kebutuhan: int, eksisting: int, selisih: int}>  $baris
     * @return array<string, array{label: string, jp_per_minggu: ?int, kebutuhan: int, eksisting: int, selisih: int}>
     */
    private function indeks(array $baris): array
    {
        $hasil = [];
        foreach ($baris as $b) {
            $hasil[$b['label']] = $b;
        }

        return $hasil;
    }
}
