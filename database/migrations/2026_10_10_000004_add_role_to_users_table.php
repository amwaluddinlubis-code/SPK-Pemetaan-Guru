<?php
// Migration: menambah kolom role & relasi ke tabel 'users'.
// Role: guru, kepala_sekolah, ketua_pgri, kabid_ptk, kadis

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Peran pengguna — menentukan hak akses di seluruh aplikasi
            $table->string('role', 20)->default('guru')->after('password');
            // Relasi opsional: user guru terhubung ke data guru-nya
            $table->foreignId('guru_id')->nullable()->after('role')
                ->constrained('guru')->nullOnDelete();
            // Relasi opsional: user kepala sekolah terhubung ke sekolahnya
            $table->foreignId('sekolah_id')->nullable()->after('guru_id')
                ->constrained('sekolah')->nullOnDelete();
            // Cakupan wilayah untuk ketua PGRI (nama kecamatan)
            $table->string('kecamatan', 100)->nullable()->after('sekolah_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('guru_id');
            $table->dropConstrainedForeignId('sekolah_id');
            $table->dropColumn(['role', 'kecamatan']);
        });
    }
};
