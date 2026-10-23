<?php

// Seeder utama: memanggil semua seeder lain.
// Dijalankan dengan: php artisan db:seed

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            WilayahSeeder::class,       // referensi wilayah nasional (provinsi s.d. desa)
            SekolahMadinaSeeder::class, // 552 sekolah asli Madina (data referensi)
        ]);

        // Data contoh & akun demo HANYA untuk pengembangan lokal/testing.
        // Di production, seeder sengaja tidak membuat akun dengan password
        // yang diketahui publik — buat akun awal lewat prosedur operasional.
        if (app()->environment('local', 'testing')) {
            $this->call([
                DataContohSeeder::class, // 8 guru contoh
                AdminSeeder::class,      // akun demo 7 peran
            ]);
        }
    }
}
