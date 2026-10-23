<?php

// Migration: jejak skor peringkat hibrida SAW + TOPSIS pada hasil SPK.
// - skor_saw/skor_topsis: komponen metode (0..1)
// - skor: skor akhir hibrida (rata-rata keduanya, 0..1)
// - detail_skor: snapshot kriteria, bobot, normalisasi, dan jarak ideal
//   saat rekomendasi dibuat — dasar audit bila data sumber berubah.
// Rekomendasi lama tetap NULL (belum pernah dinilai dengan metode ini).

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hasil_spks', function (Blueprint $table) {
            $table->decimal('skor_saw', 5, 4)->nullable()->after('hemat_km');
            $table->decimal('skor_topsis', 5, 4)->nullable()->after('skor_saw');
            $table->decimal('skor', 5, 4)->nullable()->after('skor_topsis');
            $table->json('detail_skor')->nullable()->after('skor');
        });
    }

    public function down(): void
    {
        Schema::table('hasil_spks', function (Blueprint $table) {
            $table->dropColumn(['skor_saw', 'skor_topsis', 'skor', 'detail_skor']);
        });
    }
};
