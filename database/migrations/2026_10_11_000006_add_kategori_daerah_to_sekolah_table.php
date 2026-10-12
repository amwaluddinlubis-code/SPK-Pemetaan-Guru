<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Kategori daerah sekolah untuk rem pemerataan: guru dari sekolah
// 3T/terpencil tidak ditarik keluar lewat SPK.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sekolah', function (Blueprint $table) {
            $table->string('kategori_daerah', 20)->nullable()->default('biasa')
                ->after('desa_kelurahan');
        });
    }

    public function down(): void
    {
        Schema::table('sekolah', function (Blueprint $table) {
            $table->dropColumn('kategori_daerah');
        });
    }
};
