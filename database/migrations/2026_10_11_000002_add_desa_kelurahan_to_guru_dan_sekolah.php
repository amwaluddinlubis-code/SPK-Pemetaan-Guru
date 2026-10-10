<?php
// Migration: menambah kolom desa_kelurahan ke tabel guru & sekolah.
// Dipakai untuk mencatat desa/kelurahan domisili guru dan lokasi sekolah.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('guru', function (Blueprint $table) {
            $table->string('desa_kelurahan', 100)->nullable()->after('kecamatan');
        });
        Schema::table('sekolah', function (Blueprint $table) {
            $table->string('desa_kelurahan', 100)->nullable()->after('kecamatan');
        });
    }

    public function down(): void
    {
        Schema::table('guru', function (Blueprint $table) {
            $table->dropColumn('desa_kelurahan');
        });
        Schema::table('sekolah', function (Blueprint $table) {
            $table->dropColumn('desa_kelurahan');
        });
    }
};
