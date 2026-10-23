<?php

// Controller untuk login & logout admin.
// Route: GET /login, POST /login, POST /logout

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        // Satu kolom "login": pejabat memakai email; guru memakai NIP
        // sebagai username (keputusan AMWAL, 23 Okt 2026).
        $data = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required'],
        ], [
            'login.required' => 'Email / NIP wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $kredensial = str_contains($data['login'], '@')
            ? ['email' => $data['login'], 'password' => $data['password']]
            : ['username' => $data['login'], 'password' => $data['password']];

        // Coba login; jika gagal kembali dengan pesan error
        if (! Auth::attempt($kredensial, $request->boolean('remember'))) {
            return back()
                ->withInput($request->only('login'))
                ->withErrors(['login' => 'Email/NIP atau password salah.']);
        }

        // Cegah serangan session fixation
        $request->session()->regenerate();

        // Password bawaan (NIP) wajib diganti dulu (middleware juga menjaga).
        if ($request->user()->harus_ganti_password) {
            return redirect()->route('profil.show')
                ->with('warning', 'Demi keamanan, ganti dulu password bawaan Anda (password awal) dengan password baru.');
        }

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
}
