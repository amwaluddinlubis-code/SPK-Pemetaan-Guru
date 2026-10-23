<?php

// Tes Koordinat Wilayah: API titik tengah kecamatan/desa untuk peta
// formulir (sumber berurutan wilayah → sekolah → guru), perintah
// wilayah:hitung-koordinat, dan pengaman nama ambigu.

namespace Tests\Feature;

use App\Models\Sekolah;
use App\Models\User;
use App\Models\WilayahDesa;
use App\Models\WilayahKabupaten;
use App\Models\WilayahKecamatan;
use App\Models\WilayahProvinsi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class KoordinatWilayahTest extends TestCase
{
    use RefreshDatabase;

    private int $seq = 0;

    private function fixtures(): User
    {
        WilayahProvinsi::create(['kode' => '12', 'nama' => 'SUMATERA UTARA']);
        WilayahKabupaten::create(['kode' => '1213', 'provinsi_kode' => '12', 'nama' => 'KAB. MANDAILING NATAL']);
        WilayahKabupaten::create(['kode' => '1203', 'provinsi_kode' => '12', 'nama' => 'KAB. TAPANULI SELATAN']);
        WilayahKecamatan::create(['kode' => '121301', 'kabupaten_kode' => '1213', 'nama' => 'Panyabungan']);
        WilayahKecamatan::create(['kode' => '121309', 'kabupaten_kode' => '1213', 'nama' => 'Lembah Sorik Marapi']);
        WilayahDesa::create(['kode' => '1213012001', 'kecamatan_kode' => '121301', 'nama' => 'Panyabungan Jae']);

        return User::create([
            'name' => 'Kabid', 'email' => 'kabid@t.local',
            'password' => Hash::make('rahasia'), 'role' => 'kabid_ptk',
        ]);
    }

    private function buatSekolah(string $kecamatan, float $lat, float $lng, ?string $desa = null): Sekolah
    {
        return Sekolah::create([
            'npsn' => 'NPSN'.++$this->seq, 'nama' => 'SD '.$this->seq,
            'alamat' => 'Jl Tes', 'kecamatan' => $kecamatan,
            'desa_kelurahan' => $desa, 'lat' => $lat, 'lng' => $lng,
        ]);
    }

    public function test_kolom_koordinat_wilayah_ada(): void
    {
        $this->assertTrue(Schema::hasColumns('wilayah_kecamatan', ['lat', 'lng']));
        $this->assertTrue(Schema::hasColumns('wilayah_desa', ['lat', 'lng']));
    }

    public function test_api_koordinat_memakai_centroid_sekolah(): void
    {
        $kabid = $this->fixtures();
        $this->buatSekolah('Panyabungan', 1.0, 99.0);
        $this->buatSekolah('Panyabungan', 3.0, 101.0);

        $res = $this->actingAs($kabid)->getJson('/wilayah/api/koordinat?kecamatan=121301')
            ->assertOk()
            ->assertJsonPath('kecamatan.sumber', 'sekolah')
            ->assertJsonPath('kecamatan.jumlah', 2)
            ->assertJsonPath('desa', null);
        // JSON membuang ,0 pada bilangan bulat — bandingkan sebagai angka.
        $this->assertEqualsWithDelta(2.0, $res->json('kecamatan.lat'), 0.0000001);
        $this->assertEqualsWithDelta(100.0, $res->json('kecamatan.lng'), 0.0000001);
    }

    public function test_api_koordinat_kolom_wilayah_paling_utama(): void
    {
        $kabid = $this->fixtures();
        $this->buatSekolah('Panyabungan', 1.0, 99.0);
        WilayahKecamatan::where('kode', '121301')->update(['lat' => 0.5, 'lng' => 98.5]);

        $res = $this->actingAs($kabid)->getJson('/wilayah/api/koordinat?kecamatan=121301')
            ->assertOk()
            ->assertJsonPath('kecamatan.sumber', 'wilayah');
        $this->assertEqualsWithDelta(0.5, $res->json('kecamatan.lat'), 0.0000001);
    }

    public function test_api_koordinat_desa_dan_varian_ejaan(): void
    {
        $kabid = $this->fixtures();
        // Sekolah memakai ejaan Dapodik "Merapi", referensi "Marapi".
        $this->buatSekolah('Lembah Sorik Merapi', 0.9, 99.9, null);
        $this->buatSekolah('Panyabungan', 1.0, 99.0, 'Panyabungan Jae');
        $this->buatSekolah('Panyabungan', 1.4, 99.4, 'Panyabungan Jae');

        $this->actingAs($kabid)->getJson('/wilayah/api/koordinat?kecamatan=121309&desa=1213012001')
            ->assertOk()
            ->assertJsonPath('kecamatan.lat', 0.9)
            ->assertJsonPath('desa.lat', 1.2)
            ->assertJsonPath('desa.lng', 99.2)
            ->assertJsonPath('desa.sumber', 'sekolah');
    }

    public function test_perintah_hitung_koordinat_mengisi_kolom(): void
    {
        $this->fixtures();
        $this->buatSekolah('Panyabungan', 1.0, 99.0, 'Panyabungan Jae');
        $this->buatSekolah('Panyabungan', 3.0, 101.0, 'Panyabungan Jae');

        $this->artisan('wilayah:hitung-koordinat')->assertSuccessful();

        $kec = WilayahKecamatan::find('121301');
        $this->assertEqualsWithDelta(2.0, $kec->lat, 0.0000001);
        $this->assertEqualsWithDelta(100.0, $kec->lng, 0.0000001);
        $desa = WilayahDesa::find('1213012001');
        $this->assertEqualsWithDelta(2.0, $desa->lat, 0.0000001);
    }

    public function test_nama_kecamatan_ambigu_dilewati_perintah(): void
    {
        $this->fixtures();
        WilayahKecamatan::create(['kode' => '121399', 'kabupaten_kode' => '1213', 'nama' => 'Kota']);
        WilayahKecamatan::create(['kode' => '120399', 'kabupaten_kode' => '1203', 'nama' => 'Kota']);
        $this->buatSekolah('Kota', 1.0, 99.0);

        $this->artisan('wilayah:hitung-koordinat')->assertSuccessful();

        $this->assertNull(WilayahKecamatan::find('121399')->lat);
        $this->assertNull(WilayahKecamatan::find('120399')->lat);
    }

    public function test_api_koordinat_butuh_login(): void
    {
        $this->fixtures();
        $this->get('/wilayah/api/koordinat?kecamatan=121301')->assertRedirect('/login');
    }
}
