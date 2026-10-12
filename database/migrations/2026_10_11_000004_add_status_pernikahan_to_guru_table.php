<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Menambah status pernikahan guru untuk pertimbangan prioritas keluarga
// dalam rekomendasi penempatan (guru berkeluarga diprioritaskan).
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('guru', function (Blueprint $table) {
            $table->string('status_pernikahan', 20)->nullable()->default('belum_kawin')
                ->after('desa_kelurahan');
        });
    }

    public function down(): void
    {
        Schema::table('guru', function (Blueprint $table) {
            $table->dropColumn('status_pernikahan');
        });
    }
};
