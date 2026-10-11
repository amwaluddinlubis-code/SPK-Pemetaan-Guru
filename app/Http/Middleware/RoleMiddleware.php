<?php
// Middleware: membatasi akses halaman berdasarkan role pengguna.
// Cara pakai di routes/web.php:
//   Route::get('/x', ...)->middleware('role:kadis,kabid_ptk');
// Jika role tidak cocok → error 403 (Akses Ditolak).

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        // Belum login atau role tidak termasuk yang diizinkan → tolak
        if (! $user || ! in_array($user->role, $roles, true)) {
            abort(403, 'Anda tidak memiliki hak akses ke halaman ini.');
        }

        return $next($request);
    }
}
