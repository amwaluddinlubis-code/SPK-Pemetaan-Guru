<?php

// Tes agregat defisit/surplus (B): rumus, filter, privasi (tanpa PII), akses peran.

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\HasilSpk;
use App\Models\KebutuhanGuru;
use App\Models\Sekolah;
use App\Models\User;
use App\Services\AgregatKebutuhan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DistribusiTest extends TestCase
{
    use RefreshDatabase;

    private int $seq = 1100;

    private function buatUser(string $role): User
    {
        return User::create([
            'name' => 'User '.$role.' '.++$this->seq,
            'email' => $role.$this->seq.'@test.local',
            'password' => Hash::make('rahasia123'),
            'role' => $role,
        ]);
    }

    private function buatSekolah(string $kecamatan): Sekolah
    {
        return Sekolah::create([
            'npsn' => '1'.++$this->seq, 'nama' => 'S'.$this->seq, 'alamat' => 'A',
            'kecamatan' => $kecamatan, 'lat' => 1.0, 'lng' => 100.0,
        ]);
    }

    private function buatGuru(Sekolah $sekolah, string $mapel): Guru
    {
        return Guru::create([
            'nip' => 'NIP'.++$this->seq, 'nama' => 'Guru '.$this->seq,
            'mapel' => $mapel, 'jenjang' => 'SD',
            'domisili_alamat' => 'D', 'kecamatan' => $sekolah->kecamatan,
            'sekolah_id' => $sekolah->id, 'sekolah_saat_ini' => $sekolah->nama,
            'domisili_lat' => 1.0, 'domisili_lng' => 100.0,
        ]);
    }

    public function test_rumus_defisit_surplus_benar(): void
    {
        $kabid = $this->buatUser('kabid_ptk');
        $sekolah = $this->buatSekolah('Panyabungan');
        // 2 guru Matematika, kebutuhan 5 → defisit 3
        $this->buatGuru($sekolah, 'Matematika');
        $this->buatGuru($sekolah, 'Matematika');
        KebutuhanGuru::create(['sekolah_id' => $sekolah->id, 'mapel' => 'Matematika', 'jumlah' => 5]);
        // 3 guru IPA, kebutuhan 1 → surplus 2
        $this->buatGuru($sekolah, 'IPA');
        $this->buatGuru($sekolah, 'IPA');
        $this->buatGuru($sekolah, 'IPA');
        KebutuhanGuru::create(['sekolah_id' => $sekolah->id, 'mapel' => 'IPA', 'jumlah' => 1]);

        $svc = new AgregatKebutuhan;
        $data = collect($svc->perKecamatanMapel('Panyabungan'))->keyBy('mapel');

        $this->assertEquals(3, $data['Matematika']['defisit']);
        $this->assertEquals(0, $data['Matematika']['surplus']);
        $this->assertEquals(0, $data['IPA']['defisit']);
        $this->assertEquals(2, $data['IPA']['surplus']);
    }

    public function test_guru_dalam_proses_dihitung_di_kecamatan_sekolah_asal(): void
    {
        $sekolah = $this->buatSekolah('Panyabungan');
        $guru = $this->buatGuru($sekolah, 'Matematika');
        $guru->update(['kecamatan' => 'Siabu']); // kecamatan domisili berbeda dari sekolah tugas
        HasilSpk::create([
            'guru_id' => $guru->id,
            'sekolah_asal_id' => $sekolah->id,
            'sekolah_tujuan_id' => $this->buatSekolah('Siabu')->id,
            'jarak_km' => 10, 'jarak_sekarang_km' => 20, 'hemat_km' => 10,
            'status' => 'rekomendasi',
        ]);

        $data = collect((new AgregatKebutuhan)->perKecamatanMapel())->keyBy('kecamatan');
        $this->assertSame(1, $data['Panyabungan']['dalam_proses']);
        $this->assertArrayNotHasKey('Siabu', $data->filter(fn ($r) => $r['dalam_proses'] > 0)->all());
    }

    public function test_halaman_tidak_memuat_identitas_guru(): void
    {
        $pemda = $this->buatUser('pemda');
        $sekolah = $this->buatSekolah('Panyabungan');
        $guru = $this->buatGuru($sekolah, 'Matematika');

        $res = $this->actingAs($pemda)->get('/distribusi');
        $res->assertOk();
        // Agregat saja: nama guru & NIP tidak boleh muncul
        $res->assertDontSee($guru->nama, false);
        $res->assertDontSee($guru->nip, false);
        $res->assertSee('Defisit', false);
    }

    public function test_akses_peran(): void
    {
        $guru = $this->buatUser('guru');
        $pemda = $this->buatUser('pemda');
        $kadis = $this->buatUser('kadis');

        $this->actingAs($guru)->get('/distribusi')->assertForbidden();
        $this->actingAs($pemda)->get('/distribusi')->assertOk();
        $this->actingAs($kadis)->get('/distribusi')->assertOk();
    }

    public function test_filter_kecamatan_bekerja(): void
    {
        $kabid = $this->buatUser('kabid_ptk');
        $s1 = $this->buatSekolah('Panyabungan');
        $s2 = $this->buatSekolah('Siabu');
        $this->buatGuru($s1, 'Matematika');
        $this->buatGuru($s2, 'Matematika');

        $res = $this->actingAs($kabid)->get('/distribusi?kecamatan=Siabu');
        $res->assertOk();
        $html = $res->getContent();
        $this->assertStringContainsString('<td>Siabu</td>', $html);
        $this->assertStringNotContainsString('<td>Panyabungan</td>', $html);
    }
}
