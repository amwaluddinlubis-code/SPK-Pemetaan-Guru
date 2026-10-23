<?php

// PaksaGantiPassword: akun dengan password bawaan (mis. akun guru yang
// password awalnya = NIP) wajib mengganti password sebelum memakai
// aplikasi. Halaman profil & logout tetap dapat diakses.

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PaksaGantiPassword
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->harus_ganti_password
            && ! $request->routeIs('profil.show', 'profil.password', 'logout')) {
            return redirect()->route('profil.show')
                ->with('warning', 'Demi keamanan, ganti dulu password bawaan Anda (password awal) dengan password baru.');
        }

        return $next($request);
    }
}
