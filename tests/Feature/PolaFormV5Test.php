<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

// Migrasi form ke pola daisyUI v5: fieldset + fieldset-legend,
// tanpa kelas v4 yang sudah dihapus (form-control, label-text di form,
// input-bordered, select-bordered, textarea-bordered, file-input-bordered).
class PolaFormV5Test extends TestCase
{
    use RefreshDatabase;

    private function loginKabid(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'kabid_ptk']));
    }

    // Halaman dengan form memakai pola v5 dan bebas kelas v4
    public static function halamanBerform(): array
    {
        return [
            'login' => ['/login'],
            'form guru' => ['/guru/create'],
            'form sekolah' => ['/sekolah/create'],
            'form pengguna' => ['/pengguna/create'],
            'form usulan' => ['/usulan/create'],
            'profil' => ['/profil'],
        ];
    }

    #[DataProvider('halamanBerform')]
    public function test_form_memakai_pola_v5(string $url): void
    {
        if ($url !== '/login') {
            $this->loginKabid();
        }

        $res = $this->get($url);
        $res->assertOk();

        $html = $res->getContent();
        // Pola v5 hadir
        $this->assertStringContainsString('<fieldset', $html);
        $this->assertStringContainsString('fieldset-legend', $html);
        // Kelas v4 yang dihapus di v5 tidak boleh ada
        foreach (['form-control', 'input-bordered', 'select-bordered', 'textarea-bordered', 'file-input-bordered'] as $kelas) {
            $this->assertStringNotContainsString($kelas, $html, "Kelas v4 '$kelas' masih dipakai di $url");
        }
    }

    public function test_label_text_hanya_untuk_checkbox_v5(): void
    {
        // label-text masih valid di v5 hanya di dalam <label class="label"> (checkbox)
        $this->loginKabid();
        $res = $this->get('/guru/create');
        $res->assertOk();

        $html = $res->getContent();
        // Tidak ada lagi span label-text di dalam fieldset (sudah jadi legend)
        $this->assertDoesNotMatchRegularExpression(
            '/<fieldset[^>]*>.*?<span[^>]*label-text/s',
            $html
        );
    }
}
