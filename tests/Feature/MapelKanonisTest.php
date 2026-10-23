<?php

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\User;
use App\Services\AnalisisKebutuhanGuru;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// Mapel tidak lagi isian bebas: daftar kanonis tunggal dari
// AnalisisKebutuhanGuru, form memakai pilihan, dan nilai yang tersimpan
// selalu label kanonis (varian ejaan dinormalisasi di controller & impor).
class MapelKanonisTest extends TestCase
{
    use RefreshDatabase;

    private function loginKabid(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'kabid_ptk']));
    }

    private function payloadGuru(array $timpa = []): array
    {
        return array_merge([
            'nip' => '198501012010011001',
            'nama' => 'Siti Aminah',
            'mapel' => 'Matematika',
            'jenjang' => 'SMP',
            'domisili_alamat' => 'Jl. Mawar No. 10',
            'domisili_lat' => '0.79',
            'domisili_lng' => '99.41',
            'sekolah_saat_ini' => 'SMPN 1 Panyabungan',
        ], $timpa);
    }

    public function test_daftar_kanonis_tunggal_dan_tanpa_duplikat(): void
    {
        $daftar = AnalisisKebutuhanGuru::daftarKanonis();

        foreach (['Guru Kelas', 'Agama Islam', 'Agama Kristen', 'Pancasila', 'Bahasa Indonesia', 'Matematika',
            'IPA', 'IPS', 'Bahasa Inggris', 'PJOK', 'Informatika', 'Seni/Prakarya'] as $label) {
            $this->assertContains($label, $daftar);
        }
        $this->assertSame($daftar, array_values(array_unique($daftar)));
        // Setiap label kanonis lolos normalisasi apa adanya.
        foreach ($daftar as $label) {
            $this->assertSame($label, AnalisisKebutuhanGuru::kanonis($label));
        }
    }

    public function test_form_memakai_pilihan_mapel_kanonis(): void
    {
        $this->loginKabid();

        $res = $this->get('/guru/create');
        $res->assertOk();
        $res->assertSee('id="f-mapel"', false);
        // Opsi tertanam sebagai data JS ter-escape di komponen — bandingkan
        // setelah membuang backslash & garis miring dari kedua sisi.
        $html = str_replace(['\\', '/'], '', $res->getContent());
        foreach (AnalisisKebutuhanGuru::daftarKanonis() as $label) {
            $this->assertStringContainsString(str_replace('/', '', $label), $html);
        }
    }

    public function test_store_menormalisasi_varian_ejaan_mapel(): void
    {
        $this->loginKabid();

        $this->post('/guru', $this->payloadGuru([
            'mapel' => 'Ilmu Pengetahuan Alam',
            'sudah_sertifikasi' => '1',
            'mapel_sertifikasi' => 'PAI',
        ]))->assertRedirect(route('guru.index'));

        $guru = Guru::where('nip', '198501012010011001')->firstOrFail();
        $this->assertSame('IPA', $guru->mapel);
        $this->assertSame('Agama Islam', $guru->mapel_sertifikasi);
    }

    public function test_update_menormalisasi_varian_ejaan_mapel(): void
    {
        $this->loginKabid();
        $guru = Guru::create([
            'nip' => '199001012015011002',
            'nama' => 'Budi Santoso',
            'mapel' => 'Matematika',
            'jenjang' => 'SMP',
            'domisili_alamat' => 'Jl. Kenanga No. 3',
            'domisili_lat' => 0.80,
            'domisili_lng' => 99.50,
            'sekolah_saat_ini' => 'SMPN 1 Panyabungan',
        ]);

        $this->put('/guru/'.$guru->id, $this->payloadGuru([
            'nip' => $guru->nip,
            'mapel' => 'penjaskes',
        ]))->assertRedirect(route('guru.index'));

        $this->assertSame('PJOK', $guru->fresh()->mapel);
    }
}
