<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

// Profil pengguna: lihat data akun + ganti password sendiri.
class ProfilController extends Controller
{
    public function show()
    {
        return view('profil.show', ['pengguna' => auth()->user()]);
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'password_lama' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ], [
            'password_lama.current_password' => 'Password lama tidak sesuai.',
        ]);

        $request->user()->update([
            'password' => Hash::make($request->password),
            // Password bawaan sudah diganti — kewajiban selesai.
            'harus_ganti_password' => false,
        ]);

        return back()->with('sukses', 'Password berhasil diganti.');
    }
}
