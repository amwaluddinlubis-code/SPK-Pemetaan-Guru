<?php

// Tes negatif otorisasi objek (IDOR): kepemilikan record per peran pada
// endpoint guru, sekolah, usulan, dan pengguna.

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\Sekolah;
use App\Models\User;
use App\Models\UsulanPenempatan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class OtorisasiIdorTest extends TestCase
{
    use RefreshDatabase;

    private int $seq = 800;

    private function buatUser(string $role, array $attr = []): User
    {
        return User::create(array_merge([
            'name' => 'User '.$role.' '.++$this->seq,
            'email' => $role.$this->seq.'@test.local',
            'password' => Hash::make('rahasia123'),
            'role' => $role,
        ], $attr));
    }

    private function buatSekolah(array $attr = []): Sekolah
    {
        return Sekolah::create(array_merge([
            'npsn' => '8'.str_pad((string) ++$this->seq, 7, '0', STR_PAD_LEFT),
            'nama' => 'Sekolah '.$this->seq,
            'alamat' => 'Jl. Tes '.$this->seq,
            'kecamatan' => 'Panyabungan',
            'lat' => 1.0,
            'lng' => 100.0,
        ], $attr));
    }

    private function buatGuru(Sekolah $sekolah, array $attr = []): Guru
    {
        return Guru::create(array_merge([
            'nip' => 'nip'.++$this->seq,
            'nama' => 'Guru '.$this->seq,
            'mapel' => 'Matematika',
            'jenjang' => 'SD',
            'domisili_alamat' => 'Domisili '.$this->seq,
            'kecamatan' => 'Panyabungan',
            'sekolah_id' => $sekolah->id,
            'domisili_lat' => 1.0,
            'domisili_lng' => 100.0,
            'sekolah_saat_ini' => $sekolah->nama,
            'sekolah_saat_ini_lat' => $sekolah->lat,
            'sekolah_saat_ini_lng' => $sekolah->lng,
        ], $attr));
    }

    public function test_guru_tidak_bisa_ubah_data_guru_lain(): void
    {
        $sekolah = $this->buatSekolah();
        $guruA = $this->buatGuru($sekolah);
        $guruB = $this->buatGuru($sekolah);
        $userA = $this->buatUser('guru', ['guru_id' => $guruA->id]);

        $this->actingAs($userA)->get("/guru/{$guruB->id}/edit")->assertForbidden();
        $this->actingAs($userA)->put("/guru/{$guruB->id}", ['nama' => 'Diubah'])
            ->assertForbidden();
        $this->assertEquals($guruB->nama, $guruB->fresh()->nama);
    }

    public function test_kepala_sekolah_tidak_bisa_ubah_guru_sekolah_lain(): void
    {
        $sekolahA = $this->buatSekolah();
        $sekolahB = $this->buatSekolah();
        $guruB = $this->buatGuru($sekolahB);
        $kepsekA = $this->buatUser('kepala_sekolah', ['sekolah_id' => $sekolahA->id]);

        $this->actingAs($kepsekA)->get("/guru/{$guruB->id}/edit")->assertForbidden();
        $this->actingAs($kepsekA)->put("/guru/{$guruB->id}", ['nama' => 'Diubah'])
            ->assertForbidden();
    }

    public function test_ketua_pgri_ditolak_membuat_guru(): void
    {
        $ketua = $this->buatUser('ketua_pgri', ['kecamatan' => 'Panyabungan']);

        $this->actingAs($ketua)->post('/guru', [])->assertForbidden();
        $this->actingAs($ketua)->get('/guru/create')->assertForbidden();
    }

    public function test_guru_ditolak_mengakses_export_guru(): void
    {
        $user = $this->buatUser('guru');

        $this->actingAs($user)->get('/guru/export')->assertForbidden();
        $this->actingAs($user)->get('/guru/cetak')->assertForbidden();
    }

    public function test_pemda_ditolak_mengakses_edit_guru_langsung(): void
    {
        $pemda = $this->buatUser('pemda');
        $guru = $this->buatGuru($this->buatSekolah());

        $this->actingAs($pemda)->get("/guru/{$guru->id}/edit")->assertForbidden();
        $this->actingAs($pemda)->get('/pengguna')->assertForbidden();
    }

    public function test_kepala_sekolah_tidak_bisa_ubah_sekolah_lain(): void
    {
        $sekolahA = $this->buatSekolah();
        $sekolahB = $this->buatSekolah();
        $kepsekA = $this->buatUser('kepala_sekolah', ['sekolah_id' => $sekolahA->id]);

        $this->actingAs($kepsekA)->get("/sekolah/{$sekolahB->id}/edit")->assertForbidden();
        $this->actingAs($kepsekA)->put("/sekolah/{$sekolahB->id}", ['nama' => 'Diubah'])
            ->assertForbidden();
    }

    public function test_guru_tidak_bisa_melihat_usulan_guru_lain(): void
    {
        $sekolah = $this->buatSekolah();
        $guruA = $this->buatGuru($sekolah);
        $guruB = $this->buatGuru($sekolah);
        $userA = $this->buatUser('guru', ['guru_id' => $guruA->id]);
        $tujuan = $this->buatSekolah();

        $usulan = UsulanPenempatan::create([
            'guru_id' => $guruB->id,
            'sekolah_id' => $tujuan->id,
            'alasan' => 'Dekat rumah',
            'status' => 'diajukan',
            'diajukan_oleh' => $userA->id,
        ]);

        $this->actingAs($userA)->get("/usulan/{$usulan->id}")->assertForbidden();
    }

    public function test_guru_ditolak_verifikasi_usulan(): void
    {
        $user = $this->buatUser('guru');
        $usulan = UsulanPenempatan::create([
            'guru_id' => $this->buatGuru($this->buatSekolah())->id,
            'sekolah_id' => $this->buatSekolah()->id,
            'alasan' => 'Dekat rumah',
            'status' => 'diajukan',
            'diajukan_oleh' => $user->id,
        ]);

        $this->actingAs($user)->post("/usulan/{$usulan->id}/verifikasi")->assertForbidden();
        $this->actingAs($user)->post("/usulan/{$usulan->id}/setujui")->assertForbidden();
        $this->assertEquals('diajukan', $usulan->fresh()->status);
    }

    public function test_kadis_tetap_bisa_membuka_daftar_guru(): void
    {
        $kadis = $this->buatUser('kadis');

        $this->actingAs($kadis)->get('/guru')->assertOk();
        $this->actingAs($kadis)->get('/sekolah')->assertOk();
    }
}
