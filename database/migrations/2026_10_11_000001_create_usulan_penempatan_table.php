<?php
// Migration: tabel 'usulan_penempatan' — alur pengajuan penempatan guru.
//   diajukan (guru/kepala/kabid) → diverifikasi (kabid) → disetujui (kadis)
//   ditolak bisa dari tahap diajukan/diverifikasi (oleh kabid/kadis).
// Saat disetujui, data guru otomatis dimutasi ke sekolah tujuan (di controller).

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usulan_penempatan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guru_id')->constrained('guru')->cascadeOnDelete();
            $table->foreignId('sekolah_id')->constrained('sekolah')->cascadeOnDelete();
            $table->text('alasan');                          // alasan pengajuan
            $table->string('status', 20)->default('diajukan'); // diajukan/diverifikasi/disetujui/ditolak
            $table->foreignId('diajukan_oleh')->nullable()
                ->constrained('users')->nullOnDelete();       // siapa mengajukan
            $table->foreignId('diproses_oleh')->nullable()
                ->constrained('users')->nullOnDelete();       // kabid/kadis terakhir memproses
            $table->text('catatan')->nullable();              // catatan verifikasi/penolakan
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usulan_penempatan');
    }
};
