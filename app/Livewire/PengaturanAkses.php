<?php

// Komponen Livewire: matriks Pengaturan Akses — peran (kolom) × menu/modul
// (baris). Mengubah saklar langsung menyimpan keputusan ke tabel akses_menu
// (lewat App\Services\AksesMenu) dan tercatat di Jejak Audit. Hanya
// kabid_ptk; otorisasi ditegakkan di komponen ini, bukan di Blade.

namespace App\Livewire;

use App\Models\User;
use App\Services\AksesMenu;
use App\Services\RegistriMenu;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Pengaturan Akses — PetaPenempatan ASN')]
class PengaturanAkses extends Component
{
    /** Menu yang rutenya tetap dijaga middleware role khusus administrator. */
    public const KUNCI_ADMIN_PENUH = ['pengguna', 'pengaturan-pemda', 'pengaturan-akses'];

    public string $pesan = '';

    public function mount(): void
    {
        abort_unless(auth()->user()?->hasRole('kabid_ptk'), 403);
    }

    /** Balik satu keputusan akses (boleh ↔ tidak) untuk pasangan peran+menu. */
    public function toggle(string $kunci, string $role): void
    {
        abort_unless(auth()->user()?->hasRole('kabid_ptk'), 403);
        if (! RegistriMenu::ada($kunci) || ! array_key_exists($role, User::ROLES)) {
            return;
        }
        // Anti-terkunci: saklar admin untuk halaman ini tidak bisa dimatikan.
        if ($kunci === AksesMenu::KUNCI_SUPER && $role === AksesMenu::PERAN_SUPER) {
            return;
        }
        // Modul administrasi penuh tetap khusus kabid_ptk (middleware role
        // di rutenya); saklar untuk peran lain tidak berlaku.
        if (in_array($kunci, self::KUNCI_ADMIN_PENUH, true) && $role !== AksesMenu::PERAN_SUPER) {
            return;
        }
        AksesMenu::atur($role, $kunci, ! AksesMenu::boleh($role, $kunci));
        $this->pesan = 'Akses '.(RegistriMenu::ITEM[$kunci]['label']).
            ' untuk '.User::ROLES[$role].' kini '.(AksesMenu::boleh($role, $kunci) ? 'diizinkan.' : 'ditutup.');
    }

    /** Kembalikan seluruh peran ke bawaan registri. */
    public function resetBawaan(): void
    {
        abort_unless(auth()->user()?->hasRole('kabid_ptk'), 403);
        AksesMenu::resetBawaan();
        $this->pesan = 'Pengaturan akses dikembalikan ke bawaan.';
    }

    public function render()
    {
        return view('livewire.pengaturan-akses', [
            'grupMenu' => RegistriMenu::grup(),
            'daftarPeran' => User::ROLES,
            'matriks' => AksesMenu::matriks(),
            'kunciSuper' => AksesMenu::KUNCI_SUPER,
            'peranSuper' => AksesMenu::PERAN_SUPER,
            'kunciAdminPenuh' => self::KUNCI_ADMIN_PENUH,
        ]);
    }
}
