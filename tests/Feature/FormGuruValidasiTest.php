<?php

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\Sekolah;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// Validasi lanjutan form guru (sisa analisis P2/P3, 24 Okt 2026):
// NIP di-trim sebelum cek unik, koordinat (0,0) ditolak, tanggal lahir
// tidak boleh masa depan, bidang serdik harus dikenal Kepmen 222, dan
// pesan kesalahan tampil dalam Bahasa Indonesia.
class FormGuruValidasiTest extends TestCase
{
    use RefreshDatabase;

    private User $kabid;

    protected function setUp(): void
    {
        parent::setUp();
        $this->kabid = User::factory()->create(['role' => 'kabid_ptk']);
        Sekolah::create([
            'nama' => 'SMPN 1 Panyabungan', 'jenjang' => 'SMP', 'npsn' => '10200001',
            'lat' => 0.86, 'lng' => 99.55, 'rombel' => 6,
        ]);
    }

    private function muatanDasar(array $timpa = []): array
    {
        return array_merge([
            'nip' => '199001012020011001',
            'nama' => 'Guru Uji',
            'mapel' => 'Matematika',
            'jenjang' => 'SMP',
            'domisili_alamat' => 'Jl. Uji No. 1',
            'domisili_lat' => '0.85',
            'domisili_lng' => '99.54',
        ], $timpa);
    }

    public function test_nip_ber_spasi_tertangkap_unik_bukan_error_basis_data(): void
    {
        $this->actingAs($this->kabid)->post('/guru', $this->muatanDasar())
            ->assertSessionHasNoErrors();
        $this->assertSame(1, Guru::count());

        // NIP yang sama dengan spasi di sekeliling: harus menjadi error
        // validasi pada field nip, bukan QueryException indeks unik.
        $res = $this->actingAs($this->kabid)->post('/guru', $this->muatanDasar([
            'nip' => '  199001012020011001  ',
            'nama' => 'Guru Duplikat',
        ]));
        $res->assertSessionHasErrors('nip');
        $this->assertSame(1, Guru::count());
    }

    public function test_koordinat_nol_nol_ditolak(): void
    {
        $res = $this->actingAs($this->kabid)->post('/guru', $this->muatanDasar([
            'domisili_lat' => '0',
            'domisili_lng' => '0',
        ]));
        $res->assertSessionHasErrors('domisili_lat');
        $this->assertSame(0, Guru::count());
    }

    public function test_tanggal_lahir_masa_depan_ditolak(): void
    {
        $res = $this->actingAs($this->kabid)->post('/guru', $this->muatanDasar([
            'tanggal_lahir' => now()->addYear()->format('Y-m-d'),
        ]));
        $res->assertSessionHasErrors('tanggal_lahir');
        $this->assertSame(0, Guru::count());
    }

    public function test_bidang_serdik_di_luar_daftar_kepmen_ditolak(): void
    {
        $res = $this->actingAs($this->kabid)->post('/guru', $this->muatanDasar([
            'sudah_sertifikasi' => '1',
            'bidang_studi_serdik' => 'Bidang Karangan Bebas XYZ',
        ]));
        $res->assertSessionHasErrors('bidang_studi_serdik');
        $this->assertSame(0, Guru::count());

        // Bidang yang dikenal Kepmen lolos dan kodenya terisi.
        $res = $this->actingAs($this->kabid)->post('/guru', $this->muatanDasar([
            'sudah_sertifikasi' => '1',
            'bidang_studi_serdik' => 'Matematika',
        ]));
        $res->assertSessionHasNoErrors();
        $this->assertNotNull(Guru::first()?->kode_bidang_studi);
    }

    public function test_pesan_validasi_bahasa_indonesia(): void
    {
        $res = $this->actingAs($this->kabid)->post('/guru', $this->muatanDasar([
            'nama' => '',
        ]));
        $res->assertSessionHasErrors('nama');
        $pesan = session('errors')->first('nama');
        $this->assertSame('Nama wajib diisi.', $pesan);
    }
}
