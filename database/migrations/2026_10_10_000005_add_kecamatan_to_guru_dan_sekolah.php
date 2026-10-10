<?php
// Migration: menambah kolom kecamatan ke tabel guru & sekolah.
// Dipakai untuk memfilter data sesuai wilayah ketua PGRI.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('guru', function (Blueprint $table) {
            $table->string('kecamatan', 100)->nullable()->after('domisili_alamat');
        });
        Schema::table('sekolah', function (Blueprint $table) {
            $table->string('kecamatan', 100)->nullable()->after('alamat');
        });
    }

    public function down(): void
    {
        Schema::table('guru', function (Blueprint $table) {
            $table->dropColumn('kecamatan');
        });
        Schema::table('sekolah', function (Blueprint $table) {
            $table->dropColumn('kecamatan');
        });
    }
};
