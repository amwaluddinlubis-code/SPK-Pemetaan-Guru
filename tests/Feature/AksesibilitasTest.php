<?php

// Tes analisis aksesibilitas (E): agregat benar, Haversine konsisten, privasi, akses.

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\Sekolah;
use App\Models\User;
use App\Services\AnalisisJarak;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AksesibilitasTest extends TestCase
{
    use RefreshDatabase;

    private int $seq = 1200;

    private function buatUser(string $role): User
    {
        return User::create([
            'name' => 'User '.$role.' '.++$this->seq,
            'email' => $role.$this->seq.'@test.local',
            'password' => Hash::make('rahasia123'),
            'role' => $role,
        ]);
    }

    public function test_agregat_jarak_benar_dan_konsisten_dengan_model(): void
    {
        $kabid = $this->buatUser('kabid_ptk');
        $sekolah = Sekolah::create([
            'npsn' => '1'.++$this->seq, 'nama' => 'S', 'alamat' => 'A',
            'kecamatan' => 'Panyabungan', 'lat' => 1.0, 'lng' => 100.1,
        ]);
        $guru = Guru::create([
            'nip' => 'NIP'.++$this->seq, 'nama' => 'Guru Jarak',
            'mapel' => 'Matematika', 'jenjang' => 'SD',
            'domisili_alamat' => 'D', 'kecamatan' => 'Panyabungan',
            'sekolah_id' => $sekolah->id, 'sekolah_saat_ini' => 'S',
            'domisili_lat' => 1.0, 'domisili_lng' => 100.0,
        ]);

        // Jarak versi model (PHP) sebagai pembanding
        $jarakModel = $guru->jarakKeSekolahSaatIni();

        $svc = new AnalisisJarak;
        $data = $svc->perKecamatan();
        $baris = collect($data)->firstWhere('kecamatan', 'Panyabungan');

        $this->assertNotNull($baris);
        $this->assertEquals(1, $baris['jumlah']);
        // Toleransi pembulatan 0.1 km antara SQL dan PHP
        $this->assertEqualsWithDelta($jarakModel, $baris['rata_km'], 0.1);
    }

    public function test_halaman_tidak_memuat_identitas_guru(): void
    {
        $kabid = $this->buatUser('kabid_ptk');
        $sekolah = Sekolah::create([
            'npsn' => '1'.++$this->seq, 'nama' => 'S', 'alamat' => 'A',
            'kecamatan' => 'Panyabungan', 'lat' => 1.0, 'lng' => 100.1,
        ]);
        $guru = Guru::create([
            'nip' => 'NIP'.++$this->seq, 'nama' => 'Guru Rahasia',
            'mapel' => 'Matematika', 'jenjang' => 'SD',
            'domisili_alamat' => 'D', 'kecamatan' => 'Panyabungan',
            'sekolah_id' => $sekolah->id, 'sekolah_saat_ini' => 'S',
            'domisili_lat' => 1.0, 'domisili_lng' => 100.0,
        ]);

        $res = $this->actingAs($kabid)->get('/aksesibilitas');
        $res->assertOk();
        $res->assertDontSee($guru->nama, false);
        $res->assertSee('Haversine', false);
        // Disclaimer bukan waktu tempuh
        $res->assertSee('bukan', false);
    }

    public function test_akses_dibatasi(): void
    {
        $guru = $this->buatUser('guru');
        $pemda = $this->buatUser('pemda');

        $this->actingAs($guru)->get('/aksesibilitas')->assertForbidden();
        $this->actingAs($pemda)->get('/aksesibilitas')->assertForbidden();
    }
}
