<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Linieritas fase 2 (Kepmen 222/O/2025): rekomendasi SPK mencatat
     * formasi yang benar-benar diisi (mapel_tujuan) dan tingkat
     * kelayakannya — 1 = mapel persis guru, 2 = linier kelompok dari
     * bidang studi sertifikasinya. Baris lama NULL = diisi mapel guru.
     */
    public function up(): void
    {
        Schema::table('hasil_spks', function (Blueprint $table) {
            if (! Schema::hasColumn('hasil_spks', 'mapel_tujuan')) {
                $table->string('mapel_tujuan', 50)->nullable();
            }
            if (! Schema::hasColumn('hasil_spks', 'tingkat_linieritas')) {
                $table->unsignedTinyInteger('tingkat_linieritas')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('hasil_spks', function (Blueprint $table) {
            $table->dropColumn(['mapel_tujuan', 'tingkat_linieritas']);
        });
    }
};
