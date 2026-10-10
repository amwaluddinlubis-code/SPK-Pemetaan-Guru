<?php
// Migration: menambah relasi guru → sekolah (sekolah_id).
// Dipakai agar Kepala Sekolah bisa CRUD data guru di sekolahnya sendiri.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('guru', function (Blueprint $table) {
            $table->foreignId('sekolah_id')->nullable()->after('kecamatan')
                ->constrained('sekolah')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('guru', function (Blueprint $table) {
            $table->dropConstrainedForeignId('sekolah_id');
        });
    }
};
