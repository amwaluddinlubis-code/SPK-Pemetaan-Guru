<?php

// Seeder: 8 guru contoh (data fiktif) untuk pengembangan lokal.
// Sekolah diisi oleh SekolahMadinaSeeder (data asli Kemendikdasmen).

namespace Database\Seeders;

use App\Models\Guru;
use App\Models\Sekolah;
use Illuminate\Database\Seeder;

class DataContohSeeder extends Seeder
{
    public function run(): void
    {
        // Pengaman: data guru contoh tidak boleh dibuat di production.
        abort_unless(app()->environment('local', 'testing'), 403,
            'DataContohSeeder hanya boleh dijalankan di environment local/testing.');

        // Kosongkan dulu agar seeder bisa dijalankan ulang dengan aman
        Guru::query()->delete();

        $daftarGuru = [
            ['nip' => '198501012010011001', 'nama' => 'Siti Aminah', 'mapel' => 'Matematika', 'jenjang' => 'SD',
                'domisili_alamat' => 'Jl. Merdeka No.10, Panyabungan', 'kecamatan' => 'Panyabungan',
                'domisili_lat' => 0.855, 'domisili_lng' => 99.555,
                'sekolah_saat_ini' => 'SD NEGERI NO. 039 HUTAGODANGMUDA',
                'sekolah_saat_ini_lat' => 1.010339, 'sekolah_saat_ini_lng' => 99.517867,
                'status_pernikahan' => 'kawin', 'status_kepegawaian' => 'pns',
                'sudah_sertifikasi' => true, 'mapel_sertifikasi' => 'Matematika'],
            ['nip' => '198703152012022002', 'nama' => 'Budi Hartono', 'mapel' => 'IPA', 'jenjang' => 'SMP',
                'domisili_alamat' => 'Jl. Sudirman No.5, Panyabungan', 'kecamatan' => 'Panyabungan',
                'domisili_lat' => 0.87, 'domisili_lng' => 99.57,
                'sekolah_saat_ini' => 'SD NEGERI 356 SINUNUKAN VI',
                'sekolah_saat_ini_lat' => 0.579258, 'sekolah_saat_ini_lng' => 99.6422032,
                'status_pernikahan' => 'kawin', 'status_kepegawaian' => 'pppk',
                'sudah_sertifikasi' => false, 'mapel_sertifikasi' => null],
            ['nip' => '199001202015032003', 'nama' => 'Dewi Lestari', 'mapel' => 'Bahasa Indonesia', 'jenjang' => 'SD',
                'domisili_alamat' => 'Jl. Ahmad Yani No.8, Panyabungan', 'kecamatan' => 'Panyabungan Utara',
                'domisili_lat' => 0.845, 'domisili_lng' => 99.548,
                'sekolah_saat_ini' => 'SD NEGERI 208 HUTAPUNGKUT',
                'sekolah_saat_ini_lat' => 0.6803542, 'sekolah_saat_ini_lng' => 99.6953834,
                'status_pernikahan' => 'belum_kawin', 'status_kepegawaian' => 'pns',
                'sudah_sertifikasi' => true, 'mapel_sertifikasi' => 'Bahasa Indonesia'],
            ['nip' => '198812102011011004', 'nama' => 'Agus Setiawan', 'mapel' => 'Matematika', 'jenjang' => 'SMP',
                'domisili_alamat' => 'Jl. Gatot Subroto No.3, Siabu', 'kecamatan' => 'Siabu',
                'domisili_lat' => 0.88, 'domisili_lng' => 99.54,
                'sekolah_saat_ini' => 'SD NEGERI 330 AIR APA',
                'sekolah_saat_ini_lat' => 0.5295439, 'sekolah_saat_ini_lng' => 99.2326872,
                'status_pernikahan' => 'kawin', 'status_kepegawaian' => 'honorer',
                'sudah_sertifikasi' => false, 'mapel_sertifikasi' => null],
            ['nip' => '199205182018012005', 'nama' => 'Rina Marlina', 'mapel' => 'Bahasa Inggris', 'jenjang' => 'SMP',
                'domisili_alamat' => 'Jl. Diponegoro No.12, Panyabungan', 'kecamatan' => 'Panyabungan',
                'domisili_lat' => 0.86, 'domisili_lng' => 99.575,
                'sekolah_saat_ini' => 'SDN 320 SINUNUKAN I',
                'sekolah_saat_ini_lat' => 0.5189739, 'sekolah_saat_ini_lng' => 99.2010772,
                'status_pernikahan' => 'belum_kawin', 'status_kepegawaian' => 'pppk',
                'sudah_sertifikasi' => true, 'mapel_sertifikasi' => 'Bahasa Inggris'],
            ['nip' => '198909252014021006', 'nama' => 'Joko Prasetyo', 'mapel' => 'PJOK', 'jenjang' => 'SD',
                'domisili_alamat' => 'Jl. Kartini No.7, Panyabungan Timur', 'kecamatan' => 'Panyabungan Timur',
                'domisili_lat' => 0.85, 'domisili_lng' => 99.565,
                'sekolah_saat_ini' => 'SD NEGERI 374 SIKARA KARA III',
                'sekolah_saat_ini_lat' => 0.7509017, 'sekolah_saat_ini_lng' => 99.5536083,
                'status_pernikahan' => 'kawin', 'status_kepegawaian' => 'pns',
                'sudah_sertifikasi' => false, 'mapel_sertifikasi' => null],
            ['nip' => '199311112019022007', 'nama' => 'Maya Putri', 'mapel' => 'IPA', 'jenjang' => 'SD',
                'domisili_alamat' => 'Jl. Sisingamangaraja No.2, Kotanopan', 'kecamatan' => 'Kotanopan',
                'domisili_lat' => 0.865, 'domisili_lng' => 99.55,
                'sekolah_saat_ini' => 'SD NEGERI 342 BATU SONDAT',
                'sekolah_saat_ini_lat' => 0.563508, 'sekolah_saat_ini_lng' => 99.6557432,
                'status_pernikahan' => 'cerai_hidup', 'status_kepegawaian' => 'honorer',
                'sudah_sertifikasi' => false, 'mapel_sertifikasi' => null],
            ['nip' => '198606082009011008', 'nama' => 'Hendra Gunawan', 'mapel' => 'Matematika', 'jenjang' => 'SD',
                'domisili_alamat' => 'Jl. Pattimura No.9, Panyabungan', 'kecamatan' => 'Panyabungan',
                'domisili_lat' => 0.875, 'domisili_lng' => 99.56,
                'sekolah_saat_ini' => 'SD NEGERI 200 KOTANOPAN',
                'sekolah_saat_ini_lat' => 0.6521342, 'sekolah_saat_ini_lng' => 99.6948034,
                'status_pernikahan' => 'belum_kawin', 'status_kepegawaian' => 'pns',
                'sudah_sertifikasi' => true, 'mapel_sertifikasi' => 'IPA'],
        ];
        foreach ($daftarGuru as $g) {
            Guru::create($g);
        }

        // Tautkan guru ke sekolah di sistem jika nama sekolah_saat_ini cocok.
        // (Guru yang sekolahnya tidak terdata dibiarkan null.)
        foreach (Guru::all() as $guru) {
            $sekolah = Sekolah::where('nama', $guru->sekolah_saat_ini)->first();
            if ($sekolah) {
                $guru->update(['sekolah_id' => $sekolah->id]);
            }
        }
    }
}
