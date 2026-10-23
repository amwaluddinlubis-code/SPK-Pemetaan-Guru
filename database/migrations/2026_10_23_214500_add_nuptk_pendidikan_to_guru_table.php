<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Linieritas fase 3: data pendukung Tambahan Penghasilan bagi guru
     * yang belum bersertifikat (Permendikdasmen 10/2026: memiliki NUPTK
     * dan berpendidikan paling rendah S-1/D-IV) — selama ini belum
     * tercatat di aplikasi sehingga kelayakannya tidak dapat diperiksa.
     */
    public function up(): void
    {
        Schema::table('guru', function (Blueprint $table) {
            if (! Schema::hasColumn('guru', 'nuptk')) {
                $table->string('nuptk', 30)->nullable();
            }
            if (! Schema::hasColumn('guru', 'pendidikan_terakhir')) {
                $table->string('pendidikan_terakhir', 30)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('guru', function (Blueprint $table) {
            $table->dropColumn(['nuptk', 'pendidikan_terakhir']);
        });
    }
};
