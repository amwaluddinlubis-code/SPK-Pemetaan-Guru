<?php

// AkunGuru: pembuatan & sinkronisasi akun login guru.
//
// Aturan (keputusan AMWAL, 23 Okt 2026):
// - Username akun guru = NIP guru dan TIDAK dapat diubah oleh guru.
// - Password bawaan = NIP juga; guru WAJIB menggantinya saat login
//   pertama (users.harus_ganti_password).
// - Guru tanpa NIP belum bisa memiliki akun — akun dibuat otomatis
//   begitu NIP terisi (impor, form operator, atau persetujuan
//   pendaftaran mandiri).

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Guru;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AkunGuru
{
    /** Domain email penampung akun guru (guru masuk memakai NIP). */
    public const DOMAIN_EMAIL = '@guru.spk.local';

    /**
     * Buatkan akun untuk guru bila belum ada dan NIP terisi.
     * Mengembalikan akun yang dibuat / yang sudah ada, atau null bila
     * guru belum punya NIP.
     */
    public static function buatkan(Guru $guru): ?User
    {
        $nip = trim((string) $guru->nip);
        if ($nip === '') {
            return null;
        }

        $akun = User::where('guru_id', $guru->id)
            ->orWhere('username', $nip)
            ->first();
        if ($akun) {
            return $akun;
        }

        $akun = User::create([
            'name' => $guru->nama,
            'email' => $nip.self::DOMAIN_EMAIL,
            'username' => $nip,
            'password' => Hash::make($nip),
            'role' => 'guru',
            'guru_id' => $guru->id,
            'sekolah_id' => $guru->sekolah_id,
            'harus_ganti_password' => true,
        ]);

        ActivityLog::catat('akun_guru.dibuat', $akun, 'sukses', [
            'username' => $nip,
            'guru' => $guru->nama,
        ]);

        return $akun;
    }

    /**
     * Selaraskan akun dengan data guru sesudah operator mengubah guru:
     * NIP berubah → username ikut berubah dan password kembali ke
     * bawaan (NIP baru) dengan kewajiban ganti password; NIP baru terisi
     * → akun dibuatkan.
     */
    public static function sinkronkan(Guru $guru): void
    {
        $akun = User::where('guru_id', $guru->id)->first();
        if (! $akun) {
            static::buatkan($guru);

            return;
        }

        $nip = trim((string) $guru->nip);
        if ($nip !== '' && $akun->username !== $nip) {
            $akun->update([
                'username' => $nip,
                'email' => $nip.self::DOMAIN_EMAIL,
                'password' => Hash::make($nip),
                'harus_ganti_password' => true,
            ]);
            ActivityLog::catat('akun_guru.disinkronkan', $akun, 'sukses', [
                'username' => $nip,
            ]);
        }

        if ($akun->sekolah_id !== $guru->sekolah_id) {
            $akun->update(['sekolah_id' => $guru->sekolah_id]);
        }
    }

    /** Kembalikan password akun guru ke bawaan (NIP) oleh operator. */
    public static function resetBawaan(User $akun): void
    {
        $nip = trim((string) $akun->username);
        if ($nip === '') {
            return;
        }
        $akun->update([
            'password' => Hash::make($nip),
            'harus_ganti_password' => true,
        ]);
        ActivityLog::catat('akun_guru.reset_password', $akun);
    }
}
