<?php
// Controller untuk login & logout admin.
// Route: GET /login, POST /login, POST /logout

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    // Tampilkan halaman login
    public function showLogin()
    {
        // Kalau sudah login, langsung ke dashboard
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    // Proses login
    public function login(Request $request)
    {
        // Validasi input
        $kredensial = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ], [
            'email.required'    => 'Email wajib diisi.',
            'email.email'       => 'Format email tidak valid.',
            'password.required' => 'Password wajib diisi.',
        ]);

        // Coba login; jika gagal kembali dengan pesan error
        if (! Auth::attempt($kredensial, $request->boolean('remember'))) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Email atau password salah.']);
        }

        // Cegah serangan session fixation
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    // Proses logout
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    // Tampilkan form "lupa password"
    public function showLupaPassword()
    {
        return view('auth.lupa-password');
    }

    // Kirim link reset password ke email
    // (butuh konfigurasi MAIL di .env — lihat README)
    public function kirimLinkReset(Request $request)
    {
        $request->validate(
            ['email' => ['required', 'email']],
            ['email.required' => 'Email wajib diisi.',
             'email.email' => 'Format email tidak valid.']
        );

        $status = Password::sendResetLink($request->only('email'));

        return $status === Password::RESET_LINK_SENT
            ? back()->with('sukses', 'Link reset telah dikirim ke email Anda.')
            : back()->withInput($request->only('email'))
                    ->withErrors(['email' => 'Email tidak terdaftar.']);
    }

    // Tampilkan form reset password (dari link email)
    public function showResetPassword(string $token)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => request('email'),
        ]);
    }

    // Proses reset password
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token'    => ['required'],
            'email'    => ['required', 'email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'email.required'    => 'Email wajib diisi.',
            'password.required' => 'Password baru wajib diisi.',
            'password.min'      => 'Password minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();
                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('sukses', 'Password berhasil direset. Silakan masuk.')
            : back()->withInput($request->only('email'))
                    ->withErrors(['email' => 'Link reset tidak valid/kedaluwarsa.']);
    }
}
