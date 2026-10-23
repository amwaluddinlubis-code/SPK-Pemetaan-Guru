<?php

// Tes UI konteks Pemda (Paket 1): lencana konteks di topbar, chip cakupan
// di halaman daftar, spanduk "belum berkonteks" khusus admin, dan pesan
// keadaan kosong yang menjelaskan konteks pada tabel Livewire.

namespace Tests\Feature;

use App\Livewire\TabelSekolah;
use App\Models\Sekolah;
use App\Models\User;
use App\Models\WilayahKabupaten;
use App\Models\WilayahKecamatan;
use App\Models\WilayahProvinsi;
use App\Services\KonteksPemda;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class KonteksUiTest extends TestCase
{
    use RefreshDatabase;

    private function fixtures(): array
    {
        WilayahProvinsi::create(['kode' => '12', 'nama' => 'SUMATERA UTARA']);
        WilayahKabupaten::create(['kode' => '1213', 'provinsi_kode' => '12', 'nama' => 'KAB. MANDAILING NATAL']);
        WilayahKabupaten::create(['kode' => '1203', 'provinsi_kode' => '12', 'nama' => 'KAB. TAPANULI SELATAN']);
        WilayahKecamatan::create(['kode' => '121301', 'kabupaten_kode' => '1213', 'nama' => 'Panyabungan']);
        WilayahKecamatan::create(['kode' => '120301', 'kabupaten_kode' => '1203', 'nama' => 'Sipirok']);

        $kabid = User::create([
            'name' => 'Kabid', 'email' => 'kabid@t.local',
            'password' => Hash::make('rahasia'), 'role' => 'kabid_ptk',
        ]);
        $guruUser = User::create([
            'name' => 'Guru', 'email' => 'guru@t.local',
            'password' => Hash::make('rahasia'), 'role' => 'guru',
        ]);

        return compact('kabid', 'guruUser');
    }

    private function buatSekolah(string $nama, string $kecamatan): Sekolah
    {
        return Sekolah::create([
            'npsn' => 'NPSN'.$nama, 'nama' => $nama,
            'alamat' => 'Jl Tes', 'kecamatan' => $kecamatan,
            'lat' => 0.86, 'lng' => 99.56,
        ]);
    }

    public function test_lencana_dan_chip_konteks_tampil_saat_konteks_aktif(): void
    {
        $f = $this->fixtures();
        $this->buatSekolah('SD A', 'Panyabungan');
        KonteksPemda::simpan('kabupaten', '12', '1213');

        $this->assertSame('Kab. Mandailing Natal', KonteksPemda::namaWilayah());

        $res = $this->actingAs($f['kabid'])->get('/sekolah');
        $res->assertOk();
        $res->assertSee('Kab. Mandailing Natal');            // lencana topbar + chip
        $res->assertSee('Konteks Pemerintah Kabupaten Mandailing Natal');
        $res->assertSee('chip-konteks', false);
        $res->assertDontSee('belum berkonteks');
    }

    public function test_spanduk_belum_berkonteks_hanya_untuk_admin(): void
    {
        $f = $this->fixtures();

        $this->actingAs($f['kabid'])->get('/sekolah')->assertSee('belum berkonteks');
        $this->actingAs($f['guruUser'])->get('/sekolah')->assertDontSee('belum berkonteks');
    }

    public function test_keadaan_kosong_menjelaskan_konteks(): void
    {
        $f = $this->fixtures();
        // Satu-satunya sekolah berada di luar konteks Madina.
        $this->buatSekolah('SD C', 'Sipirok');
        KonteksPemda::simpan('kabupaten', '12', '1213');

        Livewire::actingAs($f['kabid'])->test(TabelSekolah::class)
            ->assertSee('Belum ada data sekolah di wilayah Pemerintah Kabupaten Mandailing Natal');
    }
}
