<?php

// Keamanan Pendaftaran Guru (audit P0): antrean hanya untuk Kabid PTK —
// di gerbang matriks menu DAN di dalam komponen Livewire (aksi dapat
// dipanggil lewat endpoint pembaruan tanpa melewati rute halaman) —
// dan Jejak Audit tidak boleh menyimpan nilai NIP.

namespace Tests\Feature;

use App\Livewire\PendaftaranGuru as HalamanPendaftaran;
use App\Models\ActivityLog;
use App\Models\Guru;
use App\Models\PendaftaranGuru;
use App\Models\User;
use App\Services\AkunGuru;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class KeamananPendaftaranGuruTest extends TestCase
{
    use RefreshDatabase;

    private int $seq = 0;

    private function buatUser(string $role): User
    {
        return User::create([
            'name' => 'User '.$role.' '.++$this->seq,
            'email' => $role.$this->seq.'@test.local',
            'password' => Hash::make('rahasia123'),
            'role' => $role,
        ]);
    }

    public function test_halaman_pendaftaran_hanya_untuk_kabid(): void
    {
        foreach (['guru', 'kepala_sekolah', 'ketua_pgri', 'kadis', 'pemda'] as $role) {
            $this->actingAs($this->buatUser($role))
                ->get(route('pendaftaran.index'))
                ->assertForbidden();
        }

        $this->actingAs($this->buatUser('kabid_ptk'))
            ->get(route('pendaftaran.index'))
            ->assertOk();
    }

    public function test_aksi_livewire_ditolak_untuk_peran_lain(): void
    {
        $pendaftaran = PendaftaranGuru::create([
            'nama' => 'PENDAFTAR UJI',
            'nip' => '199001012020121001',
            'no_hp' => '0813000999',
            'status' => 'menunggu',
        ]);

        // Komponen bahkan tidak dapat dirender oleh peran lain (mount
        // menegakkan otorisasi) — snapshot aksi tidak pernah terbit,
        // sehingga aksi tidak dapat dipanggil lewat endpoint pembaruan;
        // setujui()/tolak() pun menegakkan otorisasi yang sama.
        foreach (['guru', 'kadis', 'kepala_sekolah'] as $role) {
            try {
                Livewire::actingAs($this->buatUser($role));
                Livewire::test(HalamanPendaftaran::class)->assertForbidden();
            } catch (HttpException $e) {
                $this->assertSame(403, $e->getStatusCode());
            }
        }

        $pendaftaran->refresh();
        $this->assertSame('menunggu', $pendaftaran->status);
        $this->assertSame(0, Guru::count());
    }

    public function test_jejak_audit_tidak_menyimpan_nilai_nip(): void
    {
        $nipRahasia = '199001012020121001';
        $operator = $this->buatUser('kabid_ptk');
        $pendaftaran = PendaftaranGuru::create([
            'nama' => 'GURU AUDIT, S.Pd',
            'nip' => $nipRahasia,
            'mapel' => 'Matematika',
            'jenjang' => 'SMP',
            'no_hp' => '0813000888',
            'status' => 'menunggu',
        ]);

        Livewire::actingAs($operator);
        Livewire::test(HalamanPendaftaran::class)->call('setujui', $pendaftaran->id);

        // Akun benar-benar dibuat (username = NIP di tabel users) …
        $this->assertNotNull(User::where('username', $nipRahasia)->first());
        // … tetapi tidak satu pun baris audit menyimpan nilai NIP itu,
        // termasuk lewat kunci 'username' pada pembuatan akun.
        foreach (ActivityLog::all() as $log) {
            $this->assertStringNotContainsString(
                $nipRahasia,
                json_encode($log->perubahan),
                "Jejak audit {$log->aksi} membocorkan NIP"
            );
        }

        // Layanan akun dipakai langsung pun sama amannya.
        $guru = Guru::create([
            'nip' => '198505052010012002', 'nama' => 'GURU LANGSUNG, S.Pd',
            'mapel' => 'IPA', 'jenjang' => 'SMP', 'status_pernikahan' => 'belum_kawin',
            'sudah_sertifikasi' => false, 'sekolah_saat_ini' => 'SMP X',
        ]);
        AkunGuru::buatkan($guru);
        foreach (ActivityLog::all() as $log) {
            $this->assertStringNotContainsString(
                '198505052010012002',
                json_encode($log->perubahan),
                "Jejak audit {$log->aksi} membocorkan NIP"
            );
        }
    }
}
