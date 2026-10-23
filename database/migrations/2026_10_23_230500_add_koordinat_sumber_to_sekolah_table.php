<?php

// Sumber verifikasi koordinat (GPS lapangan, konfirmasi sekolah, peta
// Dapodik, citra satelit, konfirmasi massal) — melengkapi kolom
// verifikasi titik koordinat pada migrasi 2026_10_23_230001.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sekolah', function (Blueprint $table) {
            $table->string('koordinat_sumber', 60)->nullable()->after('koordinat_terverifikasi_oleh');
        });
    }

    public function down(): void
    {
        Schema::table('sekolah', function (Blueprint $table) {
            $table->dropColumn('koordinat_sumber');
        });
    }
};
