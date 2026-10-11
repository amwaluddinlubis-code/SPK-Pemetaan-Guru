<?php
// Controller Profil: lihat & ubah data diri + ganti password sendiri.
// Semua user yang login bisa akses (route: /profil).
// Komentar Bahasa Indonesia agar mudah dipelajari.

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfilController extends Controller
{
    // Tampilkan profil user yang sedang login
    public function show()
    {
        $user = auth()->user();
        return view('profil.show', compact('user'));
    }

    // Form ubah nama & email
    public function edit()
    {
        $user = auth()->user();
        return view('profil.form', compact('user'));
    }

    // Simpan perubahan nama & email
    public function update(Request $request)
    {
        $user = auth()->user();

        $data = $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255',
                        Rule::unique('users', 'email')->ignore($user->id)],
        ], [
            'name.required'  => 'Nama wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email'    => 'Format email tidak valid.',
            'email.unique'   => 'Email sudah dipakai akun lain.',
        ]);

        $user->update($data);

        return redirect()->route('profil.show')
            ->with('sukses', 'Profil berhasil diperbarui.');
    }

    // Form ganti password
    public function editPassword()
    {
        return view('profil.password');
    }

    // Simpan password baru (wajib tahu password lama)
    public function updatePassword(Request $request)
    {
        $user = auth()->user();

        $data = $request->validate([
            'password_lama' => ['required', 'string'],
            'password'      => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'password_lama.required' => 'Password lama wajib diisi.',
            'password.required'      => 'Password baru wajib diisi.',
            'password.min'           => 'Password baru minimal 6 karakter.',
            'password.confirmed'     => 'Konfirmasi password tidak cocok.',
        ]);

        // Pastikan password lama benar
        if (! Hash::check($data['password_lama'], $user->password)) {
            return back()->withErrors(
                ['password_lama' => 'Password lama salah.']);
        }

        $user->update(['password' => Hash::make($data['password'])]);

        return redirect()->route('profil.show')
            ->with('sukses', 'Password berhasil diganti.');
    }
}
