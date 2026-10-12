<?php
// Migration: akuntabilitas rekomendasi SPK.
//   - sekolah_asal_id : sekolah tempat guru bertugas saat rekomendasi dibuat
//     (untuk validasi ulang saat persetujuan: guru belum pindah).
//   - hemat_km        : penghematan jarak (jarak_sekarang - jarak_tujuan),
//     bukti bahwa rekomendasi memang mendekatkan guru ke domisili.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hasil_spks', function (Blueprint $table) {
            $table->foreignId('sekolah_asal_id')->nullable()->after('guru_id')
                ->constrained('sekolah')->nullOnDelete();
            $table->decimal('hemat_km', 8, 2)->nullable()->after('jarak_sekarang_km');
        });
    }

    public function down(): void
    {
        Schema::table('hasil_spks', function (Blueprint $table) {
            $table->dropConstrainedForeignId('sekolah_asal_id');
            $table->dropColumn('hemat_km');
        });
    }
};
