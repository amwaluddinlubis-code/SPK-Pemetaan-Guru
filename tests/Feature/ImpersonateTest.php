<?php

// Tes Impersonate (23 Okt 2026): kabid_ptk masuk sebagai pengguna lain
// (bukan sesama admin, bukan diri sendiri), bilah peringatan tampil,
// berhenti mengembalikan akun asal; keduanya tercatat di Jejak Audit;
// peran lain tidak bisa memulai.

namespace Tests\Feature;

use App\Http\Controllers\ImpersonateController;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ImpersonateTest extends TestCase
{
    use RefreshDatabase;

    private function buatUser(string $email, string $role, string $nama): User
    {
        return User::create([
            'name' => $nama, 'email' => $email,
            'password' => Hash::make('rahasia'), 'role' => $role,
        ]);
    }

    public function test_alur_impersonate_penuh(): void
    {
        $admin = $this->buatUser('kabid@t.local', 'kabid_ptk', 'Admin Kabid');
        $guru = $this->buatUser('guru@t.local', 'guru', 'Guru Tujuan');

        // Tombol impersonate tampil di Kelola Pengguna untuk akun non-admin.
        $this->actingAs($admin)->get('/pengguna')->assertOk()
            ->assertSee('Impersonate');

        // Mulai: sesi berpindah ke akun guru + penanda asal tersimpan.
        $this->actingAs($admin)->post(route('impersonate.mulai', $guru))
            ->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($guru);
        $this->assertSame($admin->id, session(ImpersonateController::SESSION_ASAL));

        // Bilah peringatan tampil selama menyamar.
        $this->get('/')->assertOk()->assertSee('Mode impersonate')
            ->assertSee('Kembali ke akun saya');

        // Berhenti: kembali ke akun admin, penanda hilang.
        $this->post(route('impersonate.berhenti'))
            ->assertRedirect(route('pengguna.index'));
        $this->assertAuthenticatedAs($admin);
        $this->assertNull(session(ImpersonateController::SESSION_ASAL));

        $this->assertTrue(ActivityLog::where('aksi', 'impersonate.mulai')->exists());
        $this->assertTrue(ActivityLog::where('aksi', 'impersonate.berhenti')->exists());
    }

    public function test_batasan_impersonate(): void
    {
        $admin = $this->buatUser('kabid@t.local', 'kabid_ptk', 'Admin Kabid');
        $adminLain = $this->buatUser('kabid2@t.local', 'kabid_ptk', 'Admin Dua');
        $guru = $this->buatUser('guru@t.local', 'guru', 'Guru Tujuan');

        // Sesama admin tidak boleh di-impersonate.
        $this->actingAs($admin)->post(route('impersonate.mulai', $adminLain))
            ->assertRedirect()->assertSessionHas('gagal');
        $this->assertAuthenticatedAs($admin);
        $this->assertNull(session(ImpersonateController::SESSION_ASAL));

        // Diri sendiri tidak boleh.
        $this->actingAs($admin)->post(route('impersonate.mulai', $admin))
            ->assertRedirect()->assertSessionHas('gagal');
        $this->assertAuthenticatedAs($admin);

        // Peran lain tidak bisa memulai sama sekali (middleware role).
        $this->actingAs($guru)->post(route('impersonate.mulai', $admin))
            ->assertForbidden();
        $this->assertAuthenticatedAs($guru);

        // Berhenti tanpa sesi asal: aman, diarahkan ke dashboard.
        $this->actingAs($guru)->post(route('impersonate.berhenti'))
            ->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($guru);
    }

    public function test_selama_impersonate_halaman_admin_tertutup(): void
    {
        $admin = $this->buatUser('kabid@t.local', 'kabid_ptk', 'Admin Kabid');
        $guru = $this->buatUser('guru@t.local', 'guru', 'Guru Tujuan');

        $this->actingAs($admin)->post(route('impersonate.mulai', $guru));
        $this->assertAuthenticatedAs($guru);

        // Akun guru tidak bisa membuka Kelola Pengguna maupun memulai
        // impersonate lain dari dalam penyamaran.
        $this->get('/pengguna')->assertForbidden();
        $this->post(route('impersonate.mulai', $admin))->assertForbidden();
    }
}
