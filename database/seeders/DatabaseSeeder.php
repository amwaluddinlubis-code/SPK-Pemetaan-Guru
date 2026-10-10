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
            SekolahMadinaSeeder::class, // 553 sekolah asli Madina (dulu)
            DataContohSeeder::class,    // 8 guru contoh (d-relokasi ke Madina)
            AdminSeeder::class,         // akun 5 peran (butuh data guru/sekolah)
        ]);
    }
}
