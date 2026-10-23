<?php

// Layanan AksesMenu: menyelesaikan hak akses peran → menu/modul dari tabel
// akses_menu, dengan bawaan RegistriMenu sebagai cadangan. Baris di tabel
// hanya menyimpan penyimpangan yang diatur admin lewat Pengaturan Akses;
// selama tabel belum ada (migrasi belum jalan) aplikasi memakai bawaan.

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\AksesMenu as ModelAksesMenu;
use App\Models\User;
use Illuminate\Support\Facades\Schema;

class AksesMenu
{
    /** Peran yang tidak pernah boleh kehilangan akses ke halaman pengaturannya. */
    public const PERAN_SUPER = 'kabid_ptk';

    public const KUNCI_SUPER = 'pengaturan-akses';

    /** @var array<string, array<string, bool>>|null peta role → (kunci → boleh) dari tabel */
    private static ?array $memo = null;

    private static ?int $memoApp = null;

    private static ?bool $tabelSiap = null;

    private static ?int $tabelApp = null;

    /** Apakah peran boleh membuka menu/modul ini? */
    public static function boleh(string $role, string $kunci): bool
    {
        // Anti-terkunci: administrator selalu bisa membuka Pengaturan Akses.
        if ($kunci === self::KUNCI_SUPER && $role === self::PERAN_SUPER) {
            return true;
        }
        $peta = self::petaPeran($role);
        if (array_key_exists($kunci, $peta)) {
            return $peta[$kunci];
        }

        return in_array($role, RegistriMenu::peranBawaan($kunci), true);
    }

    /**
     * Matriks lengkap untuk halaman pengaturan: kunci → (role → boleh),
     * sudah terselesaikan (tabel + bawaan + anti-terkunci).
     *
     * @return array<string, array<string, bool>>
     */
    public static function matriks(): array
    {
        $hasil = [];
        foreach (RegistriMenu::ITEM as $kunci => $item) {
            foreach (array_keys(User::ROLES) as $role) {
                $hasil[$kunci][$role] = self::boleh($role, $kunci);
            }
        }

        return $hasil;
    }

    /** Peta visibilitas menu untuk satu peran (dipakai bilah sisi). */
    public static function petaVisibilitas(string $role): array
    {
        $hasil = [];
        foreach (array_keys(RegistriMenu::ITEM) as $kunci) {
            $hasil[$kunci] = self::boleh($role, $kunci);
        }

        return $hasil;
    }

    /** Simpan satu keputusan akses + catat ke Jejak Audit. */
    public static function atur(string $role, string $kunci, bool $boleh): void
    {
        if (! self::tabelSiap()) {
            return;
        }
        ModelAksesMenu::updateOrCreate(
            ['role' => $role, 'kunci' => $kunci],
            ['boleh' => $boleh],
        );
        self::lupakanMemo();
        ActivityLog::catatAman('akses-menu.ubah', null, 'sukses', [
            'role' => $role,
            'menu' => RegistriMenu::ITEM[$kunci]['label'] ?? $kunci,
            'boleh' => $boleh ? 'ya' : 'tidak',
        ]);
    }

    /** Hapus semua penyimpangan: seluruh peran kembali ke bawaan registri. */
    public static function resetBawaan(): void
    {
        if (! self::tabelSiap()) {
            return;
        }
        ModelAksesMenu::query()->delete();
        self::lupakanMemo();
        ActivityLog::catatAman('akses-menu.reset', null, 'sukses');
    }

    /** @return array<string, bool> keputusan tersimpan untuk satu peran */
    private static function petaPeran(string $role): array
    {
        if (! self::tabelSiap()) {
            return [];
        }
        $appId = spl_object_id(app());
        if (self::$memo === null || self::$memoApp !== $appId) {
            self::$memo = [];
            self::$memoApp = $appId;
            foreach (ModelAksesMenu::all() as $baris) {
                self::$memo[$baris->role][$baris->kunci] = (bool) $baris->boleh;
            }
        }

        return self::$memo[$role] ?? [];
    }

    private static function tabelSiap(): bool
    {
        // Diingat per instance aplikasi saja: hasil "tabel belum ada" tidak
        // boleh bocor ke konteks berikutnya (migrasi bisa baru saja jalan).
        $appId = spl_object_id(app());
        if (self::$tabelApp !== $appId) {
            self::$tabelSiap = Schema::hasTable('akses_menu');
            self::$tabelApp = $appId;
        }

        return self::$tabelSiap;
    }

    private static function lupakanMemo(): void
    {
        self::$memo = null;
        self::$memoApp = null;
    }
}
