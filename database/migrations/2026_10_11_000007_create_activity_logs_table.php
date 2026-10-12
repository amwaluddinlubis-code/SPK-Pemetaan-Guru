<?php

// Migrasi audit trail operasional (G): tabel activity_logs bersifat
// append-only — hanya INSERT lewat aplikasi, tanpa UI edit/hapus.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->timestamp('waktu')->useCurrent()->index();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('aksi', 60)->index();
            $table->string('entitas_tipe', 60)->nullable()->index();
            $table->unsignedBigInteger('entitas_id')->nullable();
            $table->string('hasil', 20)->default('sukses');
            $table->json('perubahan')->nullable();
            $table->string('ip', 45)->nullable();
            // Tidak ada timestamps updated_at: append-only.
            $table->index(['entitas_tipe', 'entitas_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
