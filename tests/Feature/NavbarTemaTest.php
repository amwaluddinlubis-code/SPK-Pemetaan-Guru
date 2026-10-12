<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NavbarTemaTest extends TestCase
{
    use RefreshDatabase;

    private function loginKabid(): User
    {
        $user = User::factory()->create(['role' => 'kabid_ptk']);
        $this->actingAs($user);

        return $user;
    }

    public function test_navbar_utama_sticky_di_semua_viewport(): void
    {
        $this->loginKabid();

        $res = $this->get('/');
        $res->assertOk();
        // Navbar punya class sticky top-0 dan testid, tanpa lg:hidden
        $res->assertSee('data-testid="navbar-utama"', false);
        $res->assertSee('sticky top-0 z-30', false);
        $this->assertStringNotContainsString('lg:hidden sticky', $res->getContent());
    }

    public function test_toggle_tema_tampil_di_navbar(): void
    {
        $this->loginKabid();

        $res = $this->get('/');
        $res->assertOk();
        $res->assertSee('data-testid="toggle-tema"', false);
    }

    public function test_html_punya_data_theme_default(): void
    {
        $this->loginKabid();

        $res = $this->get('/');
        $res->assertOk();
        // Default light, JS akan override dari localStorage / system
        $res->assertSee('<html lang="id" data-theme="light"', false);
    }

    public function test_guru_index_punya_tampilan_kartu_mobile(): void
    {
        $this->loginKabid();

        $res = $this->get('/guru');
        $res->assertOk();
        // Kartu mobile + tabel desktop keduanya ada (responsive)
        $res->assertSee('data-testid="kartu-guru-mobile"', false);
        $res->assertSee('data-testid="tabel-guru-desktop"', false);
    }

    public function test_sidebar_pakai_ikon_svg_bukan_emoji(): void
    {
        $this->loginKabid();

        $res = $this->get('/');
        $res->assertOk();
        // Ikon SVG ter-render (bukan tag mentah), bukan emoji di menu
        $res->assertSee('<svg xmlns="http://www.w3.org/2000/svg"', false);
        $this->assertStringNotContainsString('🗺️ Dashboard', $res->getContent());
    }

    public function test_sidebar_dikelompokkan_per_seksi(): void
    {
        $this->loginKabid();

        $res = $this->get('/');
        $res->assertOk();
        $res->assertSee('data-testid="menu-sidebar"', false);
        foreach (['Data Master', 'Proses Penempatan', 'Analisis', 'Sistem'] as $seksi) {
            $res->assertSee($seksi);
        }
    }

    public function test_dashboard_eksekutif_tonjolkan_menunggu_keputusan(): void
    {
        $pemda = User::factory()->create(['role' => 'pemda']);
        $this->actingAs($pemda);

        $res = $this->get('/pemda');
        $res->assertOk();
        $res->assertSee('data-testid="ringkasan-eksekutif"', false);
        // Metrik menunggu keputusan ditonjolkan visual
        $res->assertSee('border-error', false);
    }

    public function test_form_guru_berseksi(): void
    {
        $this->loginKabid();

        $res = $this->get('/guru/create');
        $res->assertOk();
        $res->assertSee('data-testid="form-guru-berseksi"', false);
        foreach (['Data Pribadi', 'Kepegawaian', 'Domisili'] as $seksi) {
            $res->assertSee($seksi);
        }
    }

    public function test_aksesibilitas_skip_link_dan_main_id(): void
    {
        $this->loginKabid();

        $res = $this->get('/');
        $res->assertOk();
        $res->assertSee('data-testid="skip-link"', false);
        $res->assertSee('id="konten-utama"', false);
    }
}
