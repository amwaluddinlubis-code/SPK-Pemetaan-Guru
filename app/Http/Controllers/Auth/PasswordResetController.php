<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as AturanPassword;

// Lupa & reset password via email (tautan reset dikirim ke email pengguna).
class PasswordResetController extends Controller
{
    public function mintaForm()
    {
        return view('auth.lupa-password');
    }

    public function kirimTautan(Request $request)
    {
        $request->validate(['email' => ['required', 'email']]);

        $status = Password::sendResetLink($request->only('email'));

        // Respons harus sama untuk email terdaftar maupun tidak terdaftar agar
        // endpoint tidak menjadi oracle enumerasi akun. Status broker tetap
        // menentukan apakah email benar-benar dikirim.
        return back()->with(
            'sukses',
            'Jika email terdaftar, tautan reset password akan dikirim. Periksa kotak masuk Anda.'
        );
    }

    public function resetForm(string $token)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => request('email'),
        ]);
    }

    public function simpanBaru(Request $request)
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', AturanPassword::min(8)],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('sukses', 'Password berhasil direset. Silakan login dengan password baru.')
            : back()->withErrors(['email' => 'Tautan reset tidak valid atau sudah kedaluwarsa.']);
    }
}
