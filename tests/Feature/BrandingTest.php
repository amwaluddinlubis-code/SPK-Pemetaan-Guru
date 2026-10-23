<?php

// Tes Branding (Redesign R0): layanan warna/logo, penyimpanan lewat
// Pengaturan Pemda (khusus kabid_ptk), dan penyuntikan tema ke layout.

namespace Tests\Feature;

use App\Livewire\PengaturanPemdaLivewire;
use App\Models\Pengaturan;
use App\Models\User;
use App\Services\Branding;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class BrandingTest extends TestCase
{
    use RefreshDatabase;

    private function kabid(): User
    {
        return User::create([
            'name' => 'Kabid', 'email' => 'kabid@t.local',
            'password' => Hash::make('rahasia'), 'role' => 'kabid_ptk',
        ]);
    }

    public function test_warna_bawaan_dan_gaya_tema(): void
    {
        $this->assertSame(Branding::WARNA_BAWAAN, Branding::warna());
        $this->assertNull(Branding::logoUrl());

        $gaya = Branding::gayaTema();
        $this->assertStringContainsString('--color-primary:'.Branding::WARNA_BAWAAN, $gaya);
        $this->assertStringContainsString('[data-theme="dark"]', $gaya);
    }

    public function test_kontras_dan_ubah_terang(): void
    {
        // Warna gelap → teks putih; warna terang → teks gelap.
        $this->assertSame('#ffffff', Branding::kontras('#1e3a8a'));
        $this->assertSame('#1f2937', Branding::kontras('#facc15'));

        // Menerangkan menuju putih & menggelapkan menuju hitam.
        $this->assertSame('#ffffff', Branding::ubahTerang('#000000', 100));
        $this->assertSame('#000000', Branding::ubahTerang('#ffffff', -100));
        $this->assertSame('#808080', Branding::ubahTerang('#000000', 50));
    }

    public function test_kabid_menyimpan_warna_valid(): void
    {
        $this->actingAs($this->kabid());

        Livewire::test(PengaturanPemdaLivewire::class)
            ->set('warnaAksen', '#0F766E')
            ->call('simpanBranding')
            ->assertHasNoErrors()
            ->assertRedirect(route('pengaturan.pemda'));

        $this->assertSame('#0f766e', Pengaturan::ambil(Branding::KUNCI_WARNA));
        $this->assertSame('#0f766e', Branding::warna());
    }

    public function test_warna_tidak_valid_ditolak(): void
    {
        $this->actingAs($this->kabid());

        Livewire::test(PengaturanPemdaLivewire::class)
            ->set('warnaAksen', 'biru')
            ->call('simpanBranding')
            ->assertHasErrors('warnaAksen');

        $this->assertSame('', (string) Pengaturan::ambil(Branding::KUNCI_WARNA, ''));
    }

    public function test_unggah_dan_hapus_logo(): void
    {
        Storage::fake('public');
        $this->actingAs($this->kabid());

        Livewire::test(PengaturanPemdaLivewire::class)
            ->set('logoBaru', UploadedFile::fake()->image('logo.png', 64, 64))
            ->call('simpanBranding')
            ->assertHasNoErrors();

        $path = Branding::logoPath();
        $this->assertNotNull($path);
        Storage::disk('public')->assertExists($path);
        $this->assertNotNull(Branding::logoUrl());

        Livewire::test(PengaturanPemdaLivewire::class)->call('hapusLogo');
        $this->assertNull(Branding::logoPath());
        Storage::disk('public')->assertMissing($path);
    }

    public function test_peran_lain_ditolak_menyimpan_branding(): void
    {
        $guru = User::create([
            'name' => 'Guru', 'email' => 'guru@t.local',
            'password' => Hash::make('rahasia'), 'role' => 'guru',
        ]);
        $this->actingAs($guru);

        Livewire::test(PengaturanPemdaLivewire::class)
            ->set('warnaAksen', '#123456')
            ->call('simpanBranding')
            ->assertForbidden();

        $this->assertSame('', (string) Pengaturan::ambil(Branding::KUNCI_WARNA, ''));
    }

    public function test_layout_menyuntikkan_warna_aksen_dan_logo(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('branding/logo.png', 'isi');
        Pengaturan::simpan(Branding::KUNCI_WARNA, '#0f766e');
        Pengaturan::simpan(Branding::KUNCI_LOGO, 'branding/logo.png');

        $respon = $this->actingAs($this->kabid())->get(route('dashboard'));
        $respon->assertOk();
        $respon->assertSee('--color-primary:#0f766e', false);
        $respon->assertSee('branding/logo.png', false);
    }
}
