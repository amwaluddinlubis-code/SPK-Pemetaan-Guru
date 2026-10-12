<?php
// Migration: tabel hasil_spks — menyimpan rekomendasi penempatan guru
// ke sekolah yang kekurangan formasi (Skenario 1 SPK) + alur persetujuan.
// Satu baris = satu usulan pemindahan satu guru ke satu sekolah tujuan.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hasil_spks', function (Blueprint $table) {
            $table->id();

            $table->foreignId('guru_id')->constrained('guru')->cascadeOnDelete();
            $table->foreignId('sekolah_tujuan_id')->constrained('sekolah')->cascadeOnDelete();

            // Jarak domisili -> sekolah tujuan (dihitung di MySQL via Haversine)
            $table->decimal('jarak_km', 8, 2);
            // Jarak domisili -> sekolah saat ini (pembanding / bukti)
            $table->decimal('jarak_sekarang_km', 8, 2);

            // Alur persetujuan
            $table->enum('status', ['rekomendasi', 'disetujui', 'ditolak'])->default('rekomendasi');
            $table->foreignId('diproses_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->text('catatan_admin')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hasil_spks');
    }
};
