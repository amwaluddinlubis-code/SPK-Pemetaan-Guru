<?php

// Tes pagination & filter baris (23 Okt 2026): tema pagination DaisyUI
// (join/btn) untuk semua tabel, pemilih baris per halaman (trait
// DenganPerHalaman), dan pencarian teks pada TabelSpk & TabelSwap.

namespace Tests\Feature;

use App\Livewire\TabelSekolah;
use App\Livewire\TabelSpk;
use App\Livewire\TabelSwap;
use App\Models\Guru;
use App\Models\HasilSpk;
use App\Models\JarakJalanCache;
use App\Models\RekomendasiSwap;
use App\Models\Sekolah;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class PaginationDaisyUiTest extends TestCase
{
    use RefreshDatabase;

    private int $seq = 0;

    private function buatSekolah(string $nama, string $kecamatan): Sekolah
    {
        return Sekolah::create([
            'npsn' => 'NPSN'.++$this->seq, 'nama' => $nama,
            'alamat' => 'Jl Tes', 'kecamatan' => $kecamatan,
            'lat' => 0.86, 'lng' => 99.56,
        ]);
    }

    private function buatGuru(string $nama, ?int $sekolahId = null): Guru
    {
        return Guru::create([
            'nip' => 'NIP'.++$this->seq, 'nama' => $nama, 'mapel' => 'Matematika',
            'jenjang' => 'SD', 'domisili_alamat' => 'Jl Uji',
            'kecamatan' => 'Panyabungan', 'sekolah_id' => $sekolahId,
            'domisili_lat' => 0.86, 'domisili_lng' => 99.56,
            'sekolah_saat_ini' => 'Sekolah A',
        ]);
    }

    private function buatUser(string $email, string $role): User
    {
        return User::create([
            'name' => $role, 'email' => $email,
            'password' => Hash::make('rahasia'), 'role' => $role,
        ]);
    }

    public function test_pagination_memakai_tema_daisyui_dan_pemilih_baris(): void
    {
        for ($i = 1; $i <= 16; $i++) {
            $this->buatSekolah('Sekolah '.$i, 'Panyabungan');
        }
        $kabid = $this->buatUser('kabid@t.local', 'kabid_ptk');

        Livewire::actingAs($kabid)->test(TabelSekolah::class)
            ->assertSee('join-item', false)
            ->assertSee('btn-active', false)
            ->assertSee('Baris per halaman', false);
    }

    public function test_per_halaman_mengubah_jumlah_baris_dan_terikat_opsi(): void
    {
        for ($i = 1; $i <= 30; $i++) {
            $this->buatSekolah('Sekolah '.$i, 'Panyabungan');
        }
        $kabid = $this->buatUser('kabid@t.local', 'kabid_ptk');

        Livewire::actingAs($kabid)->test(TabelSekolah::class)
            ->assertViewHas('daftarSekolah', fn ($d) => $d->count() === 15 && $d->total() === 30)
            ->set('perPage', 10)
            ->assertViewHas('daftarSekolah', fn ($d) => $d->count() === 10)
            ->set('perPage', 100)
            ->assertViewHas('daftarSekolah', fn ($d) => $d->count() === 30)
            ->set('perPage', 999)
            ->assertSet('perPage', 15)
            ->assertViewHas('daftarSekolah', fn ($d) => $d->count() === 15);
    }

    public function test_tabel_spk_pencarian_teks(): void
    {
        $sekolah = $this->buatSekolah('SDN Tujuan', 'Panyabungan');
        $melati = $this->buatGuru('Guru Melati', $sekolah->id);
        $mawar = $this->buatGuru('Guru Mawar', $sekolah->id);
        // Isi cache jarak agar render tidak memanggil API routing eksternal
        JarakJalanCache::create(array_merge(
            JarakJalanCache::kunci(0.86, 99.56, 0.86, 99.56),
            ['jarak_km' => 1.5, 'waktu_menit' => 5, 'sumber' => 'osrm']));
        foreach ([$melati, $mawar] as $guru) {
            HasilSpk::create(['guru_id' => $guru->id, 'sekolah_asal_id' => $sekolah->id,
                'sekolah_tujuan_id' => $sekolah->id, 'jarak_km' => 1.0,
                'jarak_sekarang_km' => 12.0, 'hemat_km' => 11.0, 'status' => 'rekomendasi']);
        }
        $kabid = $this->buatUser('kabid@t.local', 'kabid_ptk');

        Livewire::actingAs($kabid)->test(TabelSpk::class)
            ->assertSee('Guru Melati')
            ->assertSee('Guru Mawar')
            ->set('q', 'Melati')
            ->assertSee('Guru Melati')
            ->assertDontSee('Guru Mawar')
            ->call('resetFilter')
            ->assertSet('q', '')
            ->assertSee('Guru Mawar');
    }

    public function test_tabel_swap_pencarian_teks(): void
    {
        $sekolah = $this->buatSekolah('Sekolah A', 'Panyabungan');
        $anggrek = $this->buatGuru('Guru Anggrek', $sekolah->id);
        $flamboyan = $this->buatGuru('Guru Flamboyan', $sekolah->id);
        $cempaka = $this->buatGuru('Guru Cempaka', $sekolah->id);
        $jarak = ['sekolah_tujuan_a_id' => $sekolah->id, 'sekolah_tujuan_b_id' => $sekolah->id,
            'jarak_a_sekarang_km' => 10, 'jarak_b_sekarang_km' => 10,
            'jarak_a_usulan_km' => 2, 'jarak_b_usulan_km' => 3,
            'hemat_a_km' => 8, 'hemat_b_km' => 7, 'total_hemat_km' => 15,
            'status' => 'menunggu'];
        RekomendasiSwap::create(array_merge($jarak, ['guru_a_id' => $anggrek->id, 'guru_b_id' => $flamboyan->id]));
        RekomendasiSwap::create(array_merge($jarak, ['guru_a_id' => $cempaka->id, 'guru_b_id' => $flamboyan->id]));
        $kabid = $this->buatUser('kabid@t.local', 'kabid_ptk');

        Livewire::actingAs($kabid)->test(TabelSwap::class)
            ->assertSee('Guru Anggrek')
            ->assertSee('Guru Cempaka')
            ->set('q', 'Anggrek')
            ->assertSee('Guru Anggrek')
            ->assertDontSee('Guru Cempaka')
            ->set('q', 'Flamboyan')
            ->assertSee('Guru Anggrek')
            ->assertSee('Guru Cempaka');
    }
}
