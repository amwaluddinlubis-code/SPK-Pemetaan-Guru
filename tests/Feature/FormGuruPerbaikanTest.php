<?php

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\Sekolah;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// Perbaikan form upsert guru (analisis 24 Okt 2026 + keputusan AMWAL):
// jenjang TK tersedia, pendidikan_terakhir muat 60 karakter, select
// mapel_sertifikasi & nama sekolah dihapus (sumber tunggal:
// bidang_studi_serdik & sekolah_id), field administratif terkunci
// per peran, dan CRUD penuh tetap berfungsi.
class FormGuruPerbaikanTest extends TestCase
{
    use RefreshDatabase;

    private User $kabid;

    private Sekolah $sekolahA;

    private Sekolah $sekolahB;

    protected function setUp(): void
    {
        parent::setUp();
        $this->kabid = User::factory()->create(['role' => 'kabid_ptk']);
        $this->sekolahA = Sekolah::create([
            'nama' => 'SMPN 1 Panyabungan', 'jenjang' => 'SMP', 'npsn' => '10200001',
            'lat' => 0.86, 'lng' => 99.55, 'rombel' => 6,
        ]);
        $this->sekolahB = Sekolah::create([
            'nama' => 'SDN 2 Panyabungan', 'jenjang' => 'SD', 'npsn' => '10200002',
            'lat' => 0.87, 'lng' => 99.56, 'rombel' => 6,
        ]);
    }

    private function muatanDasar(array $timpa = []): array
    {
        return array_merge([
            'nip' => '199001012020011001',
            'nama' => 'Guru Uji',
            'mapel' => 'Matematika',
            'jenjang' => 'SMP',
            'status_kepegawaian' => 'pppk',
            'domisili_alamat' => 'Jl. Uji No. 1',
            'domisili_lat' => '0.85',
            'domisili_lng' => '99.54',
            'sekolah_id' => $this->sekolahA->id,
        ], $timpa);
    }

    public function test_form_tidak_lagi_memuat_dua_select_yg_dihapus(): void
    {
        $res = $this->actingAs($this->kabid)->get('/guru/create');
        $res->assertOk();
        $html = $res->getContent();
        $this->assertStringNotContainsString('name="mapel_sertifikasi"', $html);
        $this->assertStringNotContainsString('name="sekolah_saat_ini"', $html);
        // Yang dipertahankan:
        $this->assertStringContainsString('name="bidang_studi_serdik"', $html);
        $this->assertStringContainsString('name="sekolah_id"', $html);
        $this->assertStringContainsString('<option value="TK"', $html);
        $this->assertStringNotContainsString('<option value="SMA"', $html);
    }

    public function test_crud_guru_tk_dan_label_sekolah_diturunkan_dari_relasi(): void
    {
        // CREATE jenjang TK + teks sekolah yang sengaja disalahkan:
        // label harus mengikuti nama sekolah relasi, bukan teks kiriman.
        $res = $this->actingAs($this->kabid)->post('/guru', $this->muatanDasar([
            'jenjang' => 'TK',
            'nama' => 'Guru TK Uji',
            'sekolah_id' => $this->sekolahB->id,
            'sekolah_saat_ini' => 'TEKS SALAH',
        ]));
        $res->assertSessionHasNoErrors();
        $guru = Guru::where('nama', 'Guru TK Uji')->firstOrFail();
        $this->assertSame('TK', $guru->jenjang);
        $this->assertSame('SDN 2 Panyabungan', $guru->sekolah_saat_ini);

        // READ: form ubah menampilkan jenjang TK sebagai opsi terpilih.
        $res = $this->actingAs($this->kabid)->get("/guru/{$guru->id}/edit");
        $res->assertOk();
        $this->assertStringContainsString('<option value="TK"', $res->getContent());

        // UPDATE pindah sekolah: label teks ikut sekolah baru, jenjang TK utuh.
        $res = $this->actingAs($this->kabid)->put("/guru/{$guru->id}", $this->muatanDasar([
            'jenjang' => 'TK',
            'nama' => 'Guru TK Uji',
            'sekolah_id' => $this->sekolahA->id,
        ]));
        $res->assertSessionHasNoErrors();
        $guru->refresh();
        $this->assertSame('TK', $guru->jenjang);
        $this->assertSame('SMPN 1 Panyabungan', $guru->sekolah_saat_ini);

        // DELETE
        $res = $this->actingAs($this->kabid)->delete("/guru/{$guru->id}");
        $res->assertRedirect();
        $this->assertDatabaseMissing('guru', ['id' => $guru->id]);
    }

    public function test_pendidikan_terakhir_panjang_tersimpan(): void
    {
        $panjang = 'S1 - Pendidikan Bahasa dan Sastra Indonesia'; // 43 karakter
        $this->assertGreaterThan(30, strlen($panjang));
        $res = $this->actingAs($this->kabid)->post('/guru', $this->muatanDasar([
            'pendidikan_terakhir' => $panjang,
        ]));
        $res->assertSessionHasNoErrors();
        $this->assertDatabaseHas('guru', ['pendidikan_terakhir' => $panjang]);
    }

    public function test_form_peran_guru_mengunci_kepegawaian_dan_nip(): void
    {
        $guru = Guru::create([
            'nama' => 'Guru Terkunci', 'mapel' => 'IPA', 'jenjang' => 'SMP',
            'domisili_alamat' => 'Jl. Terkunci', 'domisili_lat' => 0.85, 'domisili_lng' => 99.54,
            'sekolah_saat_ini' => '', 'sekolah_id' => $this->sekolahA->id,
            'nip' => '198801012015011001',
        ]);
        $akun = User::factory()->create(['role' => 'guru', 'guru_id' => $guru->id]);

        $res = $this->actingAs($akun)->get("/guru/{$guru->id}/edit");
        $res->assertOk();
        $res->assertSee('hanya dapat diubah oleh Kabid PTK atau Kepala Sekolah', false);
        $this->assertStringContainsString('disabled', $res->getContent());

        // Upaya mengubah mapel lewat PUT tetap tidak mengubah data (SEC-002),
        // dan label sekolah tetap selaras relasi.
        $res = $this->actingAs($akun)->put("/guru/{$guru->id}", [
            'nama' => 'Guru Terkunci',
            'mapel' => 'Fisika',
            'jenjang' => 'SMP',
            'domisili_alamat' => 'Jl. Terkunci Baru',
            'domisili_lat' => '0.86',
            'domisili_lng' => '99.55',
            'status_pernikahan' => 'kawin',
            'kecamatan' => 'Panyabungan',
            'desa_kelurahan' => 'Panyabungan',
        ]);
        $res->assertSessionHasNoErrors();
        $guru->refresh();
        $this->assertSame('IPA', $guru->mapel);
        $this->assertSame('Jl. Terkunci Baru', $guru->domisili_alamat);
        $this->assertSame('SMPN 1 Panyabungan', $guru->sekolah_saat_ini);
    }
}
