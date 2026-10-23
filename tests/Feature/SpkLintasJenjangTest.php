<?php

// Pengaturan dinas "rekomendasi lintas jenjang" (keputusan AMWAL,
// 23 Okt 2026): Opsi A (bawaan) melarang mesin membuat rekomendasi ke
// jenjang lain; Opsi B mengizinkan dan lencana tingkat 2 dinilai dari
// jenjang guru. Menutup temuan QA Fase 3 (lencana bertabrakan dengan
// keterangan tingkat 2 pada kasus guru SMP → sekolah SD).

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\HasilSpk;
use App\Models\KebutuhanGuru;
use App\Models\Pengaturan;
use App\Models\Sekolah;
use App\Models\User;
use App\Services\Linieritas;
use App\Services\PengaturanSpk;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SpkLintasJenjangTest extends TestCase
{
    use RefreshDatabase;

    private int $seq = 0;

    private function buatUser(string $role): User
    {
        return User::create([
            'name' => 'User '.$role.' '.++$this->seq,
            'email' => $role.$this->seq.'@test.local',
            'password' => Hash::make('rahasia123'),
            'role' => $role,
        ]);
    }

    private function buatSekolah(float $lng, string $jenjang): Sekolah
    {
        return Sekolah::create([
            'npsn' => '7'.++$this->seq,
            'nama' => 'Sekolah '.$this->seq,
            'alamat' => 'Alamat',
            'kecamatan' => 'Panyabungan',
            'kategori_daerah' => 'biasa',
            'jenjang' => $jenjang,
            'lat' => 0.0,
            'lng' => $lng,
            'koordinat_terverifikasi_lat' => 0.0,
            'koordinat_terverifikasi_lng' => $lng,
        ]);
    }

    /** Guru SMP berserdik Matematika; satu-satunya formasi layak adalah
     *  kebutuhan Matematika di sekolah SD (lintas jenjang, tingkat 2). */
    private function skenarioLintasJenjang(): array
    {
        $asal = $this->buatSekolah(100.30, 'SMP');
        $guru = Guru::create([
            'nip' => 'NIP'.++$this->seq,
            'nama' => 'Guru '.$this->seq,
            'mapel' => 'Bahasa Inggris',
            'jenjang' => 'SMP',
            'domisili_alamat' => 'Domisili',
            'kecamatan' => 'Panyabungan',
            'status_pernikahan' => 'belum_kawin',
            'sekolah_id' => $asal->id,
            'sekolah_saat_ini' => $asal->nama,
            'domisili_lat' => 0.0,
            'domisili_lng' => 100.0,
            'sudah_sertifikasi' => true,
            'mapel_sertifikasi' => 'Matematika',
            'bidang_studi_serdik' => 'Matematika',
            'kode_bidang_studi' => '094/180/318',
        ]);
        $tujuan = $this->buatSekolah(100.10, 'SD');
        KebutuhanGuru::create([
            'sekolah_id' => $tujuan->id,
            'mapel' => 'Matematika',
            'jumlah' => 1,
        ]);

        return [$guru, $tujuan];
    }

    public function test_mode_bawaan_adalah_boleh_perilaku_lama(): void
    {
        $this->assertSame('boleh', PengaturanSpk::modeLintasJenjang());
        $this->assertTrue(PengaturanSpk::bolehLintasJenjang());
    }

    public function test_opsi_a_mesin_tidak_membuat_rekomendasi_lintas_jenjang(): void
    {
        $kabid = $this->buatUser('kabid_ptk');
        [$guru] = $this->skenarioLintasJenjang();
        Pengaturan::simpan('spk_lintas_jenjang', 'larang');
        $this->assertFalse(PengaturanSpk::bolehLintasJenjang());

        $this->actingAs($kabid)->post('/spk/generate')->assertRedirect();

        $this->assertSame(0, HasilSpk::where('guru_id', $guru->id)->count(),
            'Mode larang: rekomendasi guru SMP ke sekolah SD tidak boleh terbentuk');
    }

    public function test_opsi_b_rekomendasi_terbentuk_dan_lencana_dinilai_dari_jenjang_guru(): void
    {
        $kabid = $this->buatUser('kabid_ptk');
        [$guru, $tujuan] = $this->skenarioLintasJenjang();
        Pengaturan::simpan('spk_lintas_jenjang', 'boleh');
        $this->assertTrue(PengaturanSpk::bolehLintasJenjang());

        $this->actingAs($kabid)->post('/spk/generate')->assertRedirect();

        $hasil = HasilSpk::where('guru_id', $guru->id)->first();
        $this->assertNotNull($hasil, 'Mode boleh: rekomendasi lintas jenjang harus terbentuk');
        $this->assertSame($tujuan->id, $hasil->sekolah_tujuan_id);
        $this->assertSame(2, $hasil->tingkatLinieritas());

        // Lencana memakai jenjang guru (SMP) → serdik Matematika linier
        // ke Matematika. Dengan acuan jenjang SD hasilnya "tidak linier"
        // (tabrakan temuan QA) — helper memastikan acuan yang benar.
        $this->assertSame('SMP', $hasil->jenjangLinieritas());
        $status = Linieritas::statusUntuk($guru, $hasil->jenjangLinieritas(), $hasil->mapelTujuan());
        $this->assertSame('linier', $status['status']);
    }
}
