<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('kebutuhan_guru', function (Blueprint $table) {
            $table->unsignedInteger('jumlah_target')->default(0)->after('jumlah');
        });

        DB::table('kebutuhan_guru')->update(['jumlah_target' => DB::raw('jumlah')]);

        // Sebelum ini, persetujuan SPK mengurangi jumlah kebutuhan. Pulihkan
        // kebutuhan target dari rekomendasi yang sudah disetujui.
        $penempatanDisetujui = DB::table('hasil_spks')
            ->join('guru', 'guru.id', '=', 'hasil_spks.guru_id')
            ->where('hasil_spks.status', 'disetujui')
            ->selectRaw('hasil_spks.sekolah_tujuan_id AS sekolah_id, COALESCE(hasil_spks.mapel_tujuan, guru.mapel) AS mapel, COUNT(*) AS jumlah')
            ->groupBy('hasil_spks.sekolah_tujuan_id', DB::raw('COALESCE(hasil_spks.mapel_tujuan, guru.mapel)'))
            ->get();

        foreach ($penempatanDisetujui as $penempatan) {
            DB::table('kebutuhan_guru')
                ->where('sekolah_id', $penempatan->sekolah_id)
                ->where('mapel', $penempatan->mapel)
                ->increment('jumlah_target', (int) $penempatan->jumlah);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kebutuhan_guru', function (Blueprint $table) {
            $table->dropColumn('jumlah_target');
        });
    }
};
