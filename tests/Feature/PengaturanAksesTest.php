<?php

// Tes Pengaturan Akses (23 Okt 2026): matriks peran × menu menjadi gerbang
// halaman modul — bawaan sama dengan aturan peran lama; menutup akses
// menolak rute (403) + menyembunyikan menu; membuka akses di luar bawaan
// benar-benar membuka halaman; aksi di dalam modul tetap dijaga middleware
// peran rinci; administrator tidak bisa terkunci dari halaman pengaturan.

namespace Tests\Feature;

use App\Livewire\PengaturanAkses;
use App\Models\ActivityLog;
use App\Models\AksesMenu as ModelAksesMenu;
use App\Models\Guru;
use App\Models\Sekolah;
use App\Models\User;
use App\Models\UsulanPenempatan;
use App\Services\AksesMenu;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class PengaturanAksesTest extends TestCase
{
    use RefreshDatabase;

    private function buatUser(string $email, string $role, array $tambahan = []): User
    {
        return User::create(array_merge([
            'name' => $role, 'email' => $email,
            'password' => Hash::make('rahasia'), 'role' => $role,
        ], $tambahan));
    }

    public function test_bawaan_sama_dengan_aturan_peran_lama(): void
    {
        $this->assertFalse(AksesMenu::boleh('guru', 'pengguna'));
        $this->assertTrue(AksesMenu::boleh('kabid_ptk', 'pengguna'));
        $this->assertFalse(AksesMenu::boleh('pemda', 'guru'));
        $this->assertTrue(AksesMenu::boleh('pemda', 'wilayah-desa'));
        $this->assertTrue(AksesMenu::boleh('pemda', 'distribusi'));
        $this->assertFalse(AksesMenu::boleh('guru', 'distribusi'));
        $this->assertTrue(AksesMenu::boleh('kadis', 'kualitas-data'));
        $this->assertFalse(AksesMenu::boleh('ketua_pgri', 'simulasi'));

        // Tanpa perubahan matriks, akses halaman sama seperti sebelumnya.
        $guru = $this->buatUser('guru@t.local', 'guru');
        $pemda = $this->buatUser('pemda@t.local', 'pemda');
        $this->actingAs($guru)->get('/distribusi')->assertForbidden();
        $this->actingAs($guru)->get('/guru')->assertOk();
        $this->actingAs($pemda)->get('/guru')->assertForbidden();
        $this->actingAs($pemda)->get('/distribusi')->assertOk();
    }

    public function test_menutup_dan_membuka_akses_berlaku_ke_rute_dan_menu(): void
    {
        $kepsek = $this->buatUser('kepsek@t.local', 'kepala_sekolah');
        $guru = $this->buatUser('guru@t.local', 'guru');

        $this->actingAs($kepsek)->get('/sekolah')->assertOk()
            ->assertSee(route('sekolah.index'), false);

        // Tutup Data Sekolah untuk kepala sekolah: rute 403 + menu hilang.
        AksesMenu::atur('kepala_sekolah', 'sekolah', false);
        $this->actingAs($kepsek)->get('/sekolah')->assertForbidden();
        $this->actingAs($kepsek)->get('/guru')->assertOk()
            ->assertDontSee(route('sekolah.index'), false);

        // Buka kembali: normal lagi.
        AksesMenu::atur('kepala_sekolah', 'sekolah', true);
        $this->actingAs($kepsek)->get('/sekolah')->assertOk();

        // Membuka di luar bawaan benar-benar membuka halaman (Distribusi).
        $this->actingAs($guru)->get('/distribusi')->assertForbidden();
        AksesMenu::atur('guru', 'distribusi', true);
        $this->actingAs($guru)->get('/distribusi')->assertOk();
    }

    public function test_aksi_di_dalam_modul_tetap_dijaga_peran_rinci(): void
    {
        $sekolah = Sekolah::create(['npsn' => 'NPSNUJI', 'nama' => 'Sekolah Uji',
            'alamat' => 'Jl Tes', 'kecamatan' => 'Panyabungan', 'lat' => 0.86, 'lng' => 99.56]);
        $guruModel = Guru::create(['nip' => 'NIPUJI', 'nama' => 'Guru Uji', 'mapel' => 'Matematika',
            'jenjang' => 'SD', 'domisili_alamat' => 'Jl Uji', 'kecamatan' => 'Panyabungan',
            'sekolah_id' => $sekolah->id, 'domisili_lat' => 0.86, 'domisili_lng' => 99.56,
            'sekolah_saat_ini' => 'Sekolah Uji']);
        $kabid = $this->buatUser('kabid@t.local', 'kabid_ptk');
        $guru = $this->buatUser('guru@t.local', 'guru', ['guru_id' => $guruModel->id]);
        $usulan = UsulanPenempatan::create(['guru_id' => $guruModel->id, 'sekolah_id' => $sekolah->id,
            'alasan' => 'Alasan cukup panjang untuk validasi.', 'status' => 'diajukan',
            'diajukan_oleh' => $kabid->id]);

        // Menu Usulan terbuka untuk guru (bawaan), tetapi menyetujui tetap
        // khusus kadis — matriks tidak menaikkan hak aksi.
        $this->actingAs($guru)->get('/usulan')->assertOk();
        $this->actingAs($guru)->post('/usulan/'.$usulan->id.'/setujui')->assertForbidden();

        // Menutup modul Usulan untuk guru menutup juga halaman + aksinya.
        AksesMenu::atur('guru', 'usulan', false);
        $this->actingAs($guru)->get('/usulan')->assertForbidden();
    }

    public function test_halaman_matriks_khusus_admin_dan_toggle_tercatat(): void
    {
        $kabid = $this->buatUser('kabid@t.local', 'kabid_ptk');
        $kadis = $this->buatUser('kadis@t.local', 'kadis');

        $this->actingAs($kadis)->get('/pengaturan/akses')->assertForbidden();
        $this->actingAs($kabid)->get('/pengaturan/akses')->assertOk()
            ->assertSee('Pengaturan Akses');

        Livewire::actingAs($kabid)->test(PengaturanAkses::class)
            ->call('toggle', 'distribusi', 'guru')
            ->assertSet('pesan', fn ($p) => str_contains($p, 'Distribusi'));
        $this->assertTrue(AksesMenu::boleh('guru', 'distribusi'));
        $this->assertDatabaseHas('akses_menu', ['role' => 'guru', 'kunci' => 'distribusi', 'boleh' => true]);
        $this->assertTrue(ActivityLog::where('aksi', 'akses-menu.ubah')->exists());

        // Saklar modul administrasi untuk peran lain tidak berlaku.
        Livewire::actingAs($kabid)->test(PengaturanAkses::class)
            ->call('toggle', 'pengguna', 'guru');
        $this->assertFalse(AksesMenu::boleh('guru', 'pengguna'));
        $this->assertDatabaseMissing('akses_menu', ['role' => 'guru', 'kunci' => 'pengguna']);

        // Reset mengembalikan bawaan.
        Livewire::actingAs($kabid)->test(PengaturanAkses::class)->call('resetBawaan');
        $this->assertSame(0, ModelAksesMenu::count());
        $this->assertFalse(AksesMenu::boleh('guru', 'distribusi'));
    }

    public function test_administrator_tidak_bisa_terkunci_dari_pengaturan_akses(): void
    {
        $kabid = $this->buatUser('kabid@t.local', 'kabid_ptk');

        AksesMenu::atur('kabid_ptk', 'pengaturan-akses', false);
        $this->assertTrue(AksesMenu::boleh('kabid_ptk', 'pengaturan-akses'));
        $this->actingAs($kabid)->get('/pengaturan/akses')->assertOk();

        Livewire::actingAs($kabid)->test(PengaturanAkses::class)
            ->call('toggle', 'pengaturan-akses', 'kabid_ptk');
        $this->assertTrue(AksesMenu::boleh('kabid_ptk', 'pengaturan-akses'));
    }
}
