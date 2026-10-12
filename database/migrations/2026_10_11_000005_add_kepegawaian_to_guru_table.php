<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Data kepegawaian guru untuk pertimbangan analis: status kepegawaian
// (PNS/PPPK/honorer) dan sertifikasi (linearitas mapel).
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('guru', function (Blueprint $table) {
            $table->string('status_kepegawaian', 20)->nullable()->default('pns')
                ->after('status_pernikahan');
            $table->boolean('sudah_sertifikasi')->nullable()->default(false)
                ->after('status_kepegawaian');
            $table->string('mapel_sertifikasi', 50)->nullable()
                ->after('sudah_sertifikasi');
        });
    }

    public function down(): void
    {
        Schema::table('guru', function (Blueprint $table) {
            $table->dropColumn(['status_kepegawaian', 'sudah_sertifikasi', 'mapel_sertifikasi']);
        });
    }
};
