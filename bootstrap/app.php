<?php

use App\Http\Middleware\PaksaGantiPassword;
use App\Http\Middleware\PastikanAksesMenu;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Aplikasi berjalan di balik reverse proxy (ngrok/Herd): percayai
        // header X-Forwarded-* agar URL yang dibuat mengikuti skema & host
        // asli pengakses, bukan alamat lokal http di belakang proxy.
        $middleware->trustProxies(at: '*');

        $middleware->alias([
            'role' => RoleMiddleware::class,
        ]);

        // Pengaturan Akses: tolak rute modul yang tidak diizinkan untuk
        // peran pengguna (lihat App\Services\RegistriMenu & AksesMenu).
        // PaksaGantiPassword: akun berpassword bawaan (guru: NIP) wajib
        // mengganti password sebelum memakai aplikasi.
        $middleware->web(append: [
            PastikanAksesMenu::class,
            PaksaGantiPassword::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
