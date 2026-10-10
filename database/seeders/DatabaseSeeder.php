<?php
// Seeder utama. Data sekolah referensi dapat diisi di semua lingkungan;
// data guru dan akun demo hanya untuk pengembangan/pengujian lokal.

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            SekolahMadinaSeeder::class,
        ]);

        // Jangan memasukkan identitas guru fiktif atau kredensial demo
        // saat seeding database staging/production.
        if (app()->environment(['local', 'testing'])) {
            $this->call([
                DataContohSeeder::class,
                AdminSeeder::class,
            ]);
        }
    }
}
