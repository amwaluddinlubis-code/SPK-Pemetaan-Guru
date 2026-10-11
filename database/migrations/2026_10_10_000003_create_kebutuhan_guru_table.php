<?php
// Migration: membuat tabel 'kebutuhan_guru'.
// Satu sekolah bisa punya beberapa baris (satu per mapel yang dibutuhkan).

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kebutuhan_guru', function (Blueprint $table) {
            $table->id();
            // Relasi ke tabel sekolah; jika sekolah dihapus, kebutuhannya ikut terhapus
            $table->foreignId('sekolah_id')->constrained('sekolah')->cascadeOnDelete();
            $table->string('mapel', 50);
            $table->unsignedInteger('jumlah')->default(1); // berapa guru dibutuhkan
            $table->timestamps();

            // Satu sekolah tidak boleh punya dua baris untuk mapel yang sama
            $table->unique(['sekolah_id', 'mapel']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kebutuhan_guru');
    }
};
