<?php
// Seeder: membuat akun untuk kelima tingkatan pengguna.
// Setiap peran punya scope data berbeda:
//   guru           → data dirinya sendiri
//   kepala_sekolah → sekolahnya
//   ketua_pgri     → kecamatan
//   kabid_ptk      → kabupaten
//   kadis          → semua (penuh)
// ⚠️ GANTI semua password ini setelah login pertama!

namespace Database\Seeders;

use App\Models\Guru;
use App\Models\Sekolah;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Urutan penting: DataContohSeeder harus jalan dulu (dipanggil
        // setelahnya di DatabaseSeeder) agar guru/sekolah sudah ada.
        // Karena itu relasi dicari ulang di sini dengan aman (nullable).
        $guruSiti   = Guru::where('nip', '198501012010011001')->first();
        $sekolah1   = Sekolah::where('npsn', '10208250')->first(); // SD NEGERI 076 PANYABUNGAN

        $akun = [
            [
                'name' => 'Administrator', 'email' => 'admin@peta-asn.local',
                'password' => 'admin123', 'role' => 'kadis',
            ],
            [
                'name' => 'Siti Aminah (Guru)', 'email' => 'guru@peta-asn.local',
                'password' => 'guru123', 'role' => 'guru',
                'guru_id' => $guruSiti?->id,
            ],
            [
                'name' => 'Kepala SD Negeri 076 Panyabungan', 'email' => 'kepsek@peta-asn.local',
                'password' => 'kepsek123', 'role' => 'kepala_sekolah',
                'sekolah_id' => $sekolah1?->id,
            ],
            [
                'name' => 'Ketua PGRI Panyabungan', 'email' => 'pgri@peta-asn.local',
                'password' => 'pgri123', 'role' => 'ketua_pgri',
                'kecamatan' => 'Panyabungan',
            ],
            [
                'name' => 'Kabid PTK', 'email' => 'kabid@peta-asn.local',
                'password' => 'kabid123', 'role' => 'kabid_ptk',
            ],
            [
                'name' => 'Kadis Pendidikan', 'email' => 'kadis@peta-asn.local',
                'password' => 'kadis123', 'role' => 'kadis',
            ],
            [
                'name' => 'Pemerintah Daerah', 'email' => 'pemda@peta-asn.local',
                'password' => 'pemda123', 'role' => 'pemda',
            ],
        ];

        foreach ($akun as $a) {
            User::updateOrCreate(
                ['email' => $a['email']],
                [
                    'name'       => $a['name'],
                    'password'   => Hash::make($a['password']),
                    'role'       => $a['role'],
                    'guru_id'    => $a['guru_id'] ?? null,
                    'sekolah_id' => $a['sekolah_id'] ?? null,
                    'kecamatan'  => $a['kecamatan'] ?? null,
                ]
            );
        }
    }
}
