<?php

// Tes simulasi what-if (C): determinisme, validasi, bukti data tak berubah.

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\HasilSpk;
use App\Models\KebutuhanGuru;
use App\Models\Sekolah;
use App\Models\User;
use App\Services\SimulasiKebijakan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SimulasiTest extends TestCase
{
    use RefreshDatabase;

    private int $seq = 1300;

    private function buatUser(string $role): User
    {
        return User::create([
            'name' => 'User '.$role.' '.++$this->seq,
            'email' => $role.$this->seq.'@test.local',
            'password' => Hash::make('rahasia123'),
            'role' => $role,
        ]);
    }

    private function skenario(): array
    {
        $asal = Sekolah::create([
            'npsn' => '1'.++$this->seq, 'nama' => 'Asal', 'alamat' => 'A',
            'kecamatan' => 'Panyabungan', 'lat' => 1.0, 'lng' => 100.4,
        ]);
        $tujuan = Sekolah::create([
            'npsn' => '1'.++$this->seq, 'nama' => 'Tujuan', 'alamat' => 'A',
            'kecamatan' => 'Panyabungan', 'lat' => 1.0, 'lng' => 100.05,
        ]);
        $guru = Guru::create([
            'nip' => 'NIP'.++$this->seq, 'nama' => 'Guru Simulasi',
            'mapel' => 'Matematika', 'jenjang' => 'SD',
            'domisili_alamat' => 'D', 'kecamatan' => 'Panyabungan',
            'sekolah_id' => $asal->id, 'sekolah_saat_ini' => 'Asal',
            'domisili_lat' => 1.0, 'domisili_lng' => 100.0,
        ]);
        KebutuhanGuru::create(['sekolah_id' => $tujuan->id, 'mapel' => 'Matematika', 'jumlah' => 2]);

        return [$guru, $asal, $tujuan];
    }

    public function test_simulasi_tidak_mengubah_data_operasional(): void
    {
        $kabid = $this->buatUser('kabid_ptk');
        [$guru, $asal, $tujuan] = $this->skenario();

        $jmlSpkSebelum = HasilSpk::count();
        $jmlGuruSebelum = Guru::count();

        $res = $this->actingAs($kabid)->post('/simulasi', ['ambang_km' => 15]);
        $res->assertOk();
        $res->assertSee('Hasil Simulasi', false);

        // Data operasional tidak berubah
        $this->assertEquals($jmlSpkSebelum, HasilSpk::count());
        $this->assertEquals($jmlGuruSebelum, Guru::count());
        $this->assertEquals($asal->id, $guru->fresh()->sekolah_id);
        $this->assertEquals(2, KebutuhanGuru::where('sekolah_id', $tujuan->id)->value('jumlah'));
    }

    public function test_simulasi_deterministik(): void
    {
        $this->buatUser('kabid_ptk');
        $this->skenario();

        $svc = new SimulasiKebijakan;
        $a = $svc->jalankan(['ambang_km' => 15]);
        $b = $svc->jalankan(['ambang_km' => 15]);

        $this->assertEquals($a['jumlah_kandidat'], $b['jumlah_kandidat']);
        $this->assertEquals($a['total_hemat_km'], $b['total_hemat_km']);
        $this->assertGreaterThan(0, $a['jumlah_kandidat']);
    }

    public function test_simulasi_validasi_input(): void
    {
        $kabid = $this->buatUser('kabid_ptk');

        $this->actingAs($kabid)->post('/simulasi', ['ambang_km' => 0])
            ->assertSessionHasErrors('ambang_km');
        $this->actingAs($kabid)->post('/simulasi', ['ambang_km' => 9999])
            ->assertSessionHasErrors('ambang_km');
    }

    public function test_simulasi_menampilkan_disclaimer(): void
    {
        $kabid = $this->buatUser('kabid_ptk');
        $this->skenario();

        $this->actingAs($kabid)->post('/simulasi', ['ambang_km' => 15])
            ->assertOk()
            ->assertSee('hipotetis', false)
            ->assertSee('tidak', false);
    }

    public function test_akses_dibatasi(): void
    {
        $guru = $this->buatUser('guru');

        $this->actingAs($guru)->get('/simulasi')->assertForbidden();
        $this->actingAs($guru)->post('/simulasi', ['ambang_km' => 15])->assertForbidden();
    }
}
