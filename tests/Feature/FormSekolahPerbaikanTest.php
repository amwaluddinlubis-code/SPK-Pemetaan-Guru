<?php

namespace Tests\Feature;

use App\Models\KebutuhanGuru;
use App\Models\Sekolah;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// Perbaikan bug P0 form bersarang (24 Okt 2026): formulir "Tandai
// Terverifikasi" pernah diselipkan di dalam form utama Ubah Sekolah,
// sehingga tombol Simpan mati dan seksi Kebutuhan Guru jatuh di luar
// form — setiap ubah yang berhasil menghapus kebutuhan guru sekolah
// itu secara diam-diam. Perbaikan: form verifikasi dipindah ke luar
// form utama, controller hanya menyinkronkan kebutuhan bila seksi
// kebutuhan benar-benar terkirim, dan tes ini mengunci ketiganya.
class FormSekolahPerbaikanTest extends TestCase
{
    use RefreshDatabase;

    private User $kabid;

    private Sekolah $sekolah;

    protected function setUp(): void
    {
        parent::setUp();
        $this->kabid = User::factory()->create(['role' => 'kabid_ptk']);
        $this->sekolah = Sekolah::create([
            'nama' => 'SDN 1 Uji', 'jenjang' => 'SD', 'npsn' => '10200999',
            'alamat' => 'Jl. Uji No. 1', 'kecamatan' => 'Panyabungan',
            'kategori_daerah' => 'biasa', 'lat' => 0.86, 'lng' => 99.55,
            'rombel' => 6,
        ]);
        KebutuhanGuru::create(['sekolah_id' => $this->sekolah->id, 'mapel' => 'Matematika', 'jumlah' => 1]);
        KebutuhanGuru::create(['sekolah_id' => $this->sekolah->id, 'mapel' => 'IPA', 'jumlah' => 2]);
    }

    private function muatanDasar(array $timpa = []): array
    {
        return array_merge([
            'npsn' => '10200999',
            'nama' => 'SDN 1 Uji',
            'jenjang' => 'SD',
            'rombel' => 6,
            'alamat' => 'Jl. Uji No. 1',
            'kecamatan' => 'Panyabungan',
            'kategori_daerah' => 'biasa',
            'lat' => '0.86',
            'lng' => '99.55',
        ], $timpa);
    }

    private function assertTidakAdaFormBersarang(string $html): void
    {
        $tanpaSkrip = preg_replace('/<script\b[^>]*>.*?<\/script>/is', '', $html) ?? $html;
        preg_match_all('/<\/?form\b[^>]*>/i', $tanpaSkrip, $cocok);
        $kedalaman = 0;
        $maks = 0;
        foreach ($cocok[0] as $tag) {
            if (str_starts_with($tag, '</')) {
                $kedalaman--;
            } else {
                $kedalaman++;
                $maks = max($maks, $kedalaman);
            }
            $this->assertGreaterThanOrEqual(0, $kedalaman, 'Tag </form> tanpa pasangan: '.$tag);
        }
        $this->assertSame(0, $kedalaman, 'Ada <form> yang tidak ditutup.');
        $this->assertLessThanOrEqual(1, $maks, 'Ditemukan form bersarang (kedalaman > 1).');
    }

    public function test_form_ubah_tidak_memiliki_form_bersarang(): void
    {
        $res = $this->actingAs($this->kabid)->get(route('sekolah.edit', $this->sekolah));
        $res->assertOk();
        $html = $res->getContent();
        $this->assertTidakAdaFormBersarang($html);
        // Kedua form tetap ada sebagai form yang terpisah:
        $this->assertStringContainsString(route('sekolah.update', $this->sekolah), $html);
        $this->assertStringContainsString(route('sekolah.verifikasi-koordinat', $this->sekolah), $html);
        $this->assertStringContainsString('name="kebutuhan_terkirim"', $html);
    }

    public function test_form_tambah_tidak_memiliki_form_bersarang(): void
    {
        $res = $this->actingAs($this->kabid)->get(route('sekolah.create'));
        $res->assertOk();
        $this->assertTidakAdaFormBersarang($res->getContent());
    }

    public function test_ubah_nama_tanpa_payload_kebutuhan_mempertahankan_kebutuhan(): void
    {
        // Mensimulasikan payload form yang seksi kebutuhannya tidak
        // terkirim sama sekali (kasus bug P0): kebutuhan lama wajib utuh.
        $this->actingAs($this->kabid)->put(route('sekolah.update', $this->sekolah), $this->muatanDasar([
            'nama' => 'SDN 1 Uji Baru',
        ]))->assertRedirect(route('sekolah.index'));

        $this->assertSame('SDN 1 Uji Baru', $this->sekolah->fresh()->nama);
        $this->assertSame(
            ['IPA' => 2, 'Matematika' => 1],
            KebutuhanGuru::where('sekolah_id', $this->sekolah->id)->pluck('jumlah', 'mapel')->all()
        );
    }

    public function test_ubah_dengan_payload_kebutuhan_mengganti_kebutuhan(): void
    {
        $this->actingAs($this->kabid)->put(route('sekolah.update', $this->sekolah), $this->muatanDasar([
            'kebutuhan' => [['mapel' => 'Bahasa Indonesia', 'jumlah' => 3]],
        ]))->assertRedirect(route('sekolah.index'));

        $this->assertSame(
            ['Bahasa Indonesia' => 3],
            KebutuhanGuru::where('sekolah_id', $this->sekolah->id)->pluck('jumlah', 'mapel')->all()
        );
    }

    public function test_ubah_dengan_penanda_tanpa_baris_mengosongkan_kebutuhan(): void
    {
        // Pengguna sengaja menghapus semua baris kebutuhan di form:
        // penanda terkirim, kunci kebutuhan tidak ada → dikosongkan.
        $this->actingAs($this->kabid)->put(route('sekolah.update', $this->sekolah), $this->muatanDasar([
            'kebutuhan_terkirim' => '1',
        ]))->assertRedirect(route('sekolah.index'));

        $this->assertSame(0, KebutuhanGuru::where('sekolah_id', $this->sekolah->id)->count());
    }

    public function test_verifikasi_koordinat_tetap_berfungsi_setelah_pemisahan_form(): void
    {
        $this->actingAs($this->kabid)
            ->post(route('sekolah.verifikasi-koordinat', $this->sekolah), ['koordinat_sumber' => 'gps_lapangan'])
            ->assertRedirect();

        $this->assertTrue($this->sekolah->fresh()->koordinatTerverifikasi());
    }
}
