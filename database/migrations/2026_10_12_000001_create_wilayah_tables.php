<?php

// Migration: tabel referensi wilayah administrasi Indonesia (4 level).
// Sumber: github.com/guzfirdaus/Wilayah-Administrasi-Indonesia (Kepmendagri
// 100.1.1-6117/2022). Kode Kemendagri dipakai sebagai primary key string
// agar stabil untuk relasi (2/4/7/13 digit). Data bersifat read-only.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wilayah_provinsi', function (Blueprint $table) {
            $table->string('kode', 2)->primary(); // mis. 12 = Sumatera Utara
            $table->string('nama', 100);
        });

        Schema::create('wilayah_kabupaten', function (Blueprint $table) {
            $table->string('kode', 4)->primary(); // mis. 1201 = Kab. Tapanuli Tengah
            $table->string('provinsi_kode', 2);
            $table->string('nama', 100);
            $table->foreign('provinsi_kode')->references('kode')
                ->on('wilayah_provinsi')->cascadeOnDelete();
            $table->index('provinsi_kode');
        });

        Schema::create('wilayah_kecamatan', function (Blueprint $table) {
            $table->string('kode', 7)->primary(); // mis. 120101
            $table->string('kabupaten_kode', 4);
            $table->string('nama', 100);
            $table->foreign('kabupaten_kode')->references('kode')
                ->on('wilayah_kabupaten')->cascadeOnDelete();
            $table->index('kabupaten_kode');
        });

        Schema::create('wilayah_desa', function (Blueprint $table) {
            $table->string('kode', 13)->primary(); // mis. 1101012001
            $table->string('kecamatan_kode', 7);
            $table->string('nama', 100);
            $table->foreign('kecamatan_kode')->references('kode')
                ->on('wilayah_kecamatan')->cascadeOnDelete();
            $table->index('kecamatan_kode');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wilayah_desa');
        Schema::dropIfExists('wilayah_kecamatan');
        Schema::dropIfExists('wilayah_kabupaten');
        Schema::dropIfExists('wilayah_provinsi');
    }
};
