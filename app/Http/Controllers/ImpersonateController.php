<?php

// Controller Impersonate: administrator (kabid_ptk) masuk sebagai pengguna
// lain untuk memeriksa tampilan & membantu kendala, lalu kembali ke
// akunnya sendiri. Identitas asal disimpan di sesi (SESSION_ASAL); selama
// sesi itu ada, bilah peringatan tampil di semua halaman. Kedua arah
// tercatat di Jejak Audit.

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ImpersonateController extends Controller
{
    /** Kunci sesi berisi id pengguna asal (administrator). */
    public const SESSION_ASAL = 'impersonate_asal_id';

    /** Mulai impersonate ke akun $user (hanya lewat middleware role:kabid_ptk). */
    public function mulai(Request $request, User $user)
    {
        $admin = $request->user();

        if ($request->session()->has(self::SESSION_ASAL)) {
            return back()->with('gagal', 'Anda sedang dalam mode impersonate. Kembali ke akun Anda dulu sebelum menyamar sebagai pengguna lain.');
        }
        if ($user->id === $admin->id) {
            return back()->with('gagal', 'Anda tidak dapat impersonate akun Anda sendiri.');
        }
        if ($user->role === 'kabid_ptk') {
            return back()->with('gagal', 'Akun sesama Kabid PTK tidak dapat di-impersonate.');
        }

        $request->session()->put(self::SESSION_ASAL, $admin->id);
        ActivityLog::catatAman('impersonate.mulai', $user, 'sukses', [
            'admin' => $admin->name.' ('.$admin->email.')',
            'sebagai' => $user->name.' ('.$user->email.')',
        ]);
        Auth::login($user);

        return redirect()->route('dashboard')
            ->with('sukses', 'Anda masuk sebagai '.$user->name.' ('.$user->labelRole().'). Klik "Kembali" pada bilah kuning di atas untuk pulang ke akun Anda.');
    }

    /** Akhiri impersonate dan kembali ke akun administrator asal. */
    public function berhenti(Request $request)
    {
        $asalId = $request->session()->pull(self::SESSION_ASAL);
        if ($asalId === null) {
            return redirect()->route('dashboard');
        }

        $target = $request->user();
        $asal = User::find($asalId);
        if ($asal === null) {
            Auth::logout();

            return redirect()->route('login')
                ->with('gagal', 'Akun asal impersonate tidak ditemukan. Silakan masuk kembali.');
        }

        Auth::login($asal);
        ActivityLog::catatAman('impersonate.berhenti', $target, 'sukses', [
            'admin' => $asal->name.' ('.$asal->email.')',
            'dari' => $target?->name.' ('.$target?->email.')',
        ]);

        return redirect()->route('pengguna.index')
            ->with('sukses', 'Anda kembali ke akun Anda ('.$asal->name.').');
    }
}
