<?php

// Tes komponen Livewire TabelDistribusi: render, filter kecamatan/mapel,
// pagination, sortir, dan reset filter (aturan proyek: setiap tabel Livewire
// wajib diuji render + filter).

namespace Tests\Feature;

use App\Livewire\TabelDistribusi;
use App\Models\Guru;
use App\Models\KebutuhanGuru;
use App\Models\Sekolah;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TabelDistribusiTest extends TestCase
{
    use RefreshDatabase;

    private int $seq = 2200;

    private function buatSekolah(string $kecamatan): Sekolah
    {
        return Sekolah::create([
            'npsn' => '2'.++$this->seq, 'nama' => 'S'.$this->seq, 'alamat' => 'A',
            'kecamatan' => $kecamatan, 'lat' => 1.0, 'lng' => 100.0,
        ]);
    }

    private function buatGuru(Sekolah $sekolah, string $mapel): Guru
    {
        return Guru::create([
            'nip' => 'NIP'.++$this->seq, 'nama' => 'Guru '.$this->seq,
            'mapel' => $mapel, 'jenjang' => 'SD',
            'domisili_alamat' => 'D', 'kecamatan' => $sekolah->kecamatan,
            'sekolah_id' => $sekolah->id, 'sekolah_saat_ini' => $sekolah->nama,
            'domisili_lat' => 1.0, 'domisili_lng' => 100.0,
        ]);
    }

    private function isiDuaKecamatan(): void
    {
        $s1 = $this->buatSekolah('Panyabungan');
        $s2 = $this->buatSekolah('Siabu');
        $this->buatGuru($s1, 'Matematika');
        KebutuhanGuru::create(['sekolah_id' => $s1->id, 'mapel' => 'Matematika', 'jumlah' => 5]);
        $this->buatGuru($s2, 'IPA');
        KebutuhanGuru::create(['sekolah_id' => $s2->id, 'mapel' => 'IPA', 'jumlah' => 2]);
    }

    public function test_render_awal_memuat_agregat(): void
    {
        $this->isiDuaKecamatan();

        Livewire::test(TabelDistribusi::class)
            ->assertSeeHtml('<td>Panyabungan</td>')
            ->assertSeeHtml('<td>Siabu</td>')
            ->assertSee('Matematika')
            ->assertSee('IPA');
    }

    public function test_filter_kecamatan_dan_mapel_memersempit(): void
    {
        $this->isiDuaKecamatan();

        Livewire::test(TabelDistribusi::class)
            ->set('kecamatan', 'Siabu')
            ->assertSeeHtml('<td>Siabu</td>')
            ->assertDontSeeHtml('<td>Panyabungan</td>')
            ->set('kecamatan', '')
            ->set('mapel', 'Matematika')
            ->assertSeeHtml('<td>Panyabungan</td>')
            ->assertDontSeeHtml('<td>Siabu</td>');
    }

    public function test_pagination_membagi_baris(): void
    {
        // 11 kelompok kecamatan x mapel, perPage terkecil yang sah = 10.
        // Nama memakai huruf agar urutan leksikal sort cocok dengan harapan uji.
        for ($i = 1; $i <= 11; $i++) {
            $s = $this->buatSekolah('Kecamatan '.chr(64 + $i));
            $this->buatGuru($s, 'Matematika');
        }

        Livewire::test(TabelDistribusi::class)
            ->set('perPage', 10)
            ->assertSeeHtml('<td>Kecamatan A</td>')
            ->assertDontSeeHtml('<td>Kecamatan K</td>')
            ->call('gotoPage', 2)
            ->assertSeeHtml('<td>Kecamatan K</td>')
            ->assertDontSeeHtml('<td>Kecamatan A</td>');
    }

    public function test_sortir_dan_reset_filter(): void
    {
        $this->isiDuaKecamatan();

        Livewire::test(TabelDistribusi::class)
            ->call('urutkan', 'defisit')
            ->assertSet('sort', 'defisit')
            ->assertSet('arah', 'asc')
            ->call('urutkan', 'defisit')
            ->assertSet('arah', 'desc')
            ->call('urutkan', 'kolom-tidak-sah')
            ->assertSet('sort', 'defisit')
            ->set('kecamatan', 'Siabu')
            ->call('resetFilter')
            ->assertSet('kecamatan', '')
            ->assertSet('sort', 'kecamatan');
    }
}
