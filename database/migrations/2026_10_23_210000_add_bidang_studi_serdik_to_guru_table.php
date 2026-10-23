<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bidang studi sertifikasi guru (nama + kode pada sertifikat pendidik,
     * menurut Kepmen 222/O/2025) — dasar penilaian linieritas penempatan.
     */
    public function up(): void
    {
        Schema::table('guru', function (Blueprint $table) {
            if (! Schema::hasColumn('guru', 'bidang_studi_serdik')) {
                $table->string('bidang_studi_serdik', 150)->nullable();
            }
            if (! Schema::hasColumn('guru', 'kode_bidang_studi')) {
                $table->string('kode_bidang_studi', 40)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('guru', function (Blueprint $table) {
            $table->dropColumn(['bidang_studi_serdik', 'kode_bidang_studi']);
        });
    }
};
