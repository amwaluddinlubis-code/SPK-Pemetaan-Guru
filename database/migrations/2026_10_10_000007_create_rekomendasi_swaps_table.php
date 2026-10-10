<?php
// Migration: tabel rekomendasi_swaps — menyimpan pasangan guru yang
// direkomendasikan untuk mutasi silang (swap) beserta alur persetujuannya.
// Satu baris = satu usulan pertukaran dua guru yang saling menguntungkan.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rekomendasi_swaps', function (Blueprint $table) {
            $table->id();

            // Pasangan guru yang diusulkan bertukar sekolah
            $table->foreignId('guru_a_id')->constrained('guru')->cascadeOnDelete();
            $table->foreignId('guru_b_id')->constrained('guru')->cascadeOnDelete();

            // Sekolah tujuan masing-masing (A pindah ke sekolah B semula, dst.)
            $table->foreignId('sekolah_tujuan_a_id')->constrained('sekolah')->cascadeOnDelete();
            $table->foreignId('sekolah_tujuan_b_id')->constrained('sekolah')->cascadeOnDelete();

            // Metrik jarak (km) — bukti perhitungan SPK (Haversine)
            $table->decimal('jarak_a_sekarang_km', 8, 2);
            $table->decimal('jarak_b_sekarang_km', 8, 2);
            $table->decimal('jarak_a_usulan_km', 8, 2);
            $table->decimal('jarak_b_usulan_km', 8, 2);
            $table->decimal('hemat_a_km', 8, 2);
            $table->decimal('hemat_b_km', 8, 2);
            $table->decimal('total_hemat_km', 8, 2);

            // Alur persetujuan
            $table->enum('status', ['menunggu', 'disetujui', 'ditolak', 'batal_karena_konflik'])
                ->default('menunggu');
            $table->foreignId('diproses_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->text('catatan_admin')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rekomendasi_swaps');
    }
};
