<?php

// Tes fitur untuk temuan audit: SPK-001, SPK-002, FLOW-001, FLOW-002, SEC-001, SEC-002.
// Menjalankan skenario keamanan persetujuan terhadap database MySQL khusus tes.

namespace Tests\Feature;

use App\Livewire\PetaRekomendasi;
use App\Models\Guru;
use App\Models\HasilSpk;
use App\Models\KebutuhanGuru;
use App\Models\RekomendasiSwap;
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

    public function test_spk_generate_tidak_melebihi_sisa_satu_formasi(): void
    {
        $kabid = $this->buatUser('kabid_ptk');
        $asalA = $this->buatSekolah(0.0, 100.3);
        $asalB = $this->buatSekolah(0.0, 100.4);
        $this->buatGuru($asalA);
        $this->buatGuru($asalB);
        $tujuan = $this->buatSekolah(0.0, 100.05);
        $this->butuh($tujuan, 'Matematika', 1);

        $this->actingAs($kabid)->post('/spk/generate')->assertRedirect();
        $this->actingAs($kabid)->post('/spk/generate')->assertRedirect();

        $this->assertSame(1, HasilSpk::where('sekolah_tujuan_id', $tujuan->id)
            ->where('status', 'rekomendasi')->count());
    }

    public function test_dashboard_tidak_menghitung_koordinat_hilang_sebagai_nol_km(): void
    {
        $kabid = $this->buatUser('kabid_ptk');
        $sekolahValid = $this->buatSekolah(0.0, 100.3);
        $this->buatGuru($sekolahValid);

        // Relasi sekolah dan cache koordinat tidak tersedia pada guru ini.
        // Kolom lat/lng tabel sekolah sendiri memang NOT NULL.
        $this->buatGuru($sekolahValid, [
            'sekolah_id' => null,
            'sekolah_saat_ini_lat' => null,
            'sekolah_saat_ini_lng' => null,
        ]);

        $this->actingAs($kabid)->get('/')
            ->assertOk()
            ->assertSee('33.4 km')
            ->assertSee('1 dari 2 guru memiliki koordinat valid');
    }

    public function test_swap_tolak_mengunci_dan_memeriksa_ulang_status(): void
    {
        $kabid = $this->buatUser('kabid_ptk');
        $asalA = $this->buatSekolah(1.0, 100.8);
        $asalB = $this->buatSekolah(1.0, 100.1);
        $a = $this->buatGuru($asalA, ['domisili_lat' => 1.0]);
        $b = $this->buatGuru($asalB, [
            'domisili_lat' => 1.0,
            'domisili_lng' => 100.9,
        ]);

        $swap = RekomendasiSwap::create([
            'guru_a_id' => $a->id,
            'guru_b_id' => $b->id,
            'sekolah_tujuan_a_id' => $asalB->id,
            'sekolah_tujuan_b_id' => $asalA->id,
            'jarak_a_sekarang_km' => 20,
            'jarak_b_sekarang_km' => 20,
            'jarak_a_usulan_km' => 10,
            'jarak_b_usulan_km' => 10,
            'hemat_a_km' => 10,
            'hemat_b_km' => 10,
            'total_hemat_km' => 20,
            'status' => 'disetujui',
        ]);

        $this->actingAs($kabid)->post("/swap/{$swap->id}/tolak")
            ->assertRedirect();

        $this->assertSame('disetujui', $swap->fresh()->status);
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

    // ============ MAP-001: sumber koordinat tunggal ============

    public function test_jarak_ke_sekolah_memakai_relasi_bukan_cache(): void
    {
        // Sekolah di (0, 100.3) ≈ 33,4 km dari domisili (0,100),
        // tapi cache denormalisasi diset menyimpang (0, 999).
        $sekolah = $this->buatSekolah(0.0, 100.3);
        $guru = $this->buatGuru($sekolah, [
            'sekolah_saat_ini_lat' => 0.0,
            'sekolah_saat_ini_lng' => 999.0,
        ]);

        $koor = $guru->koordinatSekolah();
        $this->assertEqualsWithDelta(0.0, $koor['lat'], 0.001);
        $this->assertEqualsWithDelta(100.3, $koor['lng'], 0.001);

        $jarak = $guru->jarakKeSekolahSaatIni();
        $this->assertEqualsWithDelta(33.4, $jarak, 0.5);
    }

    // ============ SWAP-001: greedy max-weight + validasi administratif ============

    public function test_swap_memilih_pasangan_dengan_total_hemat_terbesar(): void
    {
        $kabid = $this->buatUser('kabid_ptk');

        // A: domisili (1,100), SA (1,100.8) → 89 km
        $sa = $this->buatSekolah(1.0, 100.8);
        $a = $this->buatGuru($sa, ['domisili_lat' => 1.0]);
        // C dibuat DULU (urutan iterasi lama akan memilih A-C):
        // C: domisili (1,100.85), SC (1,100.15) → 77,9 km
        // A↔C win-win total hemat ≈ 144,6 km
        $sc = $this->buatSekolah(1.0, 100.15);
        $c = $this->buatGuru($sc, ['domisili_lat' => 1.0, 'domisili_lng' => 100.85]);
        // B: domisili (1,100.9), SB (1,100.1) → 89 km
        // A↔B win-win total hemat ≈ 156 km (terbesar)
        $sb = $this->buatSekolah(1.0, 100.1);
        $b = $this->buatGuru($sb, ['domisili_lat' => 1.0, 'domisili_lng' => 100.9]);

        $this->actingAs($kabid)->post('/swap/generate')->assertRedirect();

        $swaps = RekomendasiSwap::all();
        $this->assertCount(1, $swaps);
        $pasangan = [$swaps->first()->guru_a_id, $swaps->first()->guru_b_id];
        sort($pasangan);
        $ab = [$a->id, $b->id];
        sort($ab);
        $this->assertEquals($ab, $pasangan); // bukan (A,C)
    }

    public function test_swap_menolak_beda_jenjang_dan_usulan_aktif(): void
    {
        $kabid = $this->buatUser('kabid_ptk');

        // Pasangan win-win geometris tapi beda jenjang → ditolak
        $sa = $this->buatSekolah(1.0, 100.8);
        $a = $this->buatGuru($sa, ['domisili_lat' => 1.0]);
        $sb = $this->buatSekolah(1.0, 100.1);
        $b = $this->buatGuru($sb, [
            'domisili_lat' => 1.0, 'domisili_lng' => 100.9, 'jenjang' => 'SMP',
        ]);

        $this->actingAs($kabid)->post('/swap/generate');
        $this->assertCount(0, RekomendasiSwap::all());

        // Jenjang disamakan → win-win, tapi B punya usulan aktif → tetap ditolak
        $b->update(['jenjang' => 'SD']);
        $tujuan = $this->buatSekolah(0.0, 100.05);
        $this->butuh($tujuan, 'Matematika', 1);
        UsulanPenempatan::create([
            'guru_id' => $b->id, 'sekolah_id' => $tujuan->id,
            'alasan' => 'Alasan pindah yang cukup panjang.',
            'status' => 'diajukan', 'diajukan_oleh' => $kabid->id,
        ]);

        $this->actingAs($kabid)->post('/swap/generate');
        $this->assertCount(0, RekomendasiSwap::all());
    }

    // ============ SPK-003: kolom penjelasan kelayakan ============

    public function test_spk_index_menampilkan_penjelasan_kelayakan(): void
    {
        $kabid = $this->buatUser('kabid_ptk');
        $asal = $this->buatSekolah(0.0, 100.3);
        $guru = $this->buatGuru($asal);
        $dekat = $this->buatSekolah(0.0, 100.05);
        $this->butuh($dekat, 'Matematika', 2);

        $this->actingAs($kabid)->post('/spk/generate');

        $this->actingAs($kabid)->get('/spk')
            ->assertOk()
            ->assertSee('Kelayakan', false)
            ->assertSee('Sisa formasi', false)
            ->assertSee('(garis lurus)', false);
    }

    public function test_login_dibatasi_setelah_lima_percobaan_per_menit(): void
    {
        $kredensial = [
            'email' => 'tidak-ada@example.test',
            'password' => 'password-salah',
        ];

        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', $kredensial)->assertRedirect();
        }

        $this->post('/login', $kredensial)->assertStatus(429);
    }

    public function test_admin_seeder_tidak_membuat_kredensial_demo_di_production(): void
    {
        $this->app['env'] = 'production';

        app(\Database\Seeders\AdminSeeder::class)->run();

        $this->assertDatabaseMissing('users', [
            'email' => 'admin@peta-asn.local',
        ]);
    }

}