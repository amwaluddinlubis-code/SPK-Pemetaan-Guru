<?php

namespace Tests\Feature;

use App\Models\Sekolah;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ImportRombelTest extends TestCase
{
    use RefreshDatabase;

    private int $seq = 500;

    private function buatUser(string $role): User
    {
        return User::create([
            'name' => 'User '.$role.' '.++$this->seq,
            'email' => $role.$this->seq.'@test.local',
            'password' => Hash::make('rahasia123'),
            'role' => $role,
        ]);
    }

    private function buatSekolah(string $npsn, array $attr = []): Sekolah
    {
        return Sekolah::create(array_merge([
            'npsn' => $npsn,
            'nama' => 'Sekolah '.$npsn,
            'alamat' => 'Jl. Tes '.$npsn,
            'kecamatan' => 'Panyabungan',
            'lat' => 1.0,
            'lng' => 100.0,
        ], $attr));
    }

    /**
     * Bangun file CSV gaya ekspor Dapodik (delimiter ';').
     *
     * @param  array<int, array<int, string>>  $baris
     */
    private function csvDapodik(array $baris): UploadedFile
    {
        $header = ['No.', 'NPSN', 'Nama Sekolah', 'Jenjang', 'Bentuk Pendidikan', 'Status',
            'Sinkronisasi', 'PD', 'Rombel', 'Guru', 'Tendik', 'R. Kelas', 'Perpus', 'Toilet'];
        $stream = fopen('php://temp', 'r+');
        fputcsv($stream, $header, ';');
        foreach ($baris as $b) {
            fputcsv($stream, $b, ';');
        }
        rewind($stream);
        $isi = stream_get_contents($stream);
        fclose($stream);

        return UploadedFile::fake()->createWithContent('sekolah-Kec._Panyabungan.csv', $isi);
    }

    public function test_import_rombel_memperbarui_sekolah_yang_cocok(): void
    {
        $kabid = $this->buatUser('kabid_ptk');
        $s1 = $this->buatSekolah('10207950');
        $s2 = $this->buatSekolah('10208060');

        $berkas = $this->csvDapodik([
            ['1', '10207950', 'SD NEGERI 107 SOPOBATU', 'Dikdas', 'SD', 'Negeri', 'Sudah Sinkron', '86', '6', '16', '1', '4', '0', '0'],
            ['2', '10208060', 'SMP MUHAMMADIYAH 31', 'Dikdas', 'SMP', 'Swasta', 'Sudah Sinkron', '315', '10', '17', '1', '11', '1', '0'],
            // NPSN tidak dikenal → dilaporkan, bukan dibuat
            ['3', '99999999', 'SEKOLAH ASING', 'Dikdas', 'SD', 'Swasta', 'Sudah Sinkron', '10', '2', '3', '0', '2', '0', '0'],
            // Rombel tidak valid → dilaporkan
            ['4', '10207950', 'SD NEGERI 107 SOPOBATU', 'Dikdas', 'SD', 'Negeri', 'Sudah Sinkron', '86', 'abc', '16', '1', '4', '0', '0'],
            // NPSN kosong → dilaporkan
            ['5', '', 'TANPA NPSN', 'Dikdas', 'SD', 'Negeri', 'Sudah Sinkron', '10', '2', '3', '0', '2', '0', '0'],
        ]);

        $respon = $this->actingAs($kabid)->post('/sekolah/import-rombel', ['berkas' => $berkas]);

        $respon->assertRedirect(route('sekolah.index'));
        $respon->assertSessionHas('sukses', 'Import rombel selesai: 2 sekolah diperbarui.');
        $this->assertSame(6, $s1->fresh()->rombel);
        $this->assertSame(10, $s2->fresh()->rombel);
        $this->assertNull(Sekolah::where('npsn', '99999999')->first());

        $gagal = session('import_gagal');
        $this->assertCount(3, $gagal);
    }

    public function test_import_rombel_menolak_header_salah(): void
    {
        $kabid = $this->buatUser('kabid_ptk');
        $berkas = UploadedFile::fake()->createWithContent(
            'salah.csv',
            "nama;alamat\nSekolah X;Jl. X\n"
        );

        $this->actingAs($kabid)->post('/sekolah/import-rombel', ['berkas' => $berkas])
            ->assertSessionHasErrors('berkas');
    }

    public function test_import_rombel_hanya_kabid(): void
    {
        $kepsek = $this->buatUser('kepala_sekolah');
        $berkas = $this->csvDapodik([]);

        $this->actingAs($kepsek)->get('/sekolah/import-rombel')->assertForbidden();
        $this->actingAs($kepsek)->post('/sekolah/import-rombel', ['berkas' => $berkas])->assertForbidden();
    }
}
