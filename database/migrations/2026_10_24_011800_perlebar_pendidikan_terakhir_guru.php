<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// pendidikan_terakhir format bebas "Jenjang - Bidang" bisa panjang
// (mis. "S1 - Pendidikan Bahasa dan Sastra Indonesia" = 41 karakter),
// sedangkan kolom awal hanya varchar(30) dan validasi form max:60 —
// isian sah bisa gagal tersimpan. Samakan kolom dengan validasi: 60.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('guru', function (Blueprint $table) {
            $table->string('pendidikan_terakhir', 60)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('guru', function (Blueprint $table) {
            $table->string('pendidikan_terakhir', 30)->nullable()->change();
        });
    }
};
