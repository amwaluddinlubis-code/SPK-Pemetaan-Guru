<?php

// Tes integrasi Generate SPK hibrida Greedy + SAW + TOPSIS:
// peringkat alternatif memakai skor multikriteria dan tersimpan sebagai
// jejak audit, sedangkan alokasi formasi tetap greedy (satu slot tidak
// dijanjikan ke dua guru; prioritas proses guru berkeluarga).

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\HasilSpk;
use App\Models\KebutuhanGuru;
use App\Models\Sekolah;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SpkSawTopsisTest extends TestCase
{
    use RefreshDatabase;

    private int $seq = 3300;

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
            'npsn' => '3'.++$this->seq,
            'nama' => 'Sekolah '.$this->seq,
            'alamat' => 'Alamat',
            'kecamatan' => 'Panyabungan',
            'kategori_daerah' => 'biasa',
            'lat' => 0.0,
            'lng' => $lng,
            'koordinat_terverifikasi_lat' => 0.0,
            'koordinat_terverifikasi_lng' => $lng,
        ], $attr));
    }

    private function buatGuru(Sekolah $asal, array $attr = []): Guru
    {
        return Guru::create(array_merge([
            'nip' => 'NIP'.++$this->seq,
            'nama' => 'Guru '.$this->seq,
            'mapel' => 'Matematika',
            'jenjang' => 'SD',
            'domisili_alamat' => 'Domisili',
            'kecamatan' => 'Panyabungan',
            'status_pernikahan' => 'belum_kawin',
            'sekolah_id' => $asal->id,
            'sekolah_saat_ini' => $asal->nama,
            'domisili_lat' => 0.0,
            'domisili_lng' => 100.0,
        ], $attr));
    }

    private function butuh(Sekolah $sekolah, int $jumlah): void
    {
        KebutuhanGuru::create([
            'sekolah_id' => $sekolah->id,
            'mapel' => 'Matematika',
            'jumlah' => $jumlah,
        ]);
    }

    public function test_generate_memeringkat_dengan_saw_topsis_dan_menyimpan_jejak_skor(): void
    {
        $kabid = $this->buatUser('kabid_ptk');
        $asal = $this->buatSekolah(100.30);
        $guru = $this->buatGuru($asal);

        // A lebih dekat, tetapi formasinya 1 dan daerah biasa.
        $dekat = $this->buatSekolah(100.05, ['kategori_daerah' => 'biasa']);
        $this->butuh($dekat, 1);
        // B sedikit lebih jauh, tetapi formasi 5 dan sangat terpencil:
        // kriteria kebutuhan + pemerataan harus mengangkatnya ke peringkat 1.
        $prioritas = $this->buatSekolah(100.06, ['kategori_daerah' => 'sangat_terpencil']);
        $this->butuh($prioritas, 5);

        $this->actingAs($kabid)->post('/spk/generate')->assertRedirect();

        $recs = HasilSpk::where('guru_id', $guru->id)->orderBy('id')->get();
        $this->assertCount(2, $recs);
        $this->assertEquals($prioritas->id, $recs[0]->sekolah_tujuan_id);
        $this->assertEquals($dekat->id, $recs[1]->sekolah_tujuan_id);
        $this->assertGreaterThan((float) $recs[1]->skor, (float) $recs[0]->skor);
        $this->assertNotNull($recs[0]->skor_saw);
        $this->assertNotNull($recs[0]->skor_topsis);
        $this->assertEqualsWithDelta(
            ((float) $recs[0]->skor_saw + (float) $recs[0]->skor_topsis) / 2,
            (float) $recs[0]->skor,
            0.0001
        );
        $this->assertSame(5, $recs[0]->detail_skor['kriteria']['sisa_formasi']);
        $this->assertSame(3, $recs[0]->detail_skor['kriteria']['prioritas_daerah']);
        $this->assertSame(1, $recs[0]->detail_skor['peringkat_ke']);
        $this->assertSame(2, $recs[1]->detail_skor['peringkat_ke']);

        // Telaahan staf menampilkan skor hibrida beserta komponennya.
        $this->actingAs($kabid)->get('/spk/'.$recs[0]->id.'/telaahan')
            ->assertOk()
            ->assertSee('Skor peringkat hibrida')
            ->assertSee('SAW')
            ->assertSee('TOPSIS')
            ->assertDontSee('belum tersedia — rekomendasi ini dibuat sebelum metode');
    }

    public function test_alokasi_tetap_greedy_dan_prioritas_keluarga_memegang_slot_tunggal(): void
    {
        $kabid = $this->buatUser('kabid_ptk');
        $asalA = $this->buatSekolah(100.30);
        $asalB = $this->buatSekolah(100.30);
        $belumKawin = $this->buatGuru($asalA, ['status_pernikahan' => 'belum_kawin']);
        $berkeluarga = $this->buatGuru($asalB, ['status_pernikahan' => 'kawin']);
        $tujuan = $this->buatSekolah(100.05);
        $this->butuh($tujuan, 1);

        $this->actingAs($kabid)->post('/spk/generate')->assertRedirect();

        $recs = HasilSpk::where('sekolah_tujuan_id', $tujuan->id)->get();
        $this->assertCount(1, $recs);
        $this->assertEquals($berkeluarga->id, $recs->first()->guru_id);
        $this->assertNotSame($belumKawin->id, $recs->first()->guru_id);
    }
}
