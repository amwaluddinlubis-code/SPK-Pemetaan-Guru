<?php

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\User;
use App\Services\Linieritas;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// Linieritas fase 1 (Kepmen 222/O/2025): bidang studi sertifikasi tersimpan
// di guru (kode diturunkan otomatis), dan status linieritas penempatan
// dinilai dari bidang studi serdik — bukan lagi sekadar label mapel.
class LinieritasTest extends TestCase
{
    use RefreshDatabase;

    private function guruSerdik(?string $bidang, bool $sertifikasi = true): Guru
    {
        return new Guru([
            'nama' => 'Guru Uji',
            'mapel' => 'Matematika',
            'jenjang' => 'SMP',
            'sudah_sertifikasi' => $sertifikasi,
            'bidang_studi_serdik' => $bidang,
        ]);
    }

    public function test_peta_linieritas_kepmen(): void
    {
        // Serdik Matematika linier ke Informatika di SMP (kelompok mapel).
        $this->assertContains('Informatika', Linieritas::mapelLinier('Matematika', 'SMP'));
        // Keluarga IPA linier ke IPA.
        $this->assertContains('IPA', Linieritas::mapelLinier('Biologi', 'SMP'));
        // Di SD hampir semua serdik linier ke Guru Kelas.
        $this->assertContains('Guru Kelas', Linieritas::mapelLinier('Biologi', 'SD'));
        // BK di SD linier ke Guru Kelas; di SMP ke Bimbingan Konseling.
        $this->assertContains('Guru Kelas', Linieritas::mapelLinier('Guru Bimbingan Konseling (Konselor)', 'SD'));
        $this->assertSame(['Bimbingan Konseling'], Linieritas::mapelLinier('Guru Bimbingan Konseling (Konselor)', 'SMP'));
        // Bidang kejuruan SMP mengikuti pola dominan Lampiran III: Prakarya.
        $this->assertSame(['Seni/Prakarya'], Linieritas::mapelLinier('Teknik Mesin', 'SMP'));
        // Bidang tak dikenal → null (tidak dapat dinilai).
        $this->assertNull(Linieritas::mapelLinier('Bidang Fiktif', 'SMP'));
    }

    public function test_status_linier_dan_tidak_linier(): void
    {
        $linier = Linieritas::statusUntuk($this->guruSerdik('Matematika'), 'SMP', 'Informatika');
        $this->assertSame('linier', $linier['status']);

        $tidak = Linieritas::statusUntuk($this->guruSerdik('Bahasa Inggris'), 'SMP', 'Matematika');
        $this->assertSame('tidak_linier', $tidak['status']);
    }

    public function test_status_agama_selalu_di_luar_cakupan(): void
    {
        $s = Linieritas::statusUntuk($this->guruSerdik('Bahasa Inggris'), 'SMP', 'Agama');
        $this->assertSame('luar_cakupan', $s['status']);
    }

    public function test_status_kasus_khusus(): void
    {
        // Belum bersertifikat → tanpa label.
        $this->assertSame('tanpa_label',
            Linieritas::statusUntuk($this->guruSerdik(null, false), 'SMP', 'IPA')['status']);
        // Bersertifikat tapi bidang studi kosong → belum ada data.
        $this->assertSame('belum_ada_data',
            Linieritas::statusUntuk($this->guruSerdik(null), 'SMP', 'IPA')['status']);
        // Bidang studi tak terdaftar → tidak dapat dinilai (bukan "tidak linier").
        $this->assertSame('tidak_dapat_dinilai',
            Linieritas::statusUntuk($this->guruSerdik('Bidang Fiktif'), 'SMP', 'IPA')['status']);
    }

    public function test_kode_diturunkan_dari_nama_bidang(): void
    {
        $this->assertSame('094/180/318', Linieritas::kodeUntuk('Matematika', 'SMP'));
        $this->assertSame('810', Linieritas::kodeUntuk('Guru Bimbingan Konseling (Konselor)', 'SD'));
        $this->assertNull(Linieritas::kodeUntuk('Bidang Fiktif', 'SMP'));
        $nama = collect(Linieritas::daftarBidangStudi())->pluck('nama');
        $this->assertTrue($nama->contains('Guru Kelas SD/Umum (kelas awal dan akhir)'));
        $this->assertTrue($nama->contains('Teknik Mesin'));
    }

    public function test_form_memuat_pilihan_bidang_studi(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'kabid_ptk']));

        $res = $this->get('/guru/create');
        $res->assertOk();
        $res->assertSee('id="f-bidang_studi_serdik"', false);
        $res->assertSee('Bidang Studi Sertifikat Pendidik');
    }

    public function test_store_mengisi_kode_bidang_otomatis(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'kabid_ptk']));

        $this->post('/guru', [
            'nip' => '198501012010011001',
            'nama' => 'Siti Aminah',
            'mapel' => 'Matematika',
            'jenjang' => 'SMP',
            'domisili_alamat' => 'Jl. Mawar No. 10',
            'domisili_lat' => '0.79',
            'domisili_lng' => '99.41',
            'sekolah_saat_ini' => 'SMPN 1 Panyabungan',
            'sudah_sertifikasi' => '1',
            'bidang_studi_serdik' => 'Matematika',
        ])->assertRedirect(route('guru.index'));

        $guru = Guru::where('nip', '198501012010011001')->firstOrFail();
        $this->assertSame('Matematika', $guru->bidang_studi_serdik);
        $this->assertSame('094/180/318', $guru->kode_bidang_studi);
    }
}
