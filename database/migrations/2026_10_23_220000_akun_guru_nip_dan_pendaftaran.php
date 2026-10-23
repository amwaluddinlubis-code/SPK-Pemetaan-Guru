<?php

// Akun guru berbasis NIP (keputusan AMWAL, 23 Okt 2026):
// - users.username (= NIP guru, tidak dapat diubah guru) + penanda wajib
//   ganti password bawaan saat login pertama.
// - guru.nip boleh kosong (guru PPPK hasil impor belum semuanya punya NIP;
//   NIP menyusul diisi operator) + tempat/tanggal lahir untuk data impor.
// - sekolah.npsn boleh kosong (sekolah baru hasil impor P3K belum punya
//   NPSN di berkas sumber; menyusul dari Dapodik).
// - Tabel pendaftaran_guru: guru yang belum punya akun/NIP boleh mendaftar
//   sendiri; akun hanya dibuat sesudah operator dinas menyetujui.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username', 30)->nullable()->unique()->after('email');
            $table->boolean('harus_ganti_password')->default(false)->after('password');
        });

        Schema::table('guru', function (Blueprint $table) {
            $table->string('nip', 30)->nullable()->change();
            $table->string('tempat_lahir', 100)->nullable()->after('nama');
            $table->date('tanggal_lahir')->nullable()->after('tempat_lahir');
            // Domisili guru hasil impor belum diketahui (berkas sumber
            // tidak memuatnya) — diisi bertahap oleh guru/operator dan
            // ditandai pemeriksa kualitas data. Mesin SPK & analisis
            // jarak sudah menyaring domisili kosong (whereNotNull).
            $table->string('domisili_alamat', 255)->nullable()->change();
            $table->decimal('domisili_lat', 10, 7)->nullable()->change();
            $table->decimal('domisili_lng', 10, 7)->nullable()->change();
        });

        Schema::table('sekolah', function (Blueprint $table) {
            $table->string('npsn', 20)->nullable()->change();
            // Sekolah baru hasil impor P3K belum punya alamat & koordinat
            // di berkas sumber — menyusul dari Dapodik.
            $table->string('alamat', 255)->nullable()->change();
            $table->decimal('lat', 10, 7)->nullable()->change();
            $table->decimal('lng', 10, 7)->nullable()->change();
        });

        Schema::create('pendaftaran_guru', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 100);
            $table->string('nip', 30)->nullable();
            $table->string('mapel', 50)->nullable();
            $table->string('jenjang', 10)->nullable();
            $table->string('status_kepegawaian', 30)->nullable();
            $table->foreignId('sekolah_id')->nullable()->constrained('sekolah')->nullOnDelete();
            $table->string('nama_sekolah', 150)->nullable();
            $table->string('no_hp', 30)->nullable();
            $table->string('status', 20)->default('menunggu');
            $table->text('catatan_operator')->nullable();
            $table->foreignId('diproses_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('guru_id')->nullable()->constrained('guru')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pendaftaran_guru');

        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['username']);
            $table->dropColumn(['username', 'harus_ganti_password']);
        });
    }
};
