<?php

// Tes proyeksi (F): jujur menampilkan "data belum cukup", tanpa angka karangan.

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProyeksiTest extends TestCase
{
    use RefreshDatabase;

    private int $seq = 1400;

    private function buatUser(string $role): User
    {
        return User::create([
            'name' => 'User '.$role.' '.++$this->seq,
            'email' => $role.$this->seq.'@test.local',
            'password' => Hash::make('rahasia123'),
            'role' => $role,
        ]);
    }

    public function test_menampilkan_data_belum_cukup_tanpa_angka_karangan(): void
    {
        $kabid = $this->buatUser('kabid_ptk');

        $res = $this->actingAs($kabid)->get('/proyeksi');
        $res->assertOk();
        $res->assertSee('Data belum cukup', false);
        $res->assertSee('tidak mengarang', false);
        // Tidak boleh ada klaim AI/akurasi
        $res->assertDontSee('kecerdasan buatan', false);
    }

    public function test_menampilkan_syarat_dan_metodologi(): void
    {
        $kadis = $this->buatUser('kadis');

        $res = $this->actingAs($kadis)->get('/proyeksi');
        $res->assertOk();
        $res->assertSee('Syarat Data', false);
        $res->assertSee('Metodologi', false);
    }

    public function test_akses_dibatasi(): void
    {
        $guru = $this->buatUser('guru');

        $this->actingAs($guru)->get('/proyeksi')->assertForbidden();
    }
}
