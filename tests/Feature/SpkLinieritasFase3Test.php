<?php

// Linieritas fase 3: perkiraan beban JP di sekolah tujuan (peringatan
// < 24 JP), info Tunjangan Khusus daerah khusus, dan tiga pemeriksaan
// Kualitas Data baru (tugas tidak linier, bidang serdik kosong, data
// pendukung Tambahan Penghasilan belum lengkap).

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\HasilSpk;
use App\Models\Sekolah;
use App\Services\PemeriksaKualitasData;
use App\Services\PerkiraanBebanKerja;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SpkLinieritasFase3Test extends TestCase
{
    use RefreshDatabase;

    private int $seq = 9100;

    private function buatSekolah(array $attr = []): Sekolah
    {
        return Sekolah::create(array_merge([
            'npsn' => '9'.++$this->seq,
            'nama' => 'Sekolah '.$this->seq,
            'alamat' => 'Alamat',
            'kecamatan' => 'Panyabungan',
            'kategori_daerah' => 'biasa',
            'jenjang' => 'SMP',
            'rombel' => 6,
            'lat' => 0.9,
            'lng' => 99.6,
        ], $attr));
    }

    private function buatGuru(Sekolah $sekolah, array $attr = []): Guru
    {
        return Guru::create(array_merge([
            'nip' => 'NIP'.++$this->seq,
            'nama' => 'Guru '.$this->seq,
            'mapel' => 'Matematika',
            'jenjang' => 'SMP',
            'domisili_alamat' => 'Domisili',
            'kecamatan' => 'Panyabungan',
            'status_pernikahan' => 'belum_kawin',
            'sekolah_id' => $sekolah->id,
            'sekolah_saat_ini' => $sekolah->nama,
            'domisili_lat' => 0.9,
            'domisili_lng' => 99.55,
        ], $attr));
    }

    private function buatHasil(Guru $guru, Sekolah $asal, Sekolah $tujuan, ?string $mapelTujuan = null): HasilSpk
    {
        return HasilSpk::create([
            'guru_id' => $guru->id,
            'sekolah_asal_id' => $asal->id,
            'sekolah_tujuan_id' => $tujuan->id,
            'mapel_tujuan' => $mapelTujuan,
            'jarak_km' => 5.0,
            'jarak_sekarang_km' => 30.0,
            'hemat_km' => 25.0,
            'status' => 'rekomendasi',
        ]);
    }

    public function test_perkiraan_beban_smp_cukup_dan_kurang(): void
    {
        $asal = $this->buatSekolah();
        $guru = $this->buatGuru($asal);

        // Rombel 6 × 4 JP Matematika = 24 JP, dibagi 1 guru → pas 24.
        $cukup = $this->buatHasil($guru, $asal, $this->buatSekolah(['rombel' => 6]));
        $hasil = PerkiraanBebanKerja::untukRekomendasi($cukup);
        $this->assertSame(24.0, $hasil['jp']);
        $this->assertTrue($hasil['cukup']);

        // Rombel 3 × 4 JP = 12 JP → di bawah beban penuh.
        $kurang = $this->buatHasil($guru, $asal, $this->buatSekolah(['rombel' => 3]));
        $hasil = PerkiraanBebanKerja::untukRekomendasi($kurang);
        $this->assertSame(12.0, $hasil['jp']);
        $this->assertFalse($hasil['cukup']);

        // Rombel 9 × 4 JP = 36 JP tetapi sudah ada 1 guru Matematika → 18.
        $tujuanPadat = $this->buatSekolah(['rombel' => 9]);
        $this->buatGuru($tujuanPadat);
        $padat = $this->buatHasil($guru, $asal, $tujuanPadat);
        $hasil = PerkiraanBebanKerja::untukRekomendasi($padat);
        $this->assertSame(18.0, $hasil['jp']);
        $this->assertFalse($hasil['cukup']);
    }

    public function test_perkiraan_beban_guru_kelas_sd_selalu_penuh_dan_rombel_kosong_null(): void
    {
        $asal = $this->buatSekolah(['jenjang' => 'SD']);
        $guru = $this->buatGuru($asal, ['mapel' => 'Guru Kelas', 'jenjang' => 'SD']);

        $tujuan = $this->buatSekolah(['jenjang' => 'SD', 'rombel' => 6]);
        $hasil = PerkiraanBebanKerja::untukRekomendasi(
            $this->buatHasil($guru, $asal, $tujuan, 'Guru Kelas')
        );
        $this->assertSame(24.0, $hasil['jp']);
        $this->assertTrue($hasil['cukup']);

        $tanpaRombel = $this->buatSekolah(['rombel' => 0]);
        $this->assertNull(PerkiraanBebanKerja::untukRekomendasi(
            $this->buatHasil($guru, $asal, $tanpaRombel, 'Matematika')
        ));
    }

    public function test_tujuan_daerah_khusus_terdeteksi(): void
    {
        $asal = $this->buatSekolah();
        $guru = $this->buatGuru($asal);

        $khusus = $this->buatHasil($guru, $asal, $this->buatSekolah(['kategori_daerah' => 'sangat_terpencil']));
        $this->assertTrue($khusus->tujuanDaerahKhusus());

        $biasa = $this->buatHasil($guru, $asal, $this->buatSekolah(['kategori_daerah' => 'biasa']));
        $this->assertFalse($biasa->tujuanDaerahKhusus());
    }

    public function test_kualitas_data_tiga_pemeriksaan_linieritas_dan_tp(): void
    {
        $sekolah = $this->buatSekolah();

        $tidakLinier = $this->buatGuru($sekolah, [
            'mapel' => 'IPA',
            'sudah_sertifikasi' => true,
            'bidang_studi_serdik' => 'Bahasa Inggris',
            'kode_bidang_studi' => '157',
        ]);
        $bidangKosong = $this->buatGuru($sekolah, ['sudah_sertifikasi' => true]);
        $tpKosong = $this->buatGuru($sekolah);
        $tpRendah = $this->buatGuru($sekolah, [
            'nuptk' => '1234567890123456',
            'pendidikan_terakhir' => 'SMA/SMK sederajat',
        ]);
        $tpLengkap = $this->buatGuru($sekolah, [
            'nuptk' => '1234567890123456',
            'pendidikan_terakhir' => 'S-1',
        ]);
        $guruAgama = $this->buatGuru($sekolah, [
            'mapel' => 'Agama',
            'sudah_sertifikasi' => true,
            'bidang_studi_serdik' => 'Bahasa Inggris',
        ]);

        $temuan = collect((new PemeriksaKualitasData)->periksa());
        $jenisUntuk = fn (Guru $g) => $temuan
            ->where('entitas', 'Guru')->where('id', $g->id)
            ->pluck('jenis')->all();

        $this->assertContains('tugas_tidak_linier', $jenisUntuk($tidakLinier));
        $this->assertContains('bidang_serdik_kosong', $jenisUntuk($bidangKosong));
        $this->assertContains('pendukung_tp_kurang', $jenisUntuk($tpKosong));
        $this->assertContains('pendukung_tp_kurang', $jenisUntuk($tpRendah));
        $this->assertNotContains('pendukung_tp_kurang', $jenisUntuk($tpLengkap));
        // Pagar Kepmen: guru Agama tidak pernah ditandai tidak linier.
        $this->assertNotContains('tugas_tidak_linier', $jenisUntuk($guruAgama));
    }
}
