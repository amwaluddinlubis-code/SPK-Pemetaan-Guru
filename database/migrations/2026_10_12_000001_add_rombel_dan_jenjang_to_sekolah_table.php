<?php

// Migration: tambah kolom 'rombel' & 'jenjang' ke tabel 'sekolah'.
//   rombel  = jumlah rombongan belajar (dari Dapodik) — bahan hitung analisis kebutuhan guru.
//   jenjang = 'SD' / 'SMP' — menentukan rumus (SD: guru kelas+PJOK+agama; SMP: per mapel).
// Baris lama: jenjang diisi otomatis dari nama sekolah (mengandung SMP/MTs → SMP).

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sekolah', function (Blueprint $table) {
            $table->string('jenjang', 10)->nullable()->after('nama');
            $table->unsignedInteger('rombel')->nullable()->after('jenjang');
        });

        // Isi jenjang untuk data lama berdasarkan nama sekolah.
        DB::table('sekolah')
            ->whereNull('jenjang')
            ->update([
                'jenjang' => DB::raw(
                    "CASE WHEN UPPER(nama) LIKE '%SMP%' OR UPPER(nama) LIKE '%MTS%' THEN 'SMP' ELSE 'SD' END"
                ),
            ]);
    }

    public function down(): void
    {
        Schema::table('sekolah', function (Blueprint $table) {
            $table->dropColumn(['jenjang', 'rombel']);
        });
    }
};
