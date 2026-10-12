<?php

// Tes modul Wilayah (aturan proyek): render + filter berjenjang + scope +
// API dropdown. Data referensi read-only Kepmendagri 100.1.1-6117/2022.

namespace Tests\Feature;

use App\Livewire\TabelDesa;
use App\Livewire\TabelKabupaten;
use App\Livewire\TabelKecamatan;
use App\Livewire\TabelProvinsi;
use App\Models\User;
use App\Models\WilayahDesa;
use App\Models\WilayahKabupaten;
use App\Models\WilayahKecamatan;
use App\Models\WilayahProvinsi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class WilayahTest extends TestCase
{
    use RefreshDatabase;

    private function fixtures(): array
    {
        $prov = WilayahProvinsi::create(['kode' => '12', 'nama' => 'SUMATERA UTARA']);
        $madina = WilayahKabupaten::create(['kode' => '1213', 'provinsi_kode' => '12', 'nama' => 'KAB. MANDAILING NATAL']);
        $tapsel = WilayahKabupaten::create(['kode' => '1203', 'provinsi_kode' => '12', 'nama' => 'KAB. TAPANULI SELATAN']);
        $panyabungan = WilayahKecamatan::create(['kode' => '121301', 'kabupaten_kode' => '1213', 'nama' => 'Panyabungan']);
        WilayahDesa::create(['kode' => '1213012001', 'kecamatan_kode' => '121301', 'nama' => 'Desa Uji']);
        $kabid = User::create(['name' => 'Kabid', 'email' => 'kabid@t.local',
            'password' => Hash::make('rahasia'), 'role' => 'kabid_ptk']);
        $guru = User::create(['name' => 'Guru', 'email' => 'guru@t.local',
            'password' => Hash::make('rahasia'), 'role' => 'guru']);

        return compact('prov', 'madina', 'tapsel', 'panyabungan', 'kabid', 'guru');
    }

    public function test_halaman_wilayah_bisa_diakses_semua_peran(): void
    {
        $f = $this->fixtures();

        foreach (['/wilayah/provinsi', '/wilayah/kabupaten', '/wilayah/kecamatan', '/wilayah/desa'] as $url) {
            $this->actingAs($f['kabid'])->get($url)->assertOk();
            $this->actingAs($f['guru'])->get($url)->assertOk();
        }
        $this->actingAs($f['kabid'])->get('/wilayah/provinsi')->assertSee('SUMATERA UTARA');
    }

    public function test_tabel_berjenjang_dan_relasinya(): void
    {
        $f = $this->fixtures();

        $this->assertSame('SUMATERA UTARA', $f['madina']->provinsi->nama);
        $this->assertSame('KAB. MANDAILING NATAL', $f['panyabungan']->kabupaten->nama);
        $this->assertSame('Panyabungan', WilayahDesa::first()->kecamatan->nama);

        // Filter provinsi pada tabel kabupaten
        Livewire::actingAs($f['kabid'])->test(TabelKabupaten::class)
            ->set('provinsi', '12')
            ->assertSee('MANDAILING NATAL')
            ->set('q', 'TAPANULI')
            ->assertSee('TAPANULI SELATAN')
            ->assertDontSee('MANDAILING NATAL');

        // Kaskade kecamatan: ganti provinsi me-reset kabupaten
        Livewire::actingAs($f['kabid'])->test(TabelKecamatan::class)
            ->set('provinsi', '12')
            ->set('kabupaten', '1213')
            ->assertSee('Panyabungan')
            ->set('provinsi', '')
            ->assertSet('kabupaten', '');

        // Desa: wajib pilih kecamatan dulu
        Livewire::actingAs($f['kabid'])->test(TabelDesa::class)
            ->assertSee('Pilih provinsi', false)
            ->assertDontSee('Desa Uji')
            ->set('provinsi', '12')
            ->set('kabupaten', '1213')
            ->set('kecamatan', '121301')
            ->assertSee('Desa Uji')
            ->set('q', 'Tidak Ada')
            ->assertDontSee('Desa Uji');

        Livewire::actingAs($f['kabid'])->test(TabelProvinsi::class)
            ->set('q', 'SUMATERA UTARA')
            ->assertSee('SUMATERA UTARA');
    }

    public function test_api_dropdown_berjenjang(): void
    {
        $f = $this->fixtures();

        $this->actingAs($f['kabid'])->getJson('/wilayah/api/provinsi')
            ->assertOk()->assertJsonFragment(['kode' => '12']);

        $this->actingAs($f['kabid'])->getJson('/wilayah/api/kabupaten?provinsi=12')
            ->assertOk()->assertJsonFragment(['kode' => '1213'])->assertJsonCount(2);

        $this->actingAs($f['kabid'])->getJson('/wilayah/api/kecamatan?kabupaten=1213')
            ->assertOk()->assertJsonFragment(['kode' => '121301']);

        $this->actingAs($f['kabid'])->getJson('/wilayah/api/desa?kecamatan=121301')
            ->assertOk()->assertJsonFragment(['kode' => '1213012001']);

        // Tanpa parameter / induk tak dikenal: array kosong, tetap 200
        $this->actingAs($f['kabid'])->getJson('/wilayah/api/kabupaten')
            ->assertOk()->assertExactJson([]);
        $this->actingAs($f['kabid'])->getJson('/wilayah/api/desa?kecamatan=9999999')
            ->assertOk()->assertExactJson([]);
    }

    public function test_tamu_ditolak(): void
    {
        $this->getJson('/wilayah/api/provinsi')->assertUnauthorized();
        $this->get('/wilayah/provinsi')->assertRedirect('/login');
    }

    public function test_berkas_csv_sumber_tersedia(): void
    {
        foreach (['provinces.csv', 'regencies.csv', 'districts.csv', 'villages.csv'] as $berkas) {
            $path = database_path('seeders/data/wilayah/'.$berkas);
            $this->assertFileExists($path);
        }
        // Header sesuai kontrak parser seeder (delimiter ';')
        $this->assertSame('id;name', trim(file(database_path('seeders/data/wilayah/provinces.csv'))[0]));
    }
}
