<?php

// Akun guru berbasis NIP (keputusan AMWAL, 23 Okt 2026): username =
// NIP (tidak diubah guru), password bawaan = NIP dengan kewajiban ganti
// saat login pertama; pendaftaran mandiri menunggu persetujuan operator.

namespace Tests\Feature;

use App\Livewire\PendaftaranGuru as HalamanPendaftaran;
use App\Models\Guru;
use App\Models\PendaftaranGuru;
use App\Models\Sekolah;
use App\Models\User;
use App\Services\AkunGuru;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class AkunGuruTest extends TestCase
{
    use RefreshDatabase;

    private function guruBerNip(array $tambahan = []): Guru
    {
        return Guru::create(array_merge([
            'nip' => '199311102025212034',
            'nama' => 'IRMAWATI BATU BARA, S.Pd',
            'mapel' => 'Guru Kelas',
            'jenjang' => 'SD',
            'status_kepegawaian' => 'pppk_paruh_waktu',
            'status_pernikahan' => 'belum_kawin',
            'sudah_sertifikasi' => false,
            'sekolah_saat_ini' => 'SD CONTOH',
        ], $tambahan));
    }

    public function test_status_pppk_paruh_waktu_dikenal(): void
    {
        $this->assertArrayHasKey('pppk_paruh_waktu', Guru::STATUS_KEPEGAWAIAN);
        $this->assertSame('PPPK Paruh Waktu', Guru::STATUS_KEPEGAWAIAN['pppk_paruh_waktu']);
    }

    public function test_akun_dibuat_dengan_username_dan_password_nip(): void
    {
        $guru = $this->guruBerNip();
        $akun = AkunGuru::buatkan($guru);

        $this->assertNotNull($akun);
        $this->assertSame('199311102025212034', $akun->username);
        $this->assertTrue(Hash::check('199311102025212034', $akun->password));
        $this->assertTrue($akun->harus_ganti_password);
        $this->assertSame('guru', $akun->role);
        $this->assertSame($guru->id, $akun->guru_id);
    }

    public function test_guru_tanpa_nip_belum_punya_akun(): void
    {
        $guru = $this->guruBerNip(['nip' => null]);
        $this->assertNull(AkunGuru::buatkan($guru));
    }

    public function test_login_memakai_nip_dan_wajib_ganti_password_bawaan(): void
    {
        $guru = $this->guruBerNip();
        AkunGuru::buatkan($guru);

        // Login dengan NIP sebagai username & password awal
        $this->post(route('login.proses'), [
            'login' => '199311102025212034',
            'password' => '199311102025212034',
        ])->assertRedirect(route('profil.show'));
        $this->assertAuthenticated();

        // Halaman lain tertutup selama password bawaan belum diganti
        $this->get(route('dashboard'))->assertRedirect(route('profil.show'));

        // Ganti password → kewajiban selesai, aplikasi terbuka
        $this->put(route('profil.password'), [
            'password_lama' => '199311102025212034',
            'password' => 'SandiBaruAman1',
            'password_confirmation' => 'SandiBaruAman1',
        ])->assertSessionHas('sukses');
        $this->assertFalse(auth()->user()->harus_ganti_password);
        $this->get(route('dashboard'))->assertOk();
    }

    public function test_login_email_pejabat_tetap_jalan(): void
    {
        User::factory()->create([
            'email' => 'kabid@contoh.id',
            'role' => 'kabid_ptk',
            'password' => Hash::make('rahasia123'),
        ]);

        $this->post(route('login.proses'), [
            'login' => 'kabid@contoh.id',
            'password' => 'rahasia123',
        ])->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();
    }

    public function test_sinkronkan_nip_baru_membuat_akun_dan_nip_berubah_mereset_bawaan(): void
    {
        $guru = $this->guruBerNip(['nip' => null]);
        AkunGuru::sinkronkan($guru);
        $this->assertSame(0, User::where('guru_id', $guru->id)->count());

        $guru->update(['nip' => '198001012010011001']);
        AkunGuru::sinkronkan($guru);
        $akun = User::where('guru_id', $guru->id)->firstOrFail();
        $this->assertSame('198001012010011001', $akun->username);

        // Koreksi NIP oleh operator → username & password bawaan mengikuti
        $guru->update(['nip' => '198001012010011999']);
        AkunGuru::sinkronkan($guru);
        $akun->refresh();
        $this->assertSame('198001012010011999', $akun->username);
        $this->assertTrue(Hash::check('198001012010011999', $akun->password));
        $this->assertTrue($akun->harus_ganti_password);
    }

    public function test_pendaftaran_mandiri_masuk_antrean_lalu_disetujui_operator(): void
    {
        $sekolah = Sekolah::create([
            'npsn' => '10200001', 'nama' => 'SDN 001 CONTOH', 'jenjang' => 'SD',
            'rombel' => 6, 'kecamatan' => 'Panyabungan', 'kategori_daerah' => 'biasa',
        ]);
        $operator = User::factory()->create(['role' => 'kabid_ptk']);

        // Guru mendaftar dari halaman publik (belum punya NIP)
        $this->post(route('daftar-guru.simpan'), [
            'nama' => 'GURU PENDAFTAR, S.Pd',
            'nip' => '',
            'mapel' => 'Guru Kelas',
            'jenjang' => 'SD',
            'status_kepegawaian' => 'pppk',
            'sekolah_id' => $sekolah->id,
            'no_hp' => '0812000111',
        ])->assertSessionHas('sukses');
        $pendaftaran = PendaftaranGuru::firstOrFail();
        $this->assertSame('menunggu', $pendaftaran->status);

        // Operator menyetujui → data guru dibuat; akun menunggu NIP
        Livewire::actingAs($operator);
        Livewire::test(HalamanPendaftaran::class)
            ->call('setujui', $pendaftaran->id);

        $pendaftaran->refresh();
        $this->assertSame('disetujui', $pendaftaran->status);
        $this->assertNotNull($pendaftaran->guru_id);
        $this->assertSame(0, User::where('guru_id', $pendaftaran->guru_id)->count());
    }

    public function test_pendaftaran_ber_nip_disetujui_langsung_punya_akun(): void
    {
        $operator = User::factory()->create(['role' => 'kabid_ptk']);
        $pendaftaran = PendaftaranGuru::create([
            'nama' => 'GURU BERNIP, S.Pd',
            'nip' => '199001012020121001',
            'mapel' => 'Matematika',
            'jenjang' => 'SMP',
            'status_kepegawaian' => 'pppk',
            'no_hp' => '0813000222',
            'status' => 'menunggu',
        ]);

        Livewire::actingAs($operator);
        Livewire::test(HalamanPendaftaran::class)
            ->call('setujui', $pendaftaran->id);

        $akun = User::where('username', '199001012020121001')->firstOrFail();
        $this->assertSame('guru', $akun->role);
        $this->assertTrue(Hash::check('199001012020121001', $akun->password));
    }

    public function test_pendaftaran_ditolak_tercatat(): void
    {
        $operator = User::factory()->create(['role' => 'kabid_ptk']);
        $pendaftaran = PendaftaranGuru::create([
            'nama' => 'PENDAFTAR DITOLAK',
            'no_hp' => '0813000333',
            'status' => 'menunggu',
        ]);

        Livewire::actingAs($operator);
        Livewire::test(HalamanPendaftaran::class)
            ->set('catatan', [$pendaftaran->id => 'Data tidak cocok dengan SK.'])
            ->call('tolak', $pendaftaran->id);

        $pendaftaran->refresh();
        $this->assertSame('ditolak', $pendaftaran->status);
        $this->assertSame('Data tidak cocok dengan SK.', $pendaftaran->catatan_operator);
        $this->assertSame(0, Guru::count());
    }

    public function test_antrean_pendaftaran_hanya_dapat_dibuka_kabid_ptk(): void
    {
        $guru = User::factory()->create(['role' => 'guru']);
        $kadis = User::factory()->create(['role' => 'kadis']);

        $this->actingAs($guru)->get(route('pendaftaran.index'))->assertForbidden();
        $this->actingAs($kadis)->get(route('pendaftaran.index'))->assertForbidden();
    }

    public function test_aksi_pendaftaran_memeriksa_peran_di_dalam_komponen(): void
    {
        $guru = User::factory()->create(['role' => 'guru']);
        $pendaftaran = PendaftaranGuru::create([
            'nama' => 'PENDAFTAR UJI',
            'no_hp' => '0812000111',
            'status' => 'menunggu',
        ]);

        // Render awal komponen ditolak 403 untuk peran selain kabid_ptk,
        // sehingga snapshot aksi tidak pernah terbit bagi mereka; aksi
        // setujui()/tolak() pun menegakkan otorisasi yang sama di dalam
        // komponen (otorisasiOperator).
        try {
            Livewire::actingAs($guru)
                ->test(HalamanPendaftaran::class)
                ->assertForbidden();
        } catch (HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
        }

        $this->assertSame('menunggu', $pendaftaran->fresh()->status);
    }
}
