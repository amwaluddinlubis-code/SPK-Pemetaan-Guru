<?php

// Tes fitur untuk temuan audit: SPK-001, SPK-002, FLOW-001, FLOW-002, SEC-001, SEC-002.
// Menjalankan skenario keamanan persetujuan terhadap database MySQL khusus tes.

namespace Tests\Feature;

use App\Livewire\PetaRekomendasi;
use App\Models\Guru;
use App\Models\HasilSpk;
use App\Models\KebutuhanGuru;
use App\Models\Sekolah;
use App\Models\User;
use App\Models\UsulanPenempatan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class KeamananPersetujuanTest extends TestCase
{
    use RefreshDatabase;

    private int $seq = 0;

    private function buatUser(string $role, array $attr = []): User
    {
        return User::create(array_merge([
            'name' => 'User '.$role.' '.++$this->seq,
            'email' => $role.$this->seq.'@test.local',
            'password' => Hash::make('rahasia123'),
            'role' => $role,
        ], $attr));
    }

    private function buatSekolah(float $lat, float $lng, array $attr = []): Sekolah
    {
        return Sekolah::create(array_merge([
            'npsn' => '9'.str_pad((string) ++$this->seq, 7, '0', STR_PAD_LEFT),
            'nama' => 'Sekolah '.$this->seq,
            'alamat' => 'Jl. Tes '.$this->seq,
            'kecamatan' => 'Panyabungan',
            'lat' => $lat,
            'lng' => $lng,
        ], $attr));
    }

    private function buatGuru(Sekolah $sekolah, array $attr = []): Guru
    {
        return Guru::create(array_merge([
            'nip' => 'nip'.++$this->seq,
            'nama' => 'Guru '.$this->seq,
            'mapel' => 'Matematika',
            'jenjang' => 'SD',
            'domisili_alamat' => 'Domisili '.$this->seq,
            'kecamatan' => 'Panyabungan',
            'sekolah_id' => $sekolah->id,
            'domisili_lat' => 0.0,
            'domisili_lng' => 100.0,
            'sekolah_saat_ini' => $sekolah->nama,
            'sekolah_saat_ini_lat' => $sekolah->lat,
            'sekolah_saat_ini_lng' => $sekolah->lng,
        ], $attr));
    }

    private function butuh(Sekolah $sekolah, string $mapel, int $jumlah): void
    {
        KebutuhanGuru::create([
            'sekolah_id' => $sekolah->id, 'mapel' => $mapel, 'jumlah' => $jumlah,
        ]);
    }

    /**
     * Skenario: guru di (0,100), sekolah asal di (0,100.3) ≈ 33 km.
     * Kandidat dekat (0,100.05) ≈ 5,5 km → hemat positif.
     * Kandidat jauh  (0,100.50) ≈ 55 km → hemat negatif, harus gugur.
     */
    public function test_spk_generate_hanya_rekomendasi_hemat_positif(): void
    {
        $kabid = $this->buatUser('kabid_ptk');
        $asal = $this->buatSekolah(0.0, 100.3);
        $guru = $this->buatGuru($asal);
        $dekat = $this->buatSekolah(0.0, 100.05);
        $jauh = $this->buatSekolah(0.0, 100.50);
        $this->butuh($dekat, 'Matematika', 2);
        $this->butuh($jauh, 'Matematika', 2);

        $this->actingAs($kabid)->post('/spk/generate')->assertRedirect();

        $recs = HasilSpk::where('guru_id', $guru->id)->get();
        $this->assertCount(1, $recs);
        $this->assertEquals($dekat->id, $recs->first()->sekolah_tujuan_id);
        $this->assertGreaterThan(0, (float) $recs->first()->hemat_km);
        $this->assertEquals($asal->id, $recs->first()->sekolah_asal_id);
    }

    public function test_spk_setujui_ganda_ditolak_tanpa_efek_ganda(): void
    {
        $kabid = $this->buatUser('kabid_ptk');
        $kadis = $this->buatUser('kadis');
        $asal = $this->buatSekolah(0.0, 100.3);
        $guru = $this->buatGuru($asal);
        $tujuan = $this->buatSekolah(0.0, 100.05);
        $this->butuh($tujuan, 'Matematika', 1);

        $this->actingAs($kabid)->post('/spk/generate');
        $rec = HasilSpk::where('guru_id', $guru->id)->firstOrFail();

        $this->actingAs($kadis)->post("/spk/{$rec->id}/setujui", ['catatan' => 'ok'])
            ->assertSessionMissing('gagal');
        $this->actingAs($kadis)->post("/spk/{$rec->id}/setujui", ['catatan' => 'lagi'])
            ->assertSessionHas('gagal');

        $this->assertEquals('disetujui', $rec->fresh()->status);
        $this->assertEquals($tujuan->id, $guru->fresh()->sekolah_id);
        $this->assertEquals(0, KebutuhanGuru::where('sekolah_id', $tujuan->id)
            ->where('mapel', 'Matematika')->value('jumlah'));
    }

    public function test_spk_setujui_guru_sudah_pindah_ditolak(): void
    {
        $kabid = $this->buatUser('kabid_ptk');
        $kadis = $this->buatUser('kadis');
        $asal = $this->buatSekolah(0.0, 100.3);
        $guru = $this->buatGuru($asal);
        $tujuan = $this->buatSekolah(0.0, 100.05);
        $lain = $this->buatSekolah(0.0, 100.06);
        $this->butuh($tujuan, 'Matematika', 1);

        $this->actingAs($kabid)->post('/spk/generate');
        $rec = HasilSpk::where('guru_id', $guru->id)->firstOrFail();

        // Guru keburu pindah ke sekolah lain sebelum persetujuan diproses
        $guru->update(['sekolah_id' => $lain->id]);

        $this->actingAs($kadis)->post("/spk/{$rec->id}/setujui")
            ->assertSessionHas('gagal');

        $this->assertEquals('rekomendasi', $rec->fresh()->status);
        $this->assertEquals($lain->id, $guru->fresh()->sekolah_id);
    }

    public function test_usulan_aktif_ganda_dicegah(): void
    {
        $kabid = $this->buatUser('kabid_ptk');
        $asal = $this->buatSekolah(0.0, 100.3);
        $guru = $this->buatGuru($asal);
        $tujuan1 = $this->buatSekolah(0.0, 100.05);
        $tujuan2 = $this->buatSekolah(0.0, 100.06);
        $this->butuh($tujuan1, 'Matematika', 1);
        $this->butuh($tujuan2, 'Matematika', 1);

        $data = fn (Sekolah $t) => [
            'guru_id' => $guru->id, 'sekolah_id' => $t->id,
            'alasan' => 'Alasan pindah yang cukup panjang.',
        ];

        $this->actingAs($kabid)->post('/usulan', $data($tujuan1))
            ->assertSessionMissing('gagal');
        $this->assertEquals(1, UsulanPenempatan::where('guru_id', $guru->id)->count());

        $this->actingAs($kabid)->post('/usulan', $data($tujuan2))
            ->assertSessionHasErrors('guru_id');
        $this->assertEquals(1, UsulanPenempatan::where('guru_id', $guru->id)->count());
    }

    public function test_usulan_setujui_harus_lewat_verifikasi_dulu(): void
    {
        $kabid = $this->buatUser('kabid_ptk');
        $kadis = $this->buatUser('kadis');
        $asal = $this->buatSekolah(0.0, 100.3);
        $guru = $this->buatGuru($asal);
        $tujuan = $this->buatSekolah(0.0, 100.05);
        $this->butuh($tujuan, 'Matematika', 1);

        $this->actingAs($kabid)->post('/usulan', [
            'guru_id' => $guru->id, 'sekolah_id' => $tujuan->id,
            'alasan' => 'Alasan pindah yang cukup panjang.',
        ]);
        $usulan = UsulanPenempatan::firstOrFail();

        // Langsung setujui tanpa verifikasi → ditolak
        $this->actingAs($kadis)->post("/usulan/{$usulan->id}/setujui")
            ->assertSessionHas('gagal');
        $this->assertEquals('diajukan', $usulan->fresh()->status);
        $this->assertEquals($asal->id, $guru->fresh()->sekolah_id);

        // Alur benar: verifikasi lalu setujui → guru termutasi
        $this->actingAs($kabid)->post("/usulan/{$usulan->id}/verifikasi");
        $this->actingAs($kadis)->post("/usulan/{$usulan->id}/setujui")
            ->assertSessionMissing('gagal');
        $this->assertEquals('disetujui', $usulan->fresh()->status);
        $this->assertEquals($tujuan->id, $guru->fresh()->sekolah_id);
    }

    public function test_guru_role_tidak_bisa_ubah_nip_lewat_request_langsung(): void
    {
        $asal = $this->buatSekolah(0.0, 100.3);
        $guru = $this->buatGuru($asal);
        $user = $this->buatUser('guru', ['guru_id' => $guru->id]);

        $this->actingAs($user)->put("/guru/{$guru->id}", [
            'nip' => 'nip-palsu',
            'nama' => $guru->nama,
            'mapel' => $guru->mapel,
            'jenjang' => $guru->jenjang,
            'domisili_alamat' => $guru->domisili_alamat,
            'domisili_lat' => $guru->domisili_lat,
            'domisili_lng' => $guru->domisili_lng,
            'sekolah_saat_ini' => $guru->sekolah_saat_ini,
        ])->assertRedirect();

        $this->assertNotEquals('nip-palsu', $guru->fresh()->nip);
    }

    public function test_kepala_sekolah_tidak_bisa_intip_guru_sekolah_lain_di_peta(): void
    {
        $sekolahA = $this->buatSekolah(0.0, 100.1);
        $sekolahB = $this->buatSekolah(0.0, 100.2);
        $guruB = $this->buatGuru($sekolahB);
        $kepsek = $this->buatUser('kepala_sekolah', ['sekolah_id' => $sekolahA->id]);

        $this->actingAs($kepsek);

        // Guru di luar scope: cari() diabort (403) sebelum data apa pun diisi.
        // Livewire 4 tidak meneruskan HttpException ke test, jadi verifikasi
        // lewat perilaku: tidak ada data guru lain yang bocor.
        $komponen = Livewire::test(PetaRekomendasi::class)
            ->set('guruId', (string) $guruB->id)
            ->call('cari');

        $this->assertEmpty($komponen->get('hasil'));
        $this->assertNull($komponen->get('infoGuru'));

        // Kontrol positif: guru dalam scope tetap bisa dicari
        $guruA = $this->buatGuru($sekolahA);
        $komponen2 = Livewire::test(PetaRekomendasi::class)
            ->set('guruId', (string) $guruA->id)
            ->call('cari');
        $this->assertNotNull($komponen2->get('infoGuru'));
    }
}
