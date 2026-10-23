<?php

// Tes select terpencarian (x-searchable-select & x-desa-select): combobox
// tampil menggantikan <select> biasa di filter wilayah dan form, dan peta
// kode kecamatan tersambung untuk memuat daftar desa dari API wilayah.

namespace Tests\Feature;

use App\Models\User;
use App\Models\WilayahKabupaten;
use App\Models\WilayahKecamatan;
use App\Models\WilayahProvinsi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SearchableSelectTest extends TestCase
{
    use RefreshDatabase;

    private function fixtures(): array
    {
        WilayahProvinsi::create(['kode' => '12', 'nama' => 'SUMATERA UTARA']);
        WilayahKabupaten::create(['kode' => '1213', 'provinsi_kode' => '12', 'nama' => 'KAB. MANDAILING NATAL']);
        WilayahKecamatan::create(['kode' => '121301', 'kabupaten_kode' => '1213', 'nama' => 'PANYABUNGAN']);
        $kabid = User::create([
            'name' => 'Kabid', 'email' => 'kabid@t.local',
            'password' => Hash::make('rahasia'), 'role' => 'kabid_ptk',
        ]);

        return compact('kabid');
    }

    public function test_filter_wilayah_memakai_combobox_pencarian(): void
    {
        $f = $this->fixtures();

        $res = $this->actingAs($f['kabid'])->get('/wilayah/desa');
        $res->assertOk();
        $res->assertSee('role="combobox"', false);
        $res->assertSee('wire:model="provinsi"', false);
        $res->assertSee('wire:model="kecamatan"', false);
    }

    public function test_form_guru_memakai_combobox_sekolah_dan_desa_api(): void
    {
        $f = $this->fixtures();

        $res = $this->actingAs($f['kabid'])->get('/guru/create');
        $res->assertOk();
        $res->assertSee('role="combobox"', false);
        $res->assertSee('name="sekolah_id"', false);
        $res->assertSee('name="desa_kelurahan"', false);
        $res->assertSee('muatDesa', false);
        // Peta nama => kode kecamatan Madina ikut terkirim untuk API desa.
        $res->assertSee('121301', false);
    }

    public function test_form_sekolah_memakai_combobox_dan_desa_api(): void
    {
        $f = $this->fixtures();

        $res = $this->actingAs($f['kabid'])->get('/sekolah/create');
        $res->assertOk();
        $res->assertSee('role="combobox"', false);
        $res->assertSee('name="kecamatan"', false);
        $res->assertSee('name="desa_kelurahan"', false);
        $res->assertSee('muatDesa', false);
        $res->assertSee('121301', false);
    }
}
