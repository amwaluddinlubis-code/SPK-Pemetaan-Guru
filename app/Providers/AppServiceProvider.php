<?php

namespace App\Providers;

use App\Models\Guru;
use App\Models\KebutuhanGuru;
use App\Models\Sekolah;
use App\Models\UsulanPenempatan;
use App\Services\KonteksPemda;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Konteks Pemda (Pengaturan Pemda): selama konteks aktif, data di luar
        // wilayah terpilih disembunyikan dari seluruh query model inti.
        // Tanpa konteks terpilih, scope-scope ini tidak membatasi apa pun.
        Sekolah::addGlobalScope('konteks_pemda', function ($query) {
            KonteksPemda::batasiSekolah($query);
        });
        Guru::addGlobalScope('konteks_pemda', function ($query) {
            KonteksPemda::batasiGuru($query);
        });
        KebutuhanGuru::addGlobalScope('konteks_pemda', function ($query) {
            KonteksPemda::batasiLewatSekolah($query);
        });
        UsulanPenempatan::addGlobalScope('konteks_pemda', function ($query) {
            KonteksPemda::batasiLewatSekolah($query);
        });

        // Identitas pemda untuk layout (nama instansi di bilah sisi).
        // Composer malas: dibaca saat view dirender, bukan saat boot, agar
        // tidak menyentuh database sebelum aplikasi siap sepenuhnya.
        View::composer('*', function ($view) {
            $view->with('konteksPemdaNama', KonteksPemda::namaPemda());
            $view->with('konteksPemdaWilayah', KonteksPemda::namaWilayah());
            $view->with('konteksPemdaAktif', KonteksPemda::aktif());
        });
    }
}
