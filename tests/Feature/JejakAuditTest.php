<?php

// Tes jejak audit (G) dan riwayat transisi status (D): pencatatan aksi,
// alasan wajib menolak, redaksi rahasia, dan akses halaman audit.

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Guru;
use App\Models\HasilSpk;
use App\Models\RekomendasiSwap;
use App\Models\Sekolah;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class JejakAuditTest extends TestCase
{
    use RefreshDatabase;

    private int $seq = 900;

    private function buatUser(string $role, array $attr = []): User
    {
        return User::create(array_merge([
            'name' => 'User '.$role.' '.++$this->seq,
            'email' => $role.$this->seq.'@test.local',
            'password' => Hash::make('rahasia123'),
            'role' => $role,
        ], $attr));
    }

    private function buatSekolah(): Sekolah
    {
        return Sekolah::create([
            'npsn' => '9'.str_pad((string) ++$this->seq, 7, '0', STR_PAD_LEFT),
            'nama' => 'Sekolah '.$this->seq,
            'alamat' => 'Jl. Tes '.$this->seq,
            'kecamatan' => 'Panyabungan',
            'lat' => 1.0,
            'lng' => 100.0,
        ]);
    }

    public function test_tambah_guru_dicatat_di_audit_tanpa_nip(): void
    {
        $kabid = $this->buatUser('kabid_ptk');
        $sekolah = $this->buatSekolah();

        $this->actingAs($kabid)->post('/guru', [
            'nip' => 'nip'.$this->seq,
            'nama' => 'Guru Audit',
            'mapel' => 'Matematika',
            'jenjang' => 'SD',
            'domisili_alamat' => 'Domisili',
            'kecamatan' => 'Panyabungan',
            'sekolah_id' => $sekolah->id,
            'domisili_lat' => 1.0,
            'domisili_lng' => 100.0,
            'sekolah_saat_ini' => $sekolah->nama,
        ])->assertRedirect('/guru');

        $this->assertDatabaseHas('activity_logs', [
            'aksi' => 'guru.tambah',
            'user_id' => $kabid->id,
            'hasil' => 'sukses',
        ]);
        $log = ActivityLog::where('aksi', 'guru.tambah')->firstOrFail();
        $this->assertArrayNotHasKey('nip', $log->perubahan ?? []);
    }

    public function test_redaksi_audit_mencakup_kunci_sensitif_bersarang(): void
    {
        ActivityLog::catat('uji.redaksi', null, 'sukses', [
            'ringkasan' => ['nip' => 'RAHASIA', 'username' => 'NIP-RAHASIA', 'password' => 'RAHASIA'],
            'status_sesudah' => 'ditolak',
        ]);
        $log = ActivityLog::where('aksi', 'uji.redaksi')->firstOrFail();
        $this->assertSame('[REDACTED]', $log->perubahan['ringkasan']['nip']);
        $this->assertSame('[REDACTED]', $log->perubahan['ringkasan']['username']);
        $this->assertSame('[REDACTED]', $log->perubahan['ringkasan']['password']);
        $this->assertSame('ditolak', $log->perubahan['status_sesudah']);
    }

    public function test_tolak_spk_wajib_alasan_dan_dicatat(): void
    {
        $kabid = $this->buatUser('kabid_ptk');
        $guru = Guru::create([
            'nip' => 'nip'.$this->seq, 'nama' => 'Guru '.$this->seq,
            'mapel' => 'Matematika', 'jenjang' => 'SD',
            'domisili_alamat' => 'D', 'kecamatan' => 'Panyabungan',
            'sekolah_id' => $this->buatSekolah()->id,
            'domisili_lat' => 1.0, 'domisili_lng' => 100.0,
            'sekolah_saat_ini' => 'S',
        ]);
        $spk = HasilSpk::create([
            'guru_id' => $guru->id,
            'sekolah_asal_id' => $guru->sekolah_id,
            'sekolah_tujuan_id' => $this->buatSekolah()->id,
            'jarak_km' => 20, 'jarak_sekarang_km' => 30, 'hemat_km' => 10,
            'status' => 'rekomendasi',
        ]);

        // Tanpa alasan: ditolak validasi
        $this->actingAs($kabid)->post("/spk/{$spk->id}/tolak", [])
            ->assertSessionHasErrors('catatan_admin');
        $this->assertEquals('rekomendasi', $spk->fresh()->status);

        // Dengan alasan: berhasil + tercatat
        $this->actingAs($kabid)->post("/spk/{$spk->id}/tolak", [
            'catatan_admin' => 'Formasi sudah terisi.',
        ])->assertRedirect();

        $this->assertEquals('ditolak', $spk->fresh()->status);
        $log = ActivityLog::where('aksi', 'spk.tolak')->firstOrFail();
        $this->assertEquals('sukses', $log->hasil);
        $this->assertEquals('rekomendasi', $log->perubahan['status_sebelum']);
        $this->assertEquals('ditolak', $log->perubahan['status_sesudah']);
        $this->assertEquals('Formasi sudah terisi.', $log->perubahan['alasan']);
    }

    public function test_tolak_swap_wajib_alasan(): void
    {
        $kabid = $this->buatUser('kabid_ptk');
        $sekolah = $this->buatSekolah();
        $guruA = Guru::create([
            'nip' => 'nipA'.$this->seq, 'nama' => 'Guru A '.$this->seq,
            'mapel' => 'IPA', 'jenjang' => 'SMP',
            'domisili_alamat' => 'D', 'kecamatan' => 'Panyabungan',
            'sekolah_id' => $sekolah->id,
            'domisili_lat' => 1.0, 'domisili_lng' => 100.0,
            'sekolah_saat_ini' => $sekolah->nama,
        ]);
        $guruB = Guru::create([
            'nip' => 'nipB'.$this->seq, 'nama' => 'Guru B '.$this->seq,
            'mapel' => 'IPA', 'jenjang' => 'SMP',
            'domisili_alamat' => 'D', 'kecamatan' => 'Panyabungan',
            'sekolah_id' => $sekolah->id,
            'domisili_lat' => 1.0, 'domisili_lng' => 100.0,
            'sekolah_saat_ini' => $sekolah->nama,
        ]);
        $swap = RekomendasiSwap::create([
            'guru_a_id' => $guruA->id, 'guru_b_id' => $guruB->id,
            'sekolah_tujuan_a_id' => $sekolah->id,
            'sekolah_tujuan_b_id' => $sekolah->id,
            'jarak_a_sekarang_km' => 30, 'jarak_b_sekarang_km' => 30,
            'jarak_a_usulan_km' => 10, 'jarak_b_usulan_km' => 10,
            'hemat_a_km' => 20, 'hemat_b_km' => 20, 'total_hemat_km' => 40,
            'status' => 'menunggu',
        ]);

        $this->actingAs($kabid)->post("/swap/{$swap->id}/tolak", [])
            ->assertSessionHasErrors('catatan_admin');
        $this->assertEquals('menunggu', $swap->fresh()->status);
    }

    public function test_redaksi_menghapus_kunci_rahasia(): void
    {
        ActivityLog::catat('test.aksi', null, 'sukses', [
            'password' => 'rahasia',
            'token' => 'abc123',
            'nama' => 'Budi',
        ]);

        $log = ActivityLog::where('aksi', 'test.aksi')->firstOrFail();
        $this->assertSame('[REDACTED]', $log->perubahan['password']);
        $this->assertSame('[REDACTED]', $log->perubahan['token']);
        $this->assertEquals('Budi', $log->perubahan['nama']);
    }

    public function test_halaman_audit_hanya_untuk_kabid(): void
    {
        $kabid = $this->buatUser('kabid_ptk');
        $guru = $this->buatUser('guru');

        $this->actingAs($kabid)->get('/aktivitas')->assertOk();
        $this->actingAs($guru)->get('/aktivitas')->assertForbidden();
    }

    public function test_riwayat_entitas_menampilkan_transisi(): void
    {
        $user = $this->buatUser('kabid_ptk');
        $sekolah = $this->buatSekolah();

        $this->actingAs($user)->post('/sekolah', [
            'npsn' => '9'.str_pad((string) ++$this->seq, 7, '0', STR_PAD_LEFT),
            'nama' => 'Sekolah Riwayat',
            'alamat' => 'Jl. R',
            'kecamatan' => 'Panyabungan',
            'lat' => 1.0,
            'lng' => 100.0,
        ]);

        $dibuat = Sekolah::where('nama', 'Sekolah Riwayat')->firstOrFail();
        $riwayat = ActivityLog::riwayat($dibuat)->get();

        $this->assertTrue($riwayat->isNotEmpty());
        $this->assertEquals('sekolah.tambah', $riwayat->first()->aksi);
    }
}
