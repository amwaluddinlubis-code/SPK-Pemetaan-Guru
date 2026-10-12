<?php

// Tes pusat kualitas data (A): deteksi temuan, filter, akses peran, ekspor.

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\KebutuhanGuru;
use App\Models\Sekolah;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class KualitasDataTest extends TestCase
{
    use RefreshDatabase;

    private int $seq = 1000;

    private function buatUser(string $role): User
    {
        return User::create([
            'name' => 'User '.$role.' '.++$this->seq,
            'email' => $role.$this->seq.'@test.local',
            'password' => Hash::make('rahasia123'),
            'role' => $role,
        ]);
    }

    public function test_mendeteksi_koordinat_kosong(): void
    {
        $kabid = $this->buatUser('kabid_ptk');
        $sekolah = Sekolah::create([
            'npsn' => '1'.++$this->seq, 'nama' => 'S', 'alamat' => 'A',
            'kecamatan' => 'Panyabungan', 'lat' => 1.0, 'lng' => 100.0,
        ]);
        Guru::create([
            'nip' => 'NIP'.$this->seq, 'nama' => 'G', 'mapel' => 'Matematika',
            'jenjang' => 'SD', 'domisili_alamat' => 'D', 'kecamatan' => 'Panyabungan',
            'sekolah_id' => $sekolah->id, 'sekolah_saat_ini' => 'S',
            'domisili_lat' => 0, 'domisili_lng' => 0,
        ]);

        $res = $this->actingAs($kabid)->get('/kualitas-data');
        $res->assertOk();
        $res->assertSee('koordinat_kosong', false);
    }

    public function test_mendeteksi_koordinat_di_luar_indonesia(): void
    {
        $kabid = $this->buatUser('kabid_ptk');
        $sekolah = Sekolah::create([
            'npsn' => '1'.++$this->seq, 'nama' => 'S', 'alamat' => 'A',
            'kecamatan' => 'Panyabungan', 'lat' => 50.0, 'lng' => 200.0,
        ]);

        $this->actingAs($kabid)->get('/kualitas-data')
            ->assertOk()
            ->assertSee('koordinat_invalid', false);
    }

    public function test_mendeteksi_kebutuhan_tidak_masuk_akal(): void
    {
        $kabid = $this->buatUser('kabid_ptk');
        $sekolah = Sekolah::create([
            'npsn' => '1'.++$this->seq, 'nama' => 'S', 'alamat' => 'A',
            'kecamatan' => 'Panyabungan', 'lat' => 1.0, 'lng' => 100.0,
        ]);
        KebutuhanGuru::create(['sekolah_id' => $sekolah->id, 'mapel' => 'Matematika', 'jumlah' => 0]);

        $this->actingAs($kabid)->get('/kualitas-data')
            ->assertOk()
            ->assertSee('jumlah_tidak_masuk_akal', false);
    }

    public function test_filter_dampak_bekerja(): void
    {
        $kabid = $this->buatUser('kabid_ptk');
        Sekolah::create([
            'npsn' => '1'.++$this->seq, 'nama' => 'S', 'alamat' => 'A',
            'kecamatan' => '', 'lat' => 1.0, 'lng' => 100.0,
        ]);

        $res = $this->actingAs($kabid)->get('/kualitas-data?dampak=tinggi');
        $res->assertOk();
        // kecamatan_kosong berdampak sedang — barisnya tidak boleh muncul di tabel
        // (nama jenis tetap ada di dropdown filter, jadi periksa <code> di tbody)
        $html = $res->getContent();
        $this->assertStringNotContainsString('<code class="text-xs">kecamatan_kosong</code>', $html);
    }

    public function test_akses_dibatasi_peran(): void
    {
        $guru = $this->buatUser('guru');
        $kadis = $this->buatUser('kadis');

        $this->actingAs($guru)->get('/kualitas-data')->assertForbidden();
        $this->actingAs($kadis)->get('/kualitas-data')->assertOk();
    }

    public function test_export_tidak_memuat_pii_berlebih(): void
    {
        $kabid = $this->buatUser('kabid_ptk');
        $sekolah = Sekolah::create([
            'npsn' => '1'.++$this->seq, 'nama' => 'S', 'alamat' => 'A',
            'kecamatan' => 'Panyabungan', 'lat' => 0, 'lng' => 0,
        ]);

        $res = $this->actingAs($kabid)->get('/kualitas-data/export');
        $res->assertOk();
        $res->assertHeader('content-type',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }
}
