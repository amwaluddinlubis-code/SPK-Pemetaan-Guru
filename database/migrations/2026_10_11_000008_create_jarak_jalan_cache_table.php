<?php

// Migrasi cache jarak jalan (hybrid routing): menyimpan hasil query
// ke layanan routing eksternal agar tidak dipanggil berulang.
// Koordinat dibulatkan 4 desimal (~11 m) sebagai kunci cache.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jarak_jalan_cache', function (Blueprint $table) {
            $table->id();
            // Koordinat dibulatkan 4 desimal untuk cache hit
            $table->decimal('lat_asal', 8, 4);
            $table->decimal('lng_asal', 9, 4);
            $table->decimal('lat_tujuan', 8, 4);
            $table->decimal('lng_tujuan', 9, 4);
            $table->decimal('jarak_km', 8, 2);
            $table->integer('waktu_menit')->nullable();
            $table->string('sumber', 20)->default('osrm'); // osrm | ors
            $table->timestamps();

            $table->unique(['lat_asal', 'lng_asal', 'lat_tujuan', 'lng_tujuan'], 'uq_jarak_jalan');
            $table->index('updated_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jarak_jalan_cache');
    }
};
