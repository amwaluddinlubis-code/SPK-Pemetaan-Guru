<?php

// Tes cakupan fitur — melengkapi aturan proyek "1 fitur = 1 test".
// Fitur yang dicakup: tolak SPK/swap/usulan, setujui swap, CRUD sekolah,
// import guru via CSV, dashboard, dan dashboard pemda.

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\HasilSpk;
use App\Models\KebutuhanGuru;
use App\Models\RekomendasiSwap;
use App\Models\Sekolah;
use App\Models\User;
use App\Models\UsulanPenempatan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CakupanFiturTest extends TestCase
{
    use RefreshDatabase;

    private int $seq = 100;

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
            'npsn' => '8'.str_pad((string) ++$this->seq, 7, '0', STR_PAD_LEFT),
            'nama' => 'Sekolah '.$this->seq,
            'alamat' => 'Jl. Tes '.$this->seq,
            'kecamatan' => 'Panyabungan',
            'lat' => $lat,
            'lng' => $lng,
            'koordinat_terverifikasi_lat' => $lat,
            'koordinat_terverifikasi_lng' => $lng,
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
            'domisili_lat' => 1.0,
            'domisili_lng' => 100.0,
            'sekolah_saat_ini' => $sekolah->nama,
            'sekolah_saat_ini_lat' => $sekolah->lat,
            'sekolah_saat_ini_lng' => $sekolah->lng,
        ], $attr));
    }

    // ============ Tolak SPK / swap / usulan ============

    public function test_spk_tolak_mengubah_status_tanpa_mutasi(): void
    {
        $kabid = $this->buatUser('kabid_ptk');
        $kadis = $this->buatUser('kadis');
        $asal = $this->buatSekolah(1.0, 100.3);
        $guru = $this->buatGuru($asal);
        $tujuan = $this->buatSekolah(1.0, 100.05);
        KebutuhanGuru::create(['sekolah_id' => $tujuan->id, 'mapel' => 'Matematika', 'jumlah' => 1]);

        $this->actingAs($kabid)->post('/spk/generate');
        $rec = HasilSpk::firstOrFail();

        $this->actingAs($kadis)->post("/spk/{$rec->id}/tolak", ['catatan_admin' => 'belum perlu'])
            ->assertRedirect();

        $this->assertEquals('ditolak', $rec->fresh()->status);
        $this->assertEquals($asal->id, $guru->fresh()->sekolah_id);
        $this->assertEquals(1, KebutuhanGuru::where('sekolah_id', $tujuan->id)->value('jumlah'));
    }

    public function test_swap_setujui_memindahkan_kedua_guru(): void
    {
        $kabid = $this->buatUser('kabid_ptk');
        $kadis = $this->buatUser('kadis');

        $sa = $this->buatSekolah(1.0, 100.8);
        $a = $this->buatGuru($sa, ['domisili_lng' => 100.0]);
        $sb = $this->buatSekolah(1.0, 100.1);
        $b = $this->buatGuru($sb, ['domisili_lng' => 100.9]);

        $this->actingAs($kabid)->post('/swap/generate');
        $swap = RekomendasiSwap::firstOrFail();

        $this->actingAs($kadis)->post("/swap/{$swap->id}/setujui")
            ->assertSessionMissing('gagal');

        $this->assertEquals('disetujui', $swap->fresh()->status);
        $this->assertEquals($sb->id, $a->fresh()->sekolah_id);
        $this->assertEquals($sa->id, $b->fresh()->sekolah_id);
    }

    public function test_swap_tolak_mengubah_status(): void
    {
        $kabid = $this->buatUser('kabid_ptk');

        $sa = $this->buatSekolah(1.0, 100.8);
        $a = $this->buatGuru($sa, ['domisili_lng' => 100.0]);
        $sb = $this->buatSekolah(1.0, 100.1);
        $b = $this->buatGuru($sb, ['domisili_lng' => 100.9]);

        $this->actingAs($kabid)->post('/swap/generate');
        $swap = RekomendasiSwap::firstOrFail();

        $this->actingAs($kabid)->post("/swap/{$swap->id}/tolak", [
            'catatan_admin' => 'Ditolak untuk pengujian.',
        ])->assertRedirect();

        $this->assertEquals('ditolak', $swap->fresh()->status);
        $this->assertEquals($sa->id, $a->fresh()->sekolah_id);
        $this->assertEquals($sb->id, $b->fresh()->sekolah_id);
    }

    public function test_usulan_tolak_mengubah_status_tanpa_mutasi(): void
    {
        $kabid = $this->buatUser('kabid_ptk');
        $asal = $this->buatSekolah(1.0, 100.3);
        $guru = $this->buatGuru($asal);
        $tujuan = $this->buatSekolah(1.0, 100.05);
        KebutuhanGuru::create(['sekolah_id' => $tujuan->id, 'mapel' => 'Matematika', 'jumlah' => 1]);

        $this->actingAs($kabid)->post('/usulan', [
            'guru_id' => $guru->id, 'sekolah_id' => $tujuan->id,
            'alasan' => 'Alasan pindah yang cukup panjang.',
        ]);
        $usulan = UsulanPenempatan::firstOrFail();

        $this->actingAs($kabid)->post("/usulan/{$usulan->id}/tolak", ['catatan' => 'belum memenuhi syarat'])
            ->assertRedirect();

        $this->assertEquals('ditolak', $usulan->fresh()->status);
        $this->assertEquals($asal->id, $guru->fresh()->sekolah_id);
    }

    // ============ CRUD sekolah ============

    public function test_sekolah_store_dan_update(): void
    {
        $kabid = $this->buatUser('kabid_ptk');

        $this->actingAs($kabid)->post('/sekolah', [
            'npsn' => '10209999', 'nama' => 'SD Negeri Tes', 'alamat' => 'Jl. Tes 1',
            'kecamatan' => 'Panyabungan', 'lat' => 1.0, 'lng' => 100.5,
            'kebutuhan' => [['mapel' => 'Matematika', 'jumlah' => 2]],
        ])->assertRedirect();

        $sekolah = Sekolah::where('npsn', '10209999')->firstOrFail();
        $this->assertEquals('SD Negeri Tes', $sekolah->nama);
        $this->assertEquals(2, $sekolah->kebutuhan()->where('mapel', 'Matematika')->value('jumlah'));

        $this->actingAs($kabid)->put("/sekolah/{$sekolah->id}", [
            'npsn' => '10209999', 'nama' => 'SD Negeri Tes Baru', 'alamat' => 'Jl. Tes 1',
            'kecamatan' => 'Panyabungan', 'lat' => 1.0, 'lng' => 100.5,
        ])->assertRedirect();
        $this->assertEquals('SD Negeri Tes Baru', $sekolah->fresh()->nama);
    }

    // ============ Import guru via CSV ============

    public function test_import_guru_csv_validasi_per_baris(): void
    {
        $kabid = $this->buatUser('kabid_ptk');
        $sekolah = $this->buatSekolah(1.0, 100.3);

        $baris = [
            ['nip', 'nama', 'mapel', 'jenjang', 'domisili_alamat', 'kecamatan',
                'domisili_lat', 'domisili_lng', 'sekolah_saat_ini',
                'sekolah_saat_ini_lat', 'sekolah_saat_ini_lng', 'npsn', 'status_pernikahan',
                'status_kepegawaian', 'sudah_sertifikasi', 'mapel_sertifikasi'],
            // Baris valid
            ['111', 'Guru Impor', 'Matematika', 'SD', 'Jl. Impor 1', 'Panyabungan',
                '1.0', '100.0', $sekolah->nama, '1.0', '100.3', $sekolah->npsn, 'kawin',
                'pns', '1', 'Matematika'],
            // Baris tidak valid: NIP kosong + lat di luar rentang
            ['', 'Guru Rusak', 'IPA', 'SD', 'Jl. Rusak', 'Panyabungan',
                '95.0', '100.0', $sekolah->nama, '', '', '', 'belum_kawin',
                'pns', '0', ''],
        ];
        $stream = fopen('php://temp', 'r+');
        foreach ($baris as $b) {
            fputcsv($stream, $b);
        }
        rewind($stream);
        $csv = stream_get_contents($stream);
        fclose($stream);

        $berkas = UploadedFile::fake()->createWithContent('guru.csv', $csv);

        $this->actingAs($kabid)->post('/guru/import', ['berkas' => $berkas])
            ->assertRedirect();

        $guru = Guru::where('nip', '111')->first();
        $this->assertNotNull($guru);
        $this->assertEquals('kawin', $guru->status_pernikahan);
        $this->assertEquals('pns', $guru->status_kepegawaian);
        $this->assertTrue((bool) $guru->sudah_sertifikasi);
        $this->assertTrue($guru->linear());
        $this->assertNull(Guru::where('nama', 'Guru Rusak')->first());
    }

    // ============ Analisis kepegawaian ============

    public function test_spk_tidak_menarik_guru_dari_sekolah_3t(): void
    {
        $kabid = $this->buatUser('kabid_ptk');

        // Sekolah asal 3T: guru di sini dilindungi dari mutasi keluar
        $asal3t = $this->buatSekolah(1.0, 100.3, ['kategori_daerah' => 'sangat_terpencil']);
        $guru3t = $this->buatGuru($asal3t);
        $tujuan = $this->buatSekolah(1.0, 100.05);
        KebutuhanGuru::create(['sekolah_id' => $tujuan->id, 'mapel' => 'Matematika', 'jumlah' => 1]);

        $this->actingAs($kabid)->post('/spk/generate');

        $this->assertNull(HasilSpk::where('guru_id', $guru3t->id)->first());
    }

    public function test_spk_menampilkan_peringatan_dampak_sekolah_asal(): void
    {
        $kabid = $this->buatUser('kabid_ptk');

        $asal = $this->buatSekolah(1.0, 100.3);
        $guru = $this->buatGuru($asal);
        // Sekolah asal kekurangan guru Matematika → peringatan wajib muncul
        KebutuhanGuru::create(['sekolah_id' => $asal->id, 'mapel' => 'Matematika', 'jumlah' => 2]);
        $tujuan = $this->buatSekolah(1.0, 100.05);
        KebutuhanGuru::create(['sekolah_id' => $tujuan->id, 'mapel' => 'Matematika', 'jumlah' => 1]);

        $this->actingAs($kabid)->post('/spk/generate');
        $this->assertNotNull(HasilSpk::where('guru_id', $guru->id)->first());

        $this->actingAs($kabid)->get('/spk')
            ->assertOk()
            ->assertSee('memperparah kekurangan', false);
    }

    public function test_guru_kepegawaian_dan_linearitas(): void
    {
        $kabid = $this->buatUser('kabid_ptk');
        $sekolah = $this->buatSekolah(1.0, 100.3);
        $guru = $this->buatGuru($sekolah);

        $this->actingAs($kabid)->put("/guru/{$guru->id}", [
            'nip' => $guru->nip, 'nama' => $guru->nama, 'mapel' => 'Matematika',
            'jenjang' => 'SD', 'domisili_alamat' => 'Jl. Tes', 'domisili_lat' => 1.0,
            'domisili_lng' => 100.0, 'sekolah_saat_ini' => $sekolah->nama,
            'status_kepegawaian' => 'pppk',
            'sudah_sertifikasi' => '1', 'mapel_sertifikasi' => 'IPA',
        ])->assertRedirect();

        $guru = $guru->fresh();
        $this->assertEquals('pppk', $guru->status_kepegawaian);
        $this->assertEquals('PPPK', $guru->labelStatusKepegawaian());
        $this->assertFalse($guru->linear()); // sertifikat IPA vs mapel Matematika
    }

    public function test_telaahan_staf_memuat_analisis(): void
    {
        $kabid = $this->buatUser('kabid_ptk');

        $asal = $this->buatSekolah(1.0, 100.3);
        $guru = $this->buatGuru($asal);
        $tujuan = $this->buatSekolah(1.0, 100.05);
        KebutuhanGuru::create(['sekolah_id' => $tujuan->id, 'mapel' => 'Matematika', 'jumlah' => 1]);

        $this->actingAs($kabid)->post('/spk/generate');
        $rec = HasilSpk::firstOrFail();

        $this->actingAs($kabid)->get("/spk/{$rec->id}/telaahan")
            ->assertOk()
            ->assertSee('Telaahan Staf', false)
            ->assertSee($guru->nama, false)
            ->assertSee('Undang-Undang Nomor 20 Tahun 2023', false);
    }

    // ============ P0: SEC-003 isolasi data per peran ============

    public function test_spk_index_hanya_tampil_milik_guru_sendiri(): void
    {
        $kabid = $this->buatUser('kabid_ptk');
        $g1 = $this->buatGuru($this->buatSekolah(1.0, 100.3));
        $g2 = $this->buatGuru($this->buatSekolah(1.0, 100.4));
        $tujuan = $this->buatSekolah(1.0, 100.05);
        KebutuhanGuru::create(['sekolah_id' => $tujuan->id, 'mapel' => 'Matematika', 'jumlah' => 5]);

        $this->actingAs($kabid)->post('/spk/generate');
        $this->assertGreaterThanOrEqual(2, HasilSpk::count());

        $akunGuru1 = $this->buatUser('guru', ['guru_id' => $g1->id]);
        $res = $this->actingAs($akunGuru1)->get('/spk');
        $res->assertOk();
        $res->assertSee($g1->nama, false);
        $res->assertDontSee($g2->nama, false);
    }

    public function test_spk_index_kepala_sekolah_hanya_sekolahnya(): void
    {
        $kabid = $this->buatUser('kabid_ptk');
        $sekolahA = $this->buatSekolah(1.0, 100.3);
        $guruA = $this->buatGuru($sekolahA);
        $guruB = $this->buatGuru($this->buatSekolah(1.0, 100.4));
        $tujuan = $this->buatSekolah(1.0, 100.05);
        KebutuhanGuru::create(['sekolah_id' => $tujuan->id, 'mapel' => 'Matematika', 'jumlah' => 5]);

        $this->actingAs($kabid)->post('/spk/generate');
        $this->assertGreaterThanOrEqual(2, HasilSpk::count());

        $kepsek = $this->buatUser('kepala_sekolah', ['sekolah_id' => $sekolahA->id]);
        $res = $this->actingAs($kepsek)->get('/spk');
        $res->assertOk();
        $res->assertSee($guruA->nama, false);
        $res->assertDontSee($guruB->nama, false);
    }

    public function test_swap_index_hanya_tampil_yang_melibatkan_guru(): void
    {
        $kabid = $this->buatUser('kabid_ptk');
        $sa = $this->buatSekolah(1.0, 100.8);
        $a = $this->buatGuru($sa, ['domisili_lng' => 100.0]);
        $sb = $this->buatSekolah(1.0, 100.1);
        $b = $this->buatGuru($sb, ['domisili_lng' => 100.9]);
        $c = $this->buatGuru($this->buatSekolah(1.0, 100.5));

        $this->actingAs($kabid)->post('/swap/generate');
        $this->assertEquals(1, RekomendasiSwap::count());

        $akunC = $this->buatUser('guru', ['guru_id' => $c->id]);
        $res = $this->actingAs($akunC)->get('/swap');
        $res->assertOk();
        $res->assertDontSee($a->nama, false);
        $res->assertDontSee($b->nama, false);

        $akunA = $this->buatUser('guru', ['guru_id' => $a->id]);
        $res = $this->actingAs($akunA)->get('/swap');
        $res->assertOk();
        $res->assertSee($a->nama, false);
    }

    public function test_telaahan_ditolak_untuk_guru_lain(): void
    {
        $kabid = $this->buatUser('kabid_ptk');
        $g1 = $this->buatGuru($this->buatSekolah(1.0, 100.3));
        $g2 = $this->buatGuru($this->buatSekolah(1.0, 100.4));
        $tujuan = $this->buatSekolah(1.0, 100.05);
        KebutuhanGuru::create(['sekolah_id' => $tujuan->id, 'mapel' => 'Matematika', 'jumlah' => 5]);

        $this->actingAs($kabid)->post('/spk/generate');
        $rec = HasilSpk::where('guru_id', $g1->id)->firstOrFail();

        $akunGuru2 = $this->buatUser('guru', ['guru_id' => $g2->id]);
        $this->actingAs($akunGuru2)->get("/spk/{$rec->id}/telaahan")->assertForbidden();

        // Kabid tetap bisa membuka telaahan
        $this->actingAs($kabid)->get("/spk/{$rec->id}/telaahan")->assertOk();
    }

    // ============ P0: FLOW-001 tolak setelah keputusan ============

    public function test_spk_tolak_setelah_disetujui_tidak_mengubah(): void
    {
        $kabid = $this->buatUser('kabid_ptk');
        $kadis = $this->buatUser('kadis');
        $asal = $this->buatSekolah(1.0, 100.3);
        $guru = $this->buatGuru($asal);
        $tujuan = $this->buatSekolah(1.0, 100.05);
        KebutuhanGuru::create(['sekolah_id' => $tujuan->id, 'mapel' => 'Matematika', 'jumlah' => 1]);

        $this->actingAs($kabid)->post('/spk/generate');
        $rec = HasilSpk::firstOrFail();
        $this->actingAs($kadis)->post("/spk/{$rec->id}/setujui");

        $this->assertEquals('disetujui', $rec->fresh()->status);

        $this->actingAs($kadis)->post("/spk/{$rec->id}/tolak", ['catatan_admin' => 'uji'])->assertRedirect();
        $this->assertEquals('disetujui', $rec->fresh()->status);
        $this->assertEquals($tujuan->id, $guru->fresh()->sekolah_id);
    }

    // ============ Dashboard & pemda ============

    public function test_dashboard_memuat_untuk_kabid(): void
    {
        $kabid = $this->buatUser('kabid_ptk');
        $sekolah = $this->buatSekolah(1.0, 100.3);
        $this->buatGuru($sekolah);

        $this->actingAs($kabid)->get('/')->assertOk()->assertSee('Dashboard', false);
    }

    public function test_pemda_dashboard_read_only(): void
    {
        $pemda = $this->buatUser('pemda');
        $sekolah = $this->buatSekolah(1.0, 100.3);
        $this->buatGuru($sekolah);

        $this->actingAs($pemda)->get('/pemda')->assertOk();

        // Peran pemda tidak boleh menjalankan aksi tulis
        $this->actingAs($pemda)->post('/spk/generate')->assertForbidden();
        $this->actingAs($pemda)->post('/swap/generate')->assertForbidden();
    }
}
