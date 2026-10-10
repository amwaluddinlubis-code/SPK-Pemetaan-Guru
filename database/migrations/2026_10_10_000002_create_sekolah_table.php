<?php
// Migration: membuat tabel 'sekolah'.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sekolah', function (Blueprint $table) {
            $table->id();
            $table->string('npsn', 20)->unique();         // NPSN tidak boleh ganda
            $table->string('nama', 100);
            $table->string('alamat', 255);
            $table->decimal('lat', 10, 7);                // koordinat sekolah
            $table->decimal('lng', 10, 7);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sekolah');
    }
};
