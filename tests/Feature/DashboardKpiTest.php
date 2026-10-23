<?php

// Tes KPI Dashboard (Redesign R2): konteks kartu adalah komposisi nyata
// (domisili terpetakan, per jenjang, per status), bukan teks pengisi.

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\Sekolah;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DashboardKpiTest extends TestCase
{
    use RefreshDatabase;

    private int $seq = 0;

    private function sekolah(string $jenjang): Sekolah
    {
        return Sekolah::create([
            'npsn' => '72'.++$this->seq, 'nama' => $jenjang.' TES '.$this->seq,
            'alamat' => 'Alamat', 'kecamatan' => 'Panyabungan',
            'kategori_daerah' => 'biasa', 'jenjang' => $jenjang,
            'lat' => 0.86, 'lng' => 99.55,
        ]);
    }

    private function guru(Sekolah $sekolah, string $status, bool $berdomisili): Guru
    {
        return Guru::create([
            'nama' => 'Guru '.++$this->seq, 'mapel' => 'Matematika',
            'jenjang' => 'SMP', 'status_kepegawaian' => $status,
            'domisili_alamat' => 'Domisili', 'kecamatan' => 'Panyabungan',
            'status_pernikahan' => 'belum_kawin', 'sudah_sertifikasi' => false,
            'sekolah_id' => $sekolah->id, 'sekolah_saat_ini' => $sekolah->nama,
            'domisili_lat' => $berdomisili ? 0.86 : null,
            'domisili_lng' => $berdomisili ? 99.55 : null,
        ]);
    }

    private function pengguna(string $role): User
    {
        return User::create([
            'name' => 'Pengguna '.$role, 'email' => $role.'@t.local',
            'password' => Hash::make('rahasia'), 'role' => $role,
        ]);
    }

    public function test_dashboard_pemetaan_konteks_komposisi(): void
    {
        $smp = $this->sekolah('SMP');
        $this->sekolah('SD');
        $this->guru($smp, 'pppk', true);
        $this->guru($smp, 'pns', true);
        $this->guru($smp, 'pppk', false);

        $res = $this->actingAs($this->pengguna('kabid_ptk'))->get(route('dashboard'));
        $res->assertOk();
        $res->assertSee('2 dari 3 berdomisili terpetakan');
        $res->assertSee('SD 1 · SMP 1');
        $res->assertSee('Tidak ada guru berjarak');
        $res->assertSee('Tidak ada usulan yang menunggu diproses');
    }

    public function test_dashboard_eksekutif_konteks_status_dan_sekolah(): void
    {
        $smp = $this->sekolah('SMP');
        $this->guru($smp, 'pppk', true);
        $this->guru($smp, 'pppk', false);
        $this->guru($smp, 'pns', true);

        $res = $this->actingAs($this->pengguna('pemda'))->get('/pemda');
        $res->assertOk();
        $res->assertSee('PPPK 2');
        $res->assertSee('PNS 1');
        $res->assertSee('SMP 1');
        $res->assertSee('Tidak ada usulan, SPK, atau swap yang menunggu keputusan');
    }
}
