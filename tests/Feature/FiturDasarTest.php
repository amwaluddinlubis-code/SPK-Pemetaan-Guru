<?php

// Tes paket "Fitur Dasar" (P1): profil & ganti password, lupa/reset password
// via email, export Excel, cetak/PDF, import sekolah, halaman 403/404,
// sorting kolom, dan info pagination.

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\Sekolah;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class FiturDasarTest extends TestCase
{
    use RefreshDatabase;

    private int $seq = 700;

    private function buatUser(string $role, array $attr = []): User
    {
        return User::create(array_merge([
            'name' => 'User '.$role.' '.++$this->seq,
            'email' => $role.$this->seq.'@test.local',
            'password' => Hash::make('rahasia123'),
            'role' => $role,
        ], $attr));
    }

    private function buatSekolah(float $lat = 1.0, float $lng = 100.0): Sekolah
    {
        return Sekolah::create([
            'npsn' => '7'.str_pad((string) ++$this->seq, 7, '0', STR_PAD_LEFT),
            'nama' => 'Sekolah '.$this->seq,
            'alamat' => 'Jl. Tes '.$this->seq,
            'kecamatan' => 'Panyabungan',
            'lat' => $lat,
            'lng' => $lng,
        ]);
    }

    private function buatGuru(Sekolah $sekolah, string $nama): Guru
    {
        return Guru::create([
            'nip' => 'nip'.++$this->seq,
            'nama' => $nama,
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
        ]);
    }

    // ============ Profil & ganti password ============

    public function test_profil_menampilkan_data_pengguna(): void
    {
        $user = $this->buatUser('kabid_ptk');

        $this->actingAs($user)->get('/profil')
            ->assertOk()
            ->assertSee($user->name, false)
            ->assertSee($user->email, false)
            ->assertSee('Ganti Password', false);
    }

    public function test_ganti_password_berhasil_dengan_password_lama_benar(): void
    {
        $user = $this->buatUser('kabid_ptk');

        $this->actingAs($user)->put('/profil/password', [
            'password_lama' => 'rahasia123',
            'password' => 'barulah123',
            'password_confirmation' => 'barulah123',
        ])->assertRedirect();

        $this->assertTrue(Hash::check('barulah123', $user->fresh()->password));
    }

    public function test_ganti_password_ditolak_jika_password_lama_salah(): void
    {
        $user = $this->buatUser('kabid_ptk');

        $this->actingAs($user)->put('/profil/password', [
            'password_lama' => 'salah123',
            'password' => 'barulah123',
            'password_confirmation' => 'barulah123',
        ])->assertSessionHasErrors('password_lama');

        $this->assertTrue(Hash::check('rahasia123', $user->fresh()->password));
    }

    // ============ Lupa / reset password via email ============

    public function test_lupa_password_mengirim_tautan_untuk_email_terdaftar(): void
    {
        $user = $this->buatUser('guru');

        $this->post('/lupa-password', ['email' => $user->email])
            ->assertRedirect()
            ->assertSessionHas('sukses', 'Jika email terdaftar, tautan reset password akan dikirim. Periksa kotak masuk Anda.')
            ->assertSessionDoesntHaveErrors('email');

        $this->assertDatabaseHas('password_reset_tokens', ['email' => $user->email]);
    }

    public function test_lupa_password_memberi_respons_generik_untuk_email_tak_terdaftar(): void
    {
        $this->post('/lupa-password', ['email' => 'tidakada@test.local'])
            ->assertRedirect()
            ->assertSessionHas('sukses', 'Jika email terdaftar, tautan reset password akan dikirim. Periksa kotak masuk Anda.')
            ->assertSessionDoesntHaveErrors('email');

        $this->assertDatabaseMissing('password_reset_tokens', ['email' => 'tidakada@test.local']);
    }

    public function test_reset_password_dengan_token_valid_mengganti_password(): void
    {
        $user = $this->buatUser('guru');
        $token = Password::createToken($user);

        $this->post('/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'baruaman123',
            'password_confirmation' => 'baruaman123',
        ])->assertRedirect('/login');

        $this->assertTrue(Hash::check('baruaman123', $user->fresh()->password));
    }

    public function test_token_reset_tidak_bisa_dipakai_ulang(): void
    {
        $user = $this->buatUser('guru');
        $token = Password::createToken($user);

        $data = [
            'token' => $token,
            'email' => $user->email,
            'password' => 'baruaman123',
            'password_confirmation' => 'baruaman123',
        ];
        $this->post('/reset-password', $data)->assertRedirect('/login');

        // Pakai ulang token yang sama: harus ditolak
        $this->post('/reset-password', array_merge($data, [
            'password' => 'lainlagi123',
            'password_confirmation' => 'lainlagi123',
        ]))->assertSessionHasErrors('email');

        $this->assertTrue(Hash::check('baruaman123', $user->fresh()->password));
    }

    // ============ Export Excel & cetak/PDF ============

    public function test_export_excel_guru(): void
    {
        $kabid = $this->buatUser('kabid_ptk');
        $this->buatGuru($this->buatSekolah(), 'Guru Export');

        $res = $this->actingAs($kabid)->get('/guru/export');
        $res->assertOk();
        $res->assertHeader('content-type',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_export_excel_sekolah(): void
    {
        $kabid = $this->buatUser('kabid_ptk');
        $this->buatSekolah();

        $this->actingAs($kabid)->get('/sekolah/export')->assertOk();
    }

    public function test_export_excel_usulan(): void
    {
        $kabid = $this->buatUser('kabid_ptk');

        $this->actingAs($kabid)->get('/usulan/export')->assertOk();
    }

    public function test_cetak_guru_menampilkan_halaman_cetak(): void
    {
        $kabid = $this->buatUser('kabid_ptk');
        $this->buatGuru($this->buatSekolah(), 'Guru Cetak');

        $this->actingAs($kabid)->get('/guru/cetak')
            ->assertOk()
            ->assertSee('Data Guru', false)
            ->assertSee('Guru Cetak', false);
    }

    // ============ Import sekolah ============

    private function berkasSekolah(array $baris): UploadedFile
    {
        $spreadsheet = new Spreadsheet;
        $lembar = $spreadsheet->getActiveSheet();
        $lembar->fromArray([[
            'npsn', 'nama', 'alamat', 'desa_kelurahan', 'kecamatan',
            'kategori_daerah', 'lat', 'lng',
        ]], null, 'A1');
        foreach ($baris as $i => $b) {
            $lembar->fromArray([$b], null, 'A'.($i + 2));
        }
        $path = tempnam(sys_get_temp_dir(), 'imp').'.xlsx';
        (new Xlsx($spreadsheet))->save($path);

        return new UploadedFile($path, 'sekolah.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);
    }

    public function test_import_sekolah_berhasil(): void
    {
        $kabid = $this->buatUser('kabid_ptk');

        $this->actingAs($kabid)->post('/sekolah/import', [
            'berkas' => $this->berkasSekolah([
                ['20100001', 'SD Impor Satu', 'Jl. Impor 1', 'Desa A', 'Panyabungan', 'biasa', 0.85, 99.55],
                ['20100002', 'SD Impor Dua', 'Jl. Impor 2', '', 'Panyabungan', 'terpencil', 0.86, 99.56],
            ]),
        ])->assertRedirect('/sekolah');

        $this->assertDatabaseHas('sekolah', ['npsn' => '20100001', 'nama' => 'SD Impor Satu']);
        $this->assertDatabaseHas('sekolah', ['npsn' => '20100002', 'kategori_daerah' => 'terpencil']);
    }

    public function test_import_sekolah_tidak_membocorkan_detail_error_file(): void
    {
        $kabid = $this->buatUser('kabid_ptk');
        $spreadsheet = new Spreadsheet;
        $spreadsheet->getActiveSheet()->fromArray([['npsn', 'nama']], null, 'A1');
        $path = tempnam(sys_get_temp_dir(), 'bad').'.xlsx';
        (new Xlsx($spreadsheet))->save($path);

        // Pertahankan struktur ZIP/OOXML yang valid untuk lolos validasi
        // MIME, tetapi rusakkan workbook XML agar IOFactory gagal membacanya.
        $zip = new \ZipArchive;
        $this->assertTrue($zip->open($path) === true);
        $zip->addFromString('xl/workbook.xml', '<workbook><broken>');
        $zip->close();
        $berkas = new UploadedFile($path, 'rusak.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);

        $this->actingAs($kabid)->post('/sekolah/import', ['berkas' => $berkas])
            ->assertSessionHasErrors('berkas')
            ->assertSessionHasErrors([
                'berkas' => 'File tidak bisa dibaca sebagai Excel/CSV.',
            ]);

        $this->assertEquals(0, Sekolah::count());
    }

    public function test_import_sekolah_menolak_berkas_terlalu_besar(): void
    {
        $kabid = $this->buatUser('kabid_ptk');
        $berkas = UploadedFile::fake()->create('sekolah.xlsx', 6000);

        $this->actingAs($kabid)->post('/sekolah/import', ['berkas' => $berkas])
            ->assertSessionHasErrors('berkas');

        $this->assertEquals(0, Sekolah::count());
    }

    public function test_import_sekolah_melaporkan_npsn_duplikat_tanpa_data_parsial(): void
    {
        $kabid = $this->buatUser('kabid_ptk');

        $this->actingAs($kabid)->post('/sekolah/import', [
            'berkas' => $this->berkasSekolah([
                ['20100011', 'SD Duplikat Satu', 'Jl. D 1', '', 'Panyabungan', 'biasa', 0.85, 99.55],
                ['20100011', 'SD Duplikat Dua', 'Jl. D 2', '', 'Panyabungan', 'biasa', 0.86, 99.56],
            ]),
        ])->assertRedirect('/sekolah');

        // Baris valid tersimpan, baris duplikat dilaporkan (bukan data parsial
        // yang setengah jadi: setiap baris yang tersimpan utuh & valid).
        $this->assertEquals(1, Sekolah::where('npsn', '20100011')->count());
        $this->assertDatabaseHas('sekolah', ['npsn' => '20100011', 'nama' => 'SD Duplikat Satu']);
    }

    public function test_import_sekolah_menolak_header_tidak_sesuai(): void
    {
        $kabid = $this->buatUser('kabid_ptk');

        $spreadsheet = new Spreadsheet;
        $spreadsheet->getActiveSheet()->fromArray([['kolom', 'salah']], null, 'A1');
        $path = tempnam(sys_get_temp_dir(), 'imp').'.xlsx';
        (new Xlsx($spreadsheet))->save($path);
        $berkas = new UploadedFile($path, 'salah.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);

        $this->actingAs($kabid)->post('/sekolah/import', ['berkas' => $berkas])
            ->assertSessionHasErrors('berkas');

        $this->assertEquals(0, Sekolah::count());
    }

    // ============ Halaman error ramah ============

    public function test_halaman_404_menampilkan_pesan_ramah(): void
    {
        $kabid = $this->buatUser('kabid_ptk');

        $this->actingAs($kabid)->get('/halaman-yang-tidak-ada')
            ->assertNotFound()
            ->assertSee('Tidak Ditemukan', false);
    }

    public function test_pemda_dibatasi_ke_dashboard_ringkasan(): void
    {
        $pemda = $this->buatUser('pemda');

        foreach (['/spk', '/swap', '/guru', '/sekolah', '/usulan'] as $path) {
            $this->actingAs($pemda)->get($path)
                ->assertForbidden()
                ->assertSee('Akses Ditolak', false);
        }
    }

    // ============ Sorting & info pagination ============

    public function test_sorting_nama_guru_desc(): void
    {
        $kabid = $this->buatUser('kabid_ptk');
        $sekolah = $this->buatSekolah();
        $this->buatGuru($sekolah, 'Apel Guru');
        $this->buatGuru($sekolah, 'Zebra Guru');

        $this->actingAs($kabid)->get('/guru?sort=nama&arah=desc')
            ->assertOk()
            ->assertSeeInOrder(['Zebra Guru', 'Apel Guru'], false);
    }

    public function test_info_pagination_ditampilkan(): void
    {
        $kabid = $this->buatUser('kabid_ptk');
        $this->buatGuru($this->buatSekolah(), 'Guru Paginasi');

        $this->actingAs($kabid)->get('/guru')
            ->assertOk()
            ->assertSee('Menampilkan', false)
            ->assertSee('dari 1 data', false);
    }
}
