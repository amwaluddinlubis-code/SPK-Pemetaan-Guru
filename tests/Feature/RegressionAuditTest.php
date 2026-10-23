<?php

// Regression test temuan audit: race condition generate, pembatasan akses
// per peran, dan atomisitas persetujuan/penolakan SPK & swap.
//
// Catatan kejujuran: MySQL mendukung row locking, tetapi PHPUnit di sini
// menjalankan skenario secara sekuensial dalam satu koneksi. Tes di bawah
// memvalidasi INVARIANT bisnis (kapasitas tidak terlampaui, tidak ada
// duplikat, status final tidak berubah) dan strategi penguncian lewat
// perilaku yang dapat diamati. Uji konkurensi nyata (dua proses paralel)
// belum dilakukan — perlu verifikasi manual/staging (lihat docs/panduan.md).

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\HasilSpk;
use App\Models\KebutuhanGuru;
use App\Models\RekomendasiSwap;
use App\Models\Sekolah;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RegressionAuditTest extends TestCase
{
    use RefreshDatabase;

    private int $seq = 500;

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

    // ============ SPK: batasan akses ============

    public function test_pemda_ditolak_membuka_daftar_spk_dan_swap(): void
    {
        $pemda = $this->buatUser('pemda');

        $this->actingAs($pemda)->get('/spk')->assertForbidden();
        $this->actingAs($pemda)->get('/swap')->assertForbidden();
        // Dashboard ringkasan tetap boleh
        $this->actingAs($pemda)->get('/pemda')->assertOk();
    }

    public function test_peran_tak_berwenang_ditolak_di_endpoint_aksi(): void
    {
        $guru = $this->buatUser('guru');
        $kabid = $this->buatUser('kabid_ptk');

        // Guru tidak boleh generate / menyetujui
        $this->actingAs($guru)->post('/spk/generate')->assertForbidden();
        $this->actingAs($guru)->post('/swap/generate')->assertForbidden();

        // Kabid tidak boleh menyetujui (hanya kadis)
        $asal = $this->buatSekolah(1.0, 100.3);
        $this->buatGuru($asal);
        $tujuan = $this->buatSekolah(1.0, 100.05);
        KebutuhanGuru::create(['sekolah_id' => $tujuan->id, 'mapel' => 'Matematika', 'jumlah' => 1]);
        $this->actingAs($kabid)->post('/spk/generate');
        $rec = HasilSpk::firstOrFail();
        $this->actingAs($kabid)->post("/spk/{$rec->id}/setujui")->assertForbidden();
        $this->assertEquals('rekomendasi', $rec->fresh()->status);
    }

    // ============ SPK: invariant kapasitas ============

    public function test_generate_tidak_melebihi_kapasitas_formasi(): void
    {
        $kabid = $this->buatUser('kabid_ptk');

        // Dua guru layak, tetapi formasi hanya 1
        $this->buatGuru($this->buatSekolah(1.0, 100.3));
        $this->buatGuru($this->buatSekolah(1.0, 100.4));
        $tujuan = $this->buatSekolah(1.0, 100.05);
        KebutuhanGuru::create(['sekolah_id' => $tujuan->id, 'mapel' => 'Matematika', 'jumlah' => 1]);

        $this->actingAs($kabid)->post('/spk/generate');

        $aktif = HasilSpk::where('status', 'rekomendasi')
            ->where('sekolah_tujuan_id', $tujuan->id)->count();
        $this->assertLessThanOrEqual(1, $aktif,
            'Rekomendasi aktif tidak boleh melebihi formasi yang tersedia.');
    }

    public function test_generate_kedua_tidak_mendobel_rekomendasi(): void
    {
        $kabid = $this->buatUser('kabid_ptk');
        $this->buatGuru($this->buatSekolah(1.0, 100.3));
        $tujuan = $this->buatSekolah(1.0, 100.05);
        KebutuhanGuru::create(['sekolah_id' => $tujuan->id, 'mapel' => 'Matematika', 'jumlah' => 2]);

        $this->actingAs($kabid)->post('/spk/generate');
        $jumlahPertama = HasilSpk::count();

        $this->actingAs($kabid)->post('/spk/generate');
        $this->assertEquals($jumlahPertama, HasilSpk::count(),
            'Generate ulang tidak boleh membuat rekomendasi duplikat.');
    }

    public function test_setujui_menghabiskan_formasi_lalu_generate_tidak_menambah(): void
    {
        $kabid = $this->buatUser('kabid_ptk');
        $kadis = $this->buatUser('kadis');
        $guru = $this->buatGuru($this->buatSekolah(1.0, 100.3));
        $tujuan = $this->buatSekolah(1.0, 100.05);
        KebutuhanGuru::create(['sekolah_id' => $tujuan->id, 'mapel' => 'Matematika', 'jumlah' => 1]);

        $this->actingAs($kabid)->post('/spk/generate');
        $rec = HasilSpk::firstOrFail();
        $this->actingAs($kadis)->post("/spk/{$rec->id}/setujui");

        $this->assertSame(0, KebutuhanGuru::sisaDenganAlokasi(['Matematika'])[$tujuan->id.'|matematika'] ?? 0,
            'Formasi harus habis setelah persetujuan (guru bertugas di tujuan).');

        // Guru baru yang layak muncul, tetapi formasi sudah habis
        $this->buatGuru($this->buatSekolah(1.0, 100.35));
        $this->actingAs($kabid)->post('/spk/generate');

        $this->assertEquals(0, HasilSpk::where('status', 'rekomendasi')
            ->where('sekolah_tujuan_id', $tujuan->id)->count());
    }

    public function test_setujui_ganda_tidak_menggandakan_mutasi(): void
    {
        $kabid = $this->buatUser('kabid_ptk');
        $kadis = $this->buatUser('kadis');
        $guru = $this->buatGuru($this->buatSekolah(1.0, 100.3));
        $tujuan = $this->buatSekolah(1.0, 100.05);
        KebutuhanGuru::create(['sekolah_id' => $tujuan->id, 'mapel' => 'Matematika', 'jumlah' => 1]);

        $this->actingAs($kabid)->post('/spk/generate');
        $rec = HasilSpk::firstOrFail();

        $this->actingAs($kadis)->post("/spk/{$rec->id}/setujui");
        // Upaya kedua (simulasi klik ganda / dua pejabat bersamaan)
        $this->actingAs($kadis)->post("/spk/{$rec->id}/setujui");

        $this->assertEquals('disetujui', $rec->fresh()->status);
        $this->assertEquals(1, KebutuhanGuru::where('sekolah_id', $tujuan->id)->value('jumlah'),
            'Target kebutuhan tidak berubah oleh persetujuan.');
        $this->assertSame(0, KebutuhanGuru::sisaDenganAlokasi(['Matematika'])[$tujuan->id.'|matematika'] ?? 0,
            'Formasi hanya boleh terisi satu kali.');
    }

    // ============ Swap: invariant & akses ============

    public function test_swap_guru_tak_terlibat_ditolak_menyetujui(): void
    {
        $kabid = $this->buatUser('kabid_ptk');
        $sa = $this->buatSekolah(1.0, 100.8);
        $a = $this->buatGuru($sa, ['domisili_lng' => 100.0]);
        $sb = $this->buatSekolah(1.0, 100.1);
        $b = $this->buatGuru($sb, ['domisili_lng' => 100.9]);

        $this->actingAs($kabid)->post('/swap/generate');
        $swap = RekomendasiSwap::firstOrFail();

        $akunGuru = $this->buatUser('guru', ['guru_id' => $a->id]);
        $this->actingAs($akunGuru)->post("/swap/{$swap->id}/setujui")->assertForbidden();
        $this->actingAs($akunGuru)->post("/swap/{$swap->id}/tolak")->assertForbidden();
        $this->assertEquals('menunggu', $swap->fresh()->status);
    }

    public function test_generate_swap_tidak_membuat_pasangan_tumpang_tindih(): void
    {
        $kabid = $this->buatUser('kabid_ptk');
        // A cocok win-win dengan B maupun C — hanya satu yang boleh aktif
        $sa = $this->buatSekolah(1.0, 100.8);
        $a = $this->buatGuru($sa, ['domisili_lng' => 100.0]);
        $sb = $this->buatSekolah(1.0, 100.1);
        $b = $this->buatGuru($sb, ['domisili_lng' => 100.9]);
        $sc = $this->buatSekolah(1.0, 100.15);
        $c = $this->buatGuru($sc, ['domisili_lng' => 100.85]);

        $this->actingAs($kabid)->post('/swap/generate');

        $aktifA = RekomendasiSwap::where('status', 'menunggu')
            ->where(fn ($q) => $q->where('guru_a_id', $a->id)->orWhere('guru_b_id', $a->id))
            ->count();
        $this->assertLessThanOrEqual(1, $aktifA,
            'Satu guru tidak boleh ada di dua swap aktif.');
    }

    public function test_swap_setujui_lalu_tolak_tidak_berubah(): void
    {
        $kabid = $this->buatUser('kabid_ptk');
        $kadis = $this->buatUser('kadis');
        $sa = $this->buatSekolah(1.0, 100.8);
        $a = $this->buatGuru($sa, ['domisili_lng' => 100.0]);
        $sb = $this->buatSekolah(1.0, 100.1);
        $b = $this->buatGuru($sb, ['domisili_lng' => 100.9]);

        $this->actingAs($kabid)->post('/swap/generate');
        $swap = RekomendasiSwap::firstOrFail();

        $this->actingAs($kadis)->post("/swap/{$swap->id}/setujui");
        $this->actingAs($kadis)->post("/swap/{$swap->id}/tolak", ['catatan_admin' => 'uji']);

        $swap = $swap->fresh();
        $this->assertEquals('disetujui', $swap->status);
        $this->assertEquals($sb->id, $a->fresh()->sekolah_id);
        $this->assertEquals($sa->id, $b->fresh()->sekolah_id);
    }
}
