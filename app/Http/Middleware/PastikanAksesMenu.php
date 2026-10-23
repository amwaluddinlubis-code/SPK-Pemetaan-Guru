<?php

// Middleware PastikanAksesMenu: gerbang Pengaturan Akses. Setiap rute
// bernama dipetakan ke kunci menu lewat RegistriMenu; bila peran pengguna
// tidak diizinkan (AksesMenu), permintaan ditolak 403. Rute di luar
// registri (dashboard, profil, API wilayah, impersonate) bebas lewat.
// Berjalan sebagai middleware grup web — pengguna tamu dilewatkan
// (gerbang 'auth' di rute yang menanganinya).

namespace App\Http\Middleware;

use App\Services\AksesMenu;
use App\Services\RegistriMenu;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PastikanAksesMenu
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if ($user !== null) {
            $kunci = RegistriMenu::kunciUntukRute($request->route()?->getName());
            if ($kunci !== null && ! AksesMenu::boleh($user->role, $kunci)) {
                abort(403, 'Modul ini tidak dapat diakses oleh peran Anda. Hubungi Kabid PTK bila Anda merasa ini keliru.');
            }
        }

        return $next($request);
    }
}
