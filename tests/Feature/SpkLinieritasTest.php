<?php

// Linieritas fase 2 (Kepmen 222/O/2025): mesin SPK boleh mengisi formasi
// mapel linier kelompok dari bidang studi sertifikasi guru (tingkat 2),
// dengan faktor penyesuai skor 0,85 di belakang penempatan mapel persis
// (tingkat 1). Persetujuan tingkat 2 memutasi guru sekaligus menjadikan
// mapel yang diisi sebagai bidang tugas barunya.

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\HasilSpk;
use App\Models\KebutuhanGuru;
use App\Models\Sekolah;
use App\Models\User;
use App\Services\MesinSawTopsis;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SpkLinieritasTest extends TestCase
{
    use RefreshDatabase;

    private int $seq = 7700;

    private function buatUser(string $role): User
    {
        return User::create([
            'name' => 'User '.$role.' '.++$this->seq,
            'email' => $role.$this->seq.'@test.local',
            'password' => Hash::make('rahasia123'),
            'role' => $role,
        ]);
    }

    private function buatSekolah(float $lng, array $attr = []): Sekolah
    {
        return Sekolah::create(array_merge([
            'npsn' => '7'.++$this->seq,
            'nama' => 'Sekolah '.$this->seq,
            'alamat' => 'Alamat',
            'kecamatan' => 'Panyabungan',
            'kategori_daerah' => 'biasa',
            'jenjang' => 'SMP',
            'lat' => 0.0,
            'lng' => $lng,
        ], $attr));
    }

    private function buatGuru(Sekolah $asal, array $attr = []): Guru
    {
        return Guru::create(array_merge([
            'nip' => 'NIP'.++$this->seq,
            'nama' => 'Guru '.$this->seq,
            'mapel' => 'Matematika',
            'jenjang' => 'SMP',
            'domisili_alamat' => 'Domisili',
            'kecamatan' => 'Panyabungan',
            'status_pernikahan' => 'belum_kawin',
            'sekolah_id' => $asal->id,
            'sekolah_saat_ini' => $asal->nama,
            'domisili_lat' => 0.0,
            'domisili_lng' => 100.0,
        ], $attr));
    }

    private function butuh(Sekolah $sekolah, string $mapel, int $jumlah): void
    {
        KebutuhanGuru::create([
            'sekolah_id' => $sekolah->id,
            'mapel' => $mapel,
            'jumlah' => $jumlah,
        ]);
    }

    private function guruMatematikaBerserdik(Sekolah $asal, array $attr = []): Guru
    {
        return $this->buatGuru($asal, array_merge([
            'sudah_sertifikasi' => true,
            'mapel_sertifikasi' => 'Matematika',
            'bidang_studi_serdik' => 'Matematika',
            'kode_bidang_studi' => '094/180/318',
        ], $attr));
    }

    public function test_generate_membuat_rekomendasi_tingkat2_linier_kelompok(): void
    {
        $kabid = $this->buatUser('kabid_ptk');
        $asal = $this->buatSekolah(100.30);
        $guru = $this->guruMatematikaBerserdik($asal);
        $tujuan = $this->buatSekolah(100.10);
        $this->butuh($tujuan, 'Informatika', 1);

        $this->actingAs($kabid)->post('/spk/generate')->assertRedirect();

        $hasil = HasilSpk::where('guru_id', $guru->id)->first();
        $this->assertNotNull($hasil, 'Rekomendasi tingkat 2 harus terbentuk');
        $this->assertSame($tujuan->id, $hasil->sekolah_tujuan_id);
        $this->assertSame('Informatika', $hasil->mapel_tujuan);
        $this->assertSame(2, $hasil->tingkatLinieritas());
        // Skor akhir = skor hibrida mentah × 0,85 (jejak audit lengkap).
        $this->assertSame(2, $hasil->detail_skor['tingkat_linieritas']);
        $this->assertEqualsWithDelta(0.85, $hasil->detail_skor['faktor_tingkat'], 0.0001);
        $this->assertEqualsWithDelta(
            $hasil->detail_skor['skor_hybrid_mentah'] * 0.85,
            (float) $hasil->skor,
            0.001
        );
    }

    public function test_tingkat2_hanya_untuk_guru_bersertifikat_dengan_bidang_terisi(): void
    {
        $kabid = $this->buatUser('kabid_ptk');
        $asal = $this->buatSekolah(100.30);
        $belumSertifikasi = $this->buatGuru($asal);
        $bidangKosong = $this->buatGuru($asal, [
            'sudah_sertifikasi' => true,
            'mapel_sertifikasi' => 'Matematika',
        ]);
        $tujuan = $this->buatSekolah(100.10);
        $this->butuh($tujuan, 'Informatika', 2);

        $this->actingAs($kabid)->post('/spk/generate')->assertRedirect();

        $this->assertSame(0, HasilSpk::where('guru_id', $belumSertifikasi->id)->count());
        $this->assertSame(0, HasilSpk::where('guru_id', $bidangKosong->id)->count());
    }

    public function test_agama_tidak_pernah_diisi_lewat_tingkat2(): void
    {
        $kabid = $this->buatUser('kabid_ptk');
        $asal = $this->buatSekolah(100.30);
        $guru = $this->guruMatematikaBerserdik($asal);
        $tujuan = $this->buatSekolah(100.10);
        $this->butuh($tujuan, 'Agama', 1);

        $this->actingAs($kabid)->post('/spk/generate')->assertRedirect();

        $this->assertSame(0, HasilSpk::where('guru_id', $guru->id)->count());
    }

    public function test_sd_formasi_guru_kelas_dapat_diisi_tingkat2(): void
    {
        $kabid = $this->buatUser('kabid_ptk');
        $asal = $this->buatSekolah(100.30, ['jenjang' => 'SD']);
        $guru = $this->buatGuru($asal, [
            'mapel' => 'Bahasa Inggris',
            'jenjang' => 'SD',
            'sudah_sertifikasi' => true,
            'mapel_sertifikasi' => 'Biologi',
            'bidang_studi_serdik' => 'Biologi',
            'kode_bidang_studi' => '124/190/321',
        ]);
        $tujuan = $this->buatSekolah(100.10, ['jenjang' => 'SD']);
        $this->butuh($tujuan, 'Guru Kelas', 1);

        $this->actingAs($kabid)->post('/spk/generate')->assertRedirect();

        $hasil = HasilSpk::where('guru_id', $guru->id)->first();
        $this->assertNotNull($hasil);
        $this->assertSame('Guru Kelas', $hasil->mapel_tujuan);
        $this->assertSame(2, $hasil->tingkatLinieritas());
    }

    public function test_satu_slot_tingkat2_tidak_dijanjikan_ke_dua_guru(): void
    {
        $kabid = $this->buatUser('kabid_ptk');
        $asal = $this->buatSekolah(100.30);
        $this->guruMatematikaBerserdik($asal);
        $this->guruMatematikaBerserdik($asal);
        $tujuan = $this->buatSekolah(100.10);
        $this->butuh($tujuan, 'Informatika', 1);

        $this->actingAs($kabid)->post('/spk/generate')->assertRedirect();

        $this->assertSame(1, HasilSpk::where('mapel_tujuan', 'Informatika')->count());
    }

    public function test_mesin_saw_topsis_mengalikan_faktor_tingkat(): void
    {
        $dasar = [
            'hemat_km' => 10.0,
            'jarak_tujuan_km' => 5.0,
            'sisa_formasi' => 2,
            'prioritas_daerah' => 1,
        ];
        $hasil = (new MesinSawTopsis)->peringkat([
            array_merge($dasar, ['tingkat_linieritas' => 2, 'nama' => 'kelompok']),
            array_merge($dasar, ['tingkat_linieritas' => 1, 'nama' => 'persis']),
        ]);

        $this->assertSame('persis', $hasil[0]['nama']);
        $this->assertSame('kelompok', $hasil[1]['nama']);
        $this->assertEqualsWithDelta(
            $hasil[0]['skor_hybrid'],
            $hasil[1]['detail_skor']['skor_hybrid_mentah'],
            0.0001
        );
        $this->assertEqualsWithDelta(
            $hasil[1]['skor_hybrid'],
            $hasil[1]['detail_skor']['skor_hybrid_mentah'] * 0.85,
            0.0001
        );
    }

    public function test_setujui_tingkat2_memutasi_dan_mengubah_mapel_guru(): void
    {
        $kadis = $this->buatUser('kadis');
        $asal = $this->buatSekolah(100.30);
        $guru = $this->guruMatematikaBerserdik($asal);
        $tujuan = $this->buatSekolah(100.10);
        $this->butuh($tujuan, 'Informatika', 1);
        $spk = HasilSpk::create([
            'guru_id' => $guru->id,
            'sekolah_asal_id' => $asal->id,
            'sekolah_tujuan_id' => $tujuan->id,
            'mapel_tujuan' => 'Informatika',
            'tingkat_linieritas' => 2,
            'jarak_km' => 11.1,
            'jarak_sekarang_km' => 33.4,
            'hemat_km' => 22.3,
            'status' => 'rekomendasi',
        ]);

        $this->actingAs($kadis)->post('/spk/'.$spk->id.'/setujui')->assertRedirect();

        $guru->refresh();
        $this->assertSame($tujuan->id, $guru->sekolah_id);
        $this->assertSame('Informatika', $guru->mapel, 'Bidang tugas guru menjadi mapel yang diisi');
        $this->assertSame('Matematika', $guru->bidang_studi_serdik, 'Bidang studi serdik tidak berubah');
        $this->assertSame(0, (int) KebutuhanGuru::where('sekolah_id', $tujuan->id)
            ->where('mapel', 'Informatika')->value('jumlah'));
        $this->assertSame('disetujui', $spk->fresh()->status);
    }
}
