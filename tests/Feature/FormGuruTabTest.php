<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// Form guru 3 tab (Data Pribadi | Kepegawaian | Domisili & Penempatan):
// tab ter-render, dan tab aktif otomatis pindah ke tab yang punya error validasi.
class FormGuruTabTest extends TestCase
{
    use RefreshDatabase;

    private function loginKabid(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'kabid_ptk']));
    }

    public function test_tiga_tab_terrender(): void
    {
        $this->loginKabid();

        $res = $this->get('/guru/create');
        $res->assertOk();
        $res->assertSee('data-testid="form-guru-tab"', false);
        foreach (['1. Data Pribadi', '2. Kepegawaian', '3. Domisili'] as $tab) {
            $res->assertSee($tab);
        }
        // Panel Alpine untuk tiap tab
        foreach (["tab === 'pribadi'", "tab === 'kepegawaian'", "tab === 'domisili'"] as $panel) {
            $res->assertSee($panel, false);
        }
    }

    public function test_tab_aktif_pindah_ke_tab_berror(): void
    {
        $this->loginKabid();

        // Kunjungi form dulu agar redirect back() kembali ke sini
        $this->get('/guru/create');

        // Submit dengan semua field valid KECUALI mapel (wajib, di tab Kepegawaian),
        // ikuti redirect agar error tampil di form
        $res = $this->followingRedirects()->post('/guru', [
            'nip' => '198501012010011001',
            'nama' => 'Siti Aminah',
            'mapel' => '',
            'jenjang' => 'SD',
            'domisili_alamat' => 'Jl. Mawar No. 10',
            'domisili_lat' => '0.79',
            'domisili_lng' => '99.41',
            'sekolah_saat_ini' => 'SDN 1 Panyabungan',
        ]);

        $res->assertOk();
        // Tab Kepegawaian yang aktif karena error mapel ada di sana
        $this->assertStringContainsString("tab: 'kepegawaian'", $res->getContent());
    }

    public function test_semua_field_tetap_ada_di_tab(): void
    {
        $this->loginKabid();

        $res = $this->get('/guru/create');
        $html = $res->getContent();
        foreach (['nip', 'nama', 'status_pernikahan', 'mapel', 'jenjang', 'kecamatan',
            'domisili_alamat', 'domisili_lat', 'sekolah_id'] as $field) {
            $this->assertStringContainsString('name="'.$field.'"', $html);
        }
    }
}
