<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('akses_menu', function (Blueprint $table) {
            $table->id();
            $table->string('role', 32);
            $table->string('kunci', 64);
            $table->boolean('boleh')->default(true);
            $table->timestamps();

            $table->unique(['role', 'kunci']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('akses_menu');
    }
};
