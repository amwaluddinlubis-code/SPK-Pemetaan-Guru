<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sekolah', function (Blueprint $table) {
            $table->decimal('koordinat_terverifikasi_lat', 10, 7)->nullable();
            $table->decimal('koordinat_terverifikasi_lng', 10, 7)->nullable();
            $table->timestamp('koordinat_terverifikasi_pada')->nullable();
            $table->foreignId('koordinat_terverifikasi_oleh')->nullable()
                ->constrained('users')->nullOnDelete();
            $table->text('koordinat_verifikasi_catatan')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sekolah', function (Blueprint $table) {
            $table->dropForeign(['koordinat_terverifikasi_oleh']);
            $table->dropColumn([
                'koordinat_terverifikasi_lat',
                'koordinat_terverifikasi_lng',
                'koordinat_terverifikasi_pada',
                'koordinat_terverifikasi_oleh',
                'koordinat_verifikasi_catatan',
            ]);
        });
    }
};
