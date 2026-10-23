<?php

// Migration: koordinat titik tengah (centroid) wilayah kecamatan & desa.
//
// Dipakai peta pemilih koordinat pada formulir: saat pengguna memilih
// kecamatan/desa, peta terbang ke wilayah itu agar mudah menentukan
// titik yang pas. Diisi massal oleh perintah `wilayah:hitung-koordinat`
// (rata-rata koordinat sekolah di wilayah tsb) dan boleh dikoreksi manual.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wilayah_kecamatan', function (Blueprint $table) {
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
        });

        Schema::table('wilayah_desa', function (Blueprint $table) {
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('wilayah_kecamatan', function (Blueprint $table) {
            $table->dropColumn(['lat', 'lng']);
        });

        Schema::table('wilayah_desa', function (Blueprint $table) {
            $table->dropColumn(['lat', 'lng']);
        });
    }
};
