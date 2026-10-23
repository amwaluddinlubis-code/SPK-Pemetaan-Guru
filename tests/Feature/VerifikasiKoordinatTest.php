<?php

// Verifikasi koordinat sekolah: status/petugas/sumber tercatat;
// otorisasi per peran (kepala sekolah hanya sekolahnya; guru tidak
// boleh); SPK & generate hanya memakai sekolah terverifikasi;
// perubahan koordinat membatalkan status terverifikasi.

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\HasilSpk;
use App\Models\KebutuhanGuru;
use App\Models\Sekolah;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class VerifikasiKoordinatTest extends TestCase
{
    use RefreshDatabase;

    private int $seq = 0;

    private function buatUser(string $role, ?int $sekolahId = null, ?int $guruId = null): User
    {
        return User::create([
            'name' => 'User '.$role.' '.++$this->seq,
            'email' => $role.$this->seq.'@test.local',
            'password' => Hash::make('rahasia123'),
            'role' => $role,
            'sekolah_id' => $sekolahId,
            'guru_id' => $guruId,
        ]);
    }

    private function buatSekolah(string $nama, float $lng, bool $terverifikasi = false): Sekolah
    {
        return Sekolah::create([
            'npsn' => '8'.++$this->seq,
            'nama' => $nama,
            'alamat' => 'Alamat',
            'kecamatan' => 'Panyabungan',
            'kategori_daerah' => 'biasa',
            'jenjang' => 'SMP',
            'lat' => 0.0,
            'lng' => $lng,
        ] + ($terverifikasi ? [
            'koordinat_terverifikasi_lat' => 0.0,
            'koordinat_terverifikasi_lng' => $lng,
        ] : []));
    }

    public function test_sekolah_baru_dan_hasil_seed_belum_terverifikasi(): void
    {
        $s = Sekolah::create([
            'npsn' => '8999', 'nama' => 'SMP SEED', 'alamat' => 'A',
            'kecamatan' => 'Panyabungan', 'kategori_daerah' => 'biasa',
            'jenjang' => 'SMP', 'lat' => 0.5, 'lng' => 99.5,
        ]);

        $this->assertFalse($s->fresh()->koordinatTerverifikasi());
        $this->assertSame('Koordinat belum terverifikasi', $s->labelStatusKoordinat());
    }

    public function test_kabid_memverifikasi_dan_metadata_tercatat(): void
    {
        $kabid = $this->buatUser('kabid_ptk');
        $s = $this->buatSekolah('SMP TUJUAN', 100.10);

        $this->actingAs($kabid)
            ->post(route('sekolah.verifikasi-koordinat', $s), [
                'koordinat_sumber' => 'gps_lapangan',
                'koordinat_catatan' => 'Dicek di lokasi',
            ])
            ->assertSessionHas('sukses');

        $s->refresh();
        $this->assertTrue($s->koordinatTerverifikasi());
        $this->assertNotNull($s->koordinat_terverifikasi_pada);
        $this->assertSame($kabid->id, $s->koordinat_terverifikasi_oleh);
        $this->assertSame('gps_lapangan', $s->koordinat_sumber);
        $this->assertSame('Dicek di lokasi', $s->koordinat_verifikasi_catatan);
    }

    public function test_kadis_boleh_memverifikasi(): void
    {
        $kadis = $this->buatUser('kadis');
        $s = $this->buatSekolah('SMP KADIS', 100.10);

        $this->actingAs($kadis)
            ->post(route('sekolah.verifikasi-koordinat', $s), ['koordinat_sumber' => 'peta_dapodik'])
            ->assertSessionHas('sukses');
        $this->assertTrue($s->fresh()->koordinatTerverifikasi());
    }

    public function test_kepala_sekolah_hanya_sekolahnya_sendiri(): void
    {
        $milik = $this->buatSekolah('SMP MILIK', 100.10);
        $lain = $this->buatSekolah('SMP LAIN', 100.20);
        $kepsek = $this->buatUser('kepala_sekolah', $milik->id);

        $this->actingAs($kepsek)
            ->post(route('sekolah.verifikasi-koordinat', $milik), ['koordinat_sumber' => 'konfirmasi_sekolah'])
            ->assertSessionHas('sukses');
        $this->assertTrue($milik->fresh()->koordinatTerverifikasi());

        $this->actingAs($kepsek)
            ->post(route('sekolah.verifikasi-koordinat', $lain), ['koordinat_sumber' => 'konfirmasi_sekolah'])
            ->assertForbidden();
        $this->assertFalse($lain->fresh()->koordinatTerverifikasi());
    }

    public function test_guru_tidak_dapat_memvalidasi(): void
    {
        $s = $this->buatSekolah('SMP GURU', 100.10);
        $guru = Guru::create([
            'nip' => 'NIPV1', 'nama' => 'GURU V', 'mapel' => 'IPA', 'jenjang' => 'SMP',
            'status_pernikahan' => 'belum_kawin', 'sudah_sertifikasi' => false,
            'sekolah_id' => $s->id, 'sekolah_saat_ini' => $s->nama,
        ]);
        $akunGuru = $this->buatUser('guru', $s->id, $guru->id);

        $this->actingAs($akunGuru)
            ->post(route('sekolah.verifikasi-koordinat', $s), ['koordinat_sumber' => 'gps_lapangan'])
            ->assertForbidden();
        $this->assertFalse($s->fresh()->koordinatTerverifikasi());
    }

    public function test_verifikasi_massal_khusus_kabid(): void
    {
        $kabid = $this->buatUser('kabid_ptk');
        $kadis = $this->buatUser('kadis');
        $a = $this->buatSekolah('SMP A', 100.10);
        $b = $this->buatSekolah('SMP B', 100.20, true);

        $this->actingAs($kadis)
            ->post(route('sekolah.verifikasi-massal'), ['koordinat_sumber' => 'konfirmasi_massal'])
            ->assertForbidden();

        $this->actingAs($kabid)
            ->post(route('sekolah.verifikasi-massal'), ['koordinat_sumber' => 'konfirmasi_massal'])
            ->assertSessionHas('sukses');

        $this->assertTrue($a->fresh()->koordinatTerverifikasi());
        $this->assertSame($kabid->id, $a->fresh()->koordinat_terverifikasi_oleh);
    }

    public function test_generate_mengabaikan_sekolah_belum_terverifikasi(): void
    {
        $kabid = $this->buatUser('kabid_ptk');
        $asal = $this->buatSekolah('SMP ASAL', 100.30, true);
        $belum = $this->buatSekolah('SMP BELUM VERIF', 100.10, false);
        KebutuhanGuru::create(['sekolah_id' => $belum->id, 'mapel' => 'Matematika', 'jumlah' => 1]);
        Guru::create([
            'nip' => 'NIPV2', 'nama' => 'GURU KANDIDAT', 'mapel' => 'Matematika', 'jenjang' => 'SMP',
            'domisili_alamat' => 'Domisili', 'kecamatan' => 'Panyabungan',
            'status_pernikahan' => 'belum_kawin', 'sudah_sertifikasi' => false,
            'sekolah_id' => $asal->id, 'sekolah_saat_ini' => $asal->nama,
            'domisili_lat' => 0.0, 'domisili_lng' => 100.0,
        ]);

        $this->actingAs($kabid)->post(route('spk.generate'))->assertRedirect();

        $this->assertSame(0, HasilSpk::count(),
            'Sekolah belum terverifikasi tidak boleh menjadi tujuan rekomendasi');

        // Setelah diverifikasi, generate yang sama menghasilkan rekomendasi.
        $belum->update(['koordinat_terverifikasi_lat' => $belum->lat, 'koordinat_terverifikasi_lng' => $belum->lng]);
        $this->actingAs($kabid)->post(route('spk.generate'))->assertRedirect();
        $this->assertSame(1, HasilSpk::where('sekolah_tujuan_id', $belum->id)->count());
    }

    public function test_mengubah_koordinat_membatalkan_verifikasi(): void
    {
        $kabid = $this->buatUser('kabid_ptk');
        $s = $this->buatSekolah('SMP UBAH', 100.10, true);
        $s->update([
            'koordinat_terverifikasi_pada' => now(),
            'koordinat_terverifikasi_oleh' => $kabid->id,
            'koordinat_sumber' => 'gps_lapangan',
        ]);
        KebutuhanGuru::create(['sekolah_id' => $s->id, 'mapel' => 'Matematika', 'jumlah' => 1]);

        $this->actingAs($kabid)->put(route('sekolah.update', $s), [
            'npsn' => $s->npsn,
            'nama' => $s->nama,
            'jenjang' => 'SMP',
            'rombel' => 6,
            'alamat' => 'Alamat',
            'kecamatan' => 'Panyabungan',
            'kategori_daerah' => 'biasa',
            'lat' => '0.5000',
            'lng' => '100.5000',
            'kebutuhan' => [['mapel' => 'Matematika', 'jumlah' => 1]],
        ])->assertRedirect(route('sekolah.index'));

        $s->refresh();
        $this->assertFalse($s->koordinatTerverifikasi(), 'Koordinat yang berubah harus diverifikasi ulang');
    }
}
