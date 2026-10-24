<?php

namespace Tests\Feature;

use App\Livewire\PetaRekomendasi;
use App\Livewire\TabelSekolah;
use App\Models\Guru;
use App\Models\Sekolah;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

// Permintaan pemilik (24 Okt 2026):
// 1) Select "Pilih guru" di panel rekomendasi dashboard menjadi
//    searchable select;
// 2) label opsi guru berformat "Nama - NIP";
// 3) tabel sekolah punya tombol aksi yang menampilkan daftar guru
//    yang ditugaskan di sekolah itu (modal, scope peran ditegakkan).
class PilihGuruDanGuruSekolahTest extends TestCase
{
    use RefreshDatabase;

    private User $kabid;

    private Sekolah $sekolahA;

    private Sekolah $sekolahB;

    private Guru $guruSatu;

    protected function setUp(): void
    {
        parent::setUp();
        $this->kabid = User::factory()->create(['role' => 'kabid_ptk']);
        $this->sekolahA = Sekolah::create([
            'nama' => 'SMPN 1 Panyabungan', 'jenjang' => 'SMP', 'npsn' => '10200001',
            'alamat' => 'Jl. A', 'kecamatan' => 'Panyabungan', 'kategori_daerah' => 'biasa',
            'lat' => 0.86, 'lng' => 99.55, 'rombel' => 6,
        ]);
        $this->sekolahB = Sekolah::create([
            'nama' => 'SDN 2 Panyabungan', 'jenjang' => 'SD', 'npsn' => '10200002',
            'alamat' => 'Jl. B', 'kecamatan' => 'Panyabungan', 'kategori_daerah' => 'biasa',
            'lat' => 0.87, 'lng' => 99.56, 'rombel' => 6,
        ]);
        $this->guruSatu = Guru::create([
            'nip' => '199001012020011001', 'nama' => 'Guru Satu', 'mapel' => 'Matematika',
            'jenjang' => 'SMP', 'status_kepegawaian' => 'pppk', 'sekolah_id' => $this->sekolahA->id,
            'sekolah_saat_ini' => 'SMPN 1 Panyabungan',
        ]);
        Guru::create([
            'nip' => '199202022020012002', 'nama' => 'Guru Dua', 'mapel' => 'IPA',
            'jenjang' => 'SMP', 'status_kepegawaian' => 'pns', 'sekolah_id' => $this->sekolahA->id,
            'sekolah_saat_ini' => 'SMPN 1 Panyabungan',
        ]);
    }

    public function test_dashboard_pilih_guru_searchable_dengan_label_nama_nip(): void
    {
        $res = $this->actingAs($this->kabid)->get('/');
        $res->assertOk();
        $html = $res->getContent();
        // Combobox pencarian, bukan select native lama:
        $this->assertStringContainsString('role="combobox"', $html);
        $this->assertStringNotContainsString('<select id="f-guruId"', $html);
        // Label opsi berformat Nama - NIP (bukan lagi Nama — Mapel (Jenjang)):
        $this->assertStringContainsString('Guru Satu - 199001012020011001', $html);
        $this->assertStringNotContainsString('Guru Satu — Matematika (SMP)', $html);
    }

    public function test_peta_rekomendasi_cari_setelah_pilih_guru(): void
    {
        $this->actingAs($this->kabid);
        Livewire::test(PetaRekomendasi::class)
            ->set('guruId', (string) $this->guruSatu->id)
            ->call('cari')
            ->assertSet('infoGuru.nama', 'Guru Satu');
    }

    public function test_modal_guru_sekolah_menampilkan_guru_yang_ditugaskan(): void
    {
        $this->actingAs($this->kabid);
        Livewire::test(TabelSekolah::class)
            ->call('tampilGuru', $this->sekolahA->id)
            ->assertSet('guruSekolahId', $this->sekolahA->id)
            ->assertSee('Guru yang Ditugaskan')
            ->assertSee('Guru Satu')
            ->assertSee('199001012020011001')
            ->assertSee('Guru Dua')
            ->call('tutupGuru')
            ->assertSet('guruSekolahId', null);
    }

    public function test_modal_guru_sekolah_tanpa_guru(): void
    {
        $this->actingAs($this->kabid);
        Livewire::test(TabelSekolah::class)
            ->call('tampilGuru', $this->sekolahB->id)
            ->assertSet('guruSekolahId', $this->sekolahB->id)
            ->assertSee('Belum ada guru yang ditugaskan di sekolah ini.');
    }

    public function test_modal_guru_sekolah_scope_kepala_sekolah(): void
    {
        $kepsek = User::create([
            'name' => 'Kepsek B', 'email' => 'kepsek-b@test.local',
            'password' => Hash::make('rahasia123'), 'role' => 'kepala_sekolah',
            'sekolah_id' => $this->sekolahB->id,
        ]);
        $this->actingAs($kepsek);

        // Sekolahnya sendiri: boleh.
        Livewire::test(TabelSekolah::class)
            ->call('tampilGuru', $this->sekolahB->id)
            ->assertSet('guruSekolahId', $this->sekolahB->id);

        // Sekolah lain: di luar scope — tidak ada data yang terbuka.
        $komponen = Livewire::test(TabelSekolah::class);
        try {
            $komponen->call('tampilGuru', $this->sekolahA->id);
            $komponen->assertSet('guruSekolahId', null);
        } catch (ModelNotFoundException) {
            $this->addToAssertionCount(1);
        }
    }
}
