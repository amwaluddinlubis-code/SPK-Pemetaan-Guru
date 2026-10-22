<?php

namespace Tests\Feature;

use App\Models\KebutuhanGuru;
use App\Models\Sekolah;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TerapkanAnalisisMassalTest extends TestCase
{
    use RefreshDatabase;

    private int $seq = 700;

    private function buatUser(string $role): User
    {
        return User::create([
            'name' => 'User '.$role.' '.++$this->seq,
            'email' => $role.$this->seq.'@test.local',
            'password' => Hash::make('rahasia123'),
            'role' => $role,
        ]);
    }

    private function buatSekolah(string $npsn, array $attr = []): Sekolah
    {
        return Sekolah::create(array_merge([
            'npsn' => $npsn,
            'nama' => 'Sekolah '.$npsn,
            'alamat' => 'Jl. Tes '.$npsn,
            'kecamatan' => 'Panyabungan',
            'lat' => 1.0,
            'lng' => 100.0,
        ], $attr));
    }

    /**
     * @return array<string, int>
     */
    private function kebutuhanSekolah(Sekolah $sekolah): array
    {
        return KebutuhanGuru::where('sekolah_id', $sekolah->id)
            ->pluck('jumlah', 'mapel')
            ->map(fn ($v) => (int) $v)
            ->all();
    }

    public function test_massal_menerapkan_rumus_ke_semua_sekolah_berrombel(): void
    {
        $kabid = $this->buatUser('kabid_ptk');
        // SD 6 rombel: Guru Kelas 6, PJOK ceil(6×3/24)=1, Agama 1.
        $sd = $this->buatSekolah('20100101', ['jenjang' => 'SD', 'rombel' => 6]);
        // SMP 10 rombel: total ceil per mapel = 16 (B. Indonesia 3, MTK 2, dst).
        $smp = $this->buatSekolah('20100102', ['jenjang' => 'SMP', 'rombel' => 10]);
        // Tanpa rombel: dilewati, baris kebutuhan manualnya utuh.
        $tanpa = $this->buatSekolah('20100103', ['jenjang' => 'SD', 'rombel' => null]);
        KebutuhanGuru::create(['sekolah_id' => $tanpa->id, 'mapel' => 'Manual', 'jumlah' => 9]);
        // Baris lama sekolah berrombel diganti, bukan ditambah.
        KebutuhanGuru::create(['sekolah_id' => $sd->id, 'mapel' => 'Usang', 'jumlah' => 7]);

        $response = $this->actingAs($kabid)->post(route('sekolah.analisis.massal'));

        $response->assertRedirect(route('sekolah.index'));
        $response->assertSessionHas('sukses');

        $kebSd = $this->kebutuhanSekolah($sd);
        $this->assertSame(6, $kebSd['Guru Kelas'] ?? null);
        $this->assertSame(1, $kebSd['PJOK'] ?? null);
        $this->assertSame(1, $kebSd['Agama'] ?? null);
        $this->assertArrayNotHasKey('Usang', $kebSd);

        $kebSmp = $this->kebutuhanSekolah($smp);
        $this->assertSame(16, array_sum($kebSmp));
        $this->assertSame(3, $kebSmp['Bahasa Indonesia'] ?? null);
        $this->assertSame(2, $kebSmp['Matematika'] ?? null);
        $this->assertSame(1, $kebSmp['Informatika'] ?? null);

        $this->assertSame(['Manual' => 9], $this->kebutuhanSekolah($tanpa));
    }

    public function test_massal_ditolak_untuk_peran_lain(): void
    {
        $guru = $this->buatUser('guru');
        $sd = $this->buatSekolah('20100104', ['jenjang' => 'SD', 'rombel' => 6]);

        $this->actingAs($guru)->post(route('sekolah.analisis.massal'))->assertForbidden();
        $this->assertSame([], $this->kebutuhanSekolah($sd));
    }
}
