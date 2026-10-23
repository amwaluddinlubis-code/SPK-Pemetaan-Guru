<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Pemisahan label mapel agama (keputusan 23 Okt 2026): label gabungan
     * "Agama" menjadi "Agama Islam" — agama mayoritas dan satu-satunya
     * agama pada data guru saat pemisahan. Baris "Agama Kristen" dibuat
     * manual per sekolah bila diperlukan. Berlaku untuk kebutuhan guru,
     * mapel & mapel sertifikasi guru, dan formasi tujuan rekomendasi.
     */
    public function up(): void
    {
        DB::table('kebutuhan_guru')->where('mapel', 'Agama')->update(['mapel' => 'Agama Islam']);
        DB::table('guru')->where('mapel', 'Agama')->update(['mapel' => 'Agama Islam']);
        DB::table('guru')->where('mapel_sertifikasi', 'Agama')->update(['mapel_sertifikasi' => 'Agama Islam']);
        DB::table('hasil_spks')->where('mapel_tujuan', 'Agama')->update(['mapel_tujuan' => 'Agama Islam']);
    }

    public function down(): void
    {
        // Tidak dapat dibalik dengan aman: baris "Agama Islam" sesudah
        // migrasi ini dapat berasal dari data baru, bukan hanya ubahan
        // nama. Pemulihan label gabungan dilakukan manual bila perlu.
    }
};
