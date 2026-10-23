<?php

// Impor massal guru P3K (perintah artisan guru:impor-p3k): sekolah baru
// ditambahkan, guru lama diganti (--ganti-semua), akun NIP dibuatkan.

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\Sekolah;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class ImporGuruP3kTest extends TestCase
{
    use RefreshDatabase;

    private function berkasContoh(): string
    {
        $wb = new Spreadsheet;

        $baru = $wb->getActiveSheet();
        $baru->setTitle('Sekolah Baru');
        $baru->fromArray([
            ['NO', 'NAMA SEKOLAH (baru, untuk SPK)', 'JENJANG', 'KECAMATAN', 'JUMLAH GURU', 'CATATAN'],
            [1, 'SD NEGERI 999 BARU', 'SD', 'Panyabungan', 1, ''],
        ]);

        $siap = $wb->createSheet();
        $siap->setTitle('Siap Impor');
        $siap->fromArray([
            ['NO', 'NIP', 'NAMA', 'MAPEL', 'JENJANG', 'SEKOLAH (SPK)', 'NPSN', 'KECAMATAN',
                'STATUS_KEPEGAWAIAN', 'TEMPAT_LAHIR', 'TANGGAL_LAHIR',
                'PENDIDIKAN TERAKHIR (perkiraan)', 'JABATAN_ASLI', 'NO_SK', 'CATATAN'],
            [1, '199001012020121001', 'GURU SATU, S.Pd', 'Guru Kelas', 'SD', 'SD NEGERI 999 BARU',
                '', 'Panyabungan', 'PPPK PARUH WAKTU', 'Panyabungan', '1990-01-01', 'S1 - PGSD',
                'GURU KELAS-SD', '813/1/K/2025', ''],
            [2, '', 'GURU DUA, S.Pd', 'Agama Islam', 'SD', 'SD NEGERI 999 BARU',
                '', 'Panyabungan', 'PPPK', 'Panyabungan', '1992-02-02', 'S1 - Pendidikan Agama Islam',
                'GURU AGAMA ISLAM', '813/2/K/2025', 'NIP kosong'],
        ]);

        $path = tempnam(sys_get_temp_dir(), 'impor-p3k-').'.xlsx';
        (new Xlsx($wb))->save($path);

        return $path;
    }

    public function test_impor_mengganti_guru_lama_dan_membuat_akun(): void
    {
        // Data contoh lama yang harus terhapus
        $lama = Guru::create([
            'nip' => '111111111111111111', 'nama' => 'GURU CONTOH LAMA',
            'mapel' => 'IPA', 'jenjang' => 'SMP', 'status_pernikahan' => 'belum_kawin',
            'sudah_sertifikasi' => false, 'sekolah_saat_ini' => 'SMP LAMA',
        ]);
        User::factory()->create(['role' => 'guru', 'guru_id' => $lama->id, 'username' => $lama->nip]);

        $this->artisan('guru:impor-p3k', [
            'berkas' => $this->berkasContoh(),
            '--ganti-semua' => true,
        ])->assertSuccessful();

        // Sekolah baru tercipta (NPSN menyusul → null)
        $sekolah = Sekolah::where('nama', 'SD NEGERI 999 BARU')->firstOrFail();
        $this->assertNull($sekolah->npsn);

        // Guru lama hilang; dua guru baru masuk
        $this->assertSame(0, Guru::where('nama', 'GURU CONTOH LAMA')->count());
        $this->assertSame(2, Guru::count());
        $satu = Guru::where('nama', 'GURU SATU, S.Pd')->firstOrFail();
        $this->assertSame('pppk_paruh_waktu', $satu->status_kepegawaian);
        $this->assertSame('S1 - PGSD', $satu->pendidikan_terakhir);
        $this->assertSame($sekolah->id, $satu->sekolah_id);
        $this->assertSame('1990-01-01', $satu->tanggal_lahir->format('Y-m-d'));

        // Akun hanya untuk guru ber-NIP; akun guru lama ikut terhapus
        $this->assertSame(1, User::where('role', 'guru')->count());
        $this->assertNotNull(User::where('username', '199001012020121001')->first());
        $dua = Guru::where('nama', 'GURU DUA, S.Pd')->firstOrFail();
        $this->assertNull($dua->nip);
    }

    public function test_impor_tanpa_ganti_semua_melewati_nip_ganda(): void
    {
        Guru::create([
            'nip' => '199001012020121001', 'nama' => 'SUDAH ADA',
            'mapel' => 'Guru Kelas', 'jenjang' => 'SD', 'status_pernikahan' => 'belum_kawin',
            'sudah_sertifikasi' => false, 'sekolah_saat_ini' => 'SD X',
        ]);

        $this->artisan('guru:impor-p3k', ['berkas' => $this->berkasContoh()])
            ->assertSuccessful();

        // Baris ber-NIP sama dilewati; baris tanpa NIP tetap masuk
        $this->assertSame(1, Guru::where('nama', 'SUDAH ADA')->count());
        $this->assertSame(1, Guru::where('nama', 'GURU DUA, S.Pd')->count());
        $this->assertSame(2, Guru::count());
    }
}
