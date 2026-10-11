<?php
// Migration: membuat tabel 'guru'.
// Dijalankan dengan: php artisan migrate

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guru', function (Blueprint $table) {
            $table->id();
            $table->string('nip', 30)->unique();          // NIP tidak boleh ganda
            $table->string('nama', 100);
            $table->string('mapel', 50);                  // mis. Matematika
            $table->string('jenjang', 10);                // SD / SMP / SMA
            $table->string('domisili_alamat', 255);       // alamat rumah
            $table->decimal('domisili_lat', 10, 7);       // koordinat rumah
            $table->decimal('domisili_lng', 10, 7);
            $table->string('sekolah_saat_ini', 100);      // tempat bertugas sekarang
            $table->decimal('sekolah_saat_ini_lat', 10, 7)->nullable();
            $table->decimal('sekolah_saat_ini_lng', 10, 7)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guru');
    }
};
