<?php

// Tes tabel Livewire (aturan proyek): render awal + filter + scope peran.
// Mencakup TabelGuru, TabelSekolah, TabelPengguna, TabelUsulan,
// TabelSpk, TabelSwap, dan TabelAktivitas.

namespace Tests\Feature;

use App\Livewire\TabelAktivitas;
use App\Livewire\TabelGuru;
use App\Livewire\TabelPengguna;
use App\Livewire\TabelSekolah;
use App\Livewire\TabelSpk;
use App\Livewire\TabelSwap;
use App\Livewire\TabelUsulan;
use App\Models\ActivityLog;
use App\Models\Guru;
use App\Models\HasilSpk;
use App\Models\JarakJalanCache;
use App\Models\RekomendasiSwap;
use App\Models\Sekolah;
use App\Models\UsulanPenempatan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class TabelLivewireTest extends TestCase
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

    private function buatGuru(string $nama, string $kecamatan, ?int $sekolahId = null): Guru
    {
        return Guru::create([
            'nip' => 'NIP'.++$this->seq, 'nama' => $nama, 'mapel' => 'Matematika',
            'jenjang' => 'SD', 'domisili_alamat' => 'Jl Uji',
            'kecamatan' => $kecamatan, 'sekolah_id' => $sekolahId,
            'domisili_lat' => 0.86, 'domisili_lng' => 99.56,
            'sekolah_saat_ini' => 'Sekolah A',
        ]);
    }

    private function buatUser(string $email, string $role, array $tambahan = []): User
    {
        return User::create(array_merge([
            'name' => $role, 'email' => $email,
            'password' => Hash::make('rahasia'), 'role' => $role,
        ], $tambahan));
    }

    public function test_tabel_guru_cari_filter_scope(): void
    {
        $sekolah = $this->buatSekolah('Sekolah A', 'Panyabungan');
        $guruCari = $this->buatGuru('Guru Cari', 'Panyabungan', $sekolah->id);
        $this->buatGuru('Guru Lain', 'Siabu');
        $kabid = $this->buatUser('kabid@t.local', 'kabid_ptk');
        $akunGuru = $this->buatUser('guru@t.local', 'guru', ['guru_id' => $guruCari->id]);

        // Render awal: data terlihat (server-side, tanpa JS)
        $this->actingAs($kabid)->get('/guru')->assertOk()->assertSee('Guru Cari');

        // Filter teks + reset
        Livewire::actingAs($kabid)->test(TabelGuru::class)
            ->assertSee('Guru Cari')
            ->set('q', 'Lain')
            ->assertSee('Guru Lain')
            ->assertDontSee('Guru Cari')
            ->call('urutkan', 'mapel')
            ->assertSet('sort', 'mapel')
            ->call('resetFilter')
            ->assertSet('q', '')
            ->assertSee('Guru Cari');

        // Scope peran: guru hanya melihat dirinya sendiri
        Livewire::actingAs($akunGuru)->test(TabelGuru::class)
            ->assertSee('Guru Cari')
            ->assertDontSee('Guru Lain');
    }

    public function test_tabel_sekolah_filter_kecamatan(): void
    {
        $this->buatSekolah('Sekolah A', 'Panyabungan');
        $this->buatSekolah('Sekolah B', 'Siabu');
        $kabid = $this->buatUser('kabid@t.local', 'kabid_ptk');

        $this->actingAs($kabid)->get('/sekolah')->assertOk()->assertSee('Sekolah A');

        Livewire::actingAs($kabid)->test(TabelSekolah::class)
            ->set('kecamatan', 'Siabu')
            ->assertSee('Sekolah B')
            ->assertDontSee('Sekolah A')
            ->call('resetFilter')
            ->assertSee('Sekolah A');
    }

    public function test_tabel_pengguna_filter_peran(): void
    {
        $this->buatUser('guru@t.local', 'guru');
        $kabid = $this->buatUser('kabid@t.local', 'kabid_ptk');

        $this->actingAs($kabid)->get('/pengguna')->assertOk();

        Livewire::actingAs($kabid)->test(TabelPengguna::class)
            ->set('role', 'guru')
            ->assertSee('guru@t.local')
            ->assertDontSee('kabid@t.local');
    }

    public function test_tabel_usulan_filter_status_scope(): void
    {
        $sekolah = $this->buatSekolah('Sekolah A', 'Panyabungan');
        $guru = $this->buatGuru('Guru Usul', 'Panyabungan', $sekolah->id);
        $kabid = $this->buatUser('kabid@t.local', 'kabid_ptk');
        UsulanPenempatan::create(['guru_id' => $guru->id, 'sekolah_id' => $sekolah->id,
            'alasan' => 'Alasan cukup panjang untuk validasi.', 'status' => 'diajukan',
            'diajukan_oleh' => $kabid->id]);

        $this->actingAs($kabid)->get('/usulan')->assertOk()->assertSee('Guru Usul');

        Livewire::actingAs($kabid)->test(TabelUsulan::class)
            ->set('status', 'ditolak')
            ->assertDontSee('Guru Usul')
            ->set('status', 'diajukan')
            ->assertSee('Guru Usul');
    }

    public function test_tabel_spk_swap_aktivitas_render_filter(): void
    {
        $sekolah = $this->buatSekolah('Sekolah A', 'Panyabungan');
        $guru = $this->buatGuru('Guru SPK', 'Panyabungan', $sekolah->id);
        // Isi cache jarak agar render tidak memanggil API routing eksternal
        JarakJalanCache::create(array_merge(
            JarakJalanCache::kunci(0.86, 99.56, 0.86, 99.56),
            ['jarak_km' => 1.5, 'waktu_menit' => 5, 'sumber' => 'osrm']));
        HasilSpk::create(['guru_id' => $guru->id, 'sekolah_asal_id' => $sekolah->id,
            'sekolah_tujuan_id' => $sekolah->id, 'jarak_km' => 1.0,
            'jarak_sekarang_km' => 12.0, 'hemat_km' => 11.0, 'status' => 'rekomendasi']);
        RekomendasiSwap::create(['guru_a_id' => $guru->id, 'guru_b_id' => $guru->id,
            'sekolah_tujuan_a_id' => $sekolah->id, 'sekolah_tujuan_b_id' => $sekolah->id,
            'jarak_a_sekarang_km' => 10, 'jarak_b_sekarang_km' => 10,
            'jarak_a_usulan_km' => 2, 'jarak_b_usulan_km' => 3,
            'hemat_a_km' => 8, 'hemat_b_km' => 7, 'total_hemat_km' => 15,
            'status' => 'menunggu']);
        $kabid = $this->buatUser('kabid@t.local', 'kabid_ptk');
        ActivityLog::catat('uji.sukses', null, 'sukses');
        ActivityLog::catat('uji.gagal', null, 'gagal');

        $this->actingAs($kabid)->get('/spk')->assertOk()->assertSee('Guru SPK');
        $this->actingAs($kabid)->get('/swap')->assertOk()->assertSee('Guru SPK');
        $this->actingAs($kabid)->get('/aktivitas')->assertOk()->assertSee('uji.sukses');

        Livewire::actingAs($kabid)->test(TabelSpk::class)
            ->set('status', 'ditolak')
            ->assertDontSee('Guru SPK')
            ->set('status', 'rekomendasi')
            ->assertSee('Guru SPK');

        Livewire::actingAs($kabid)->test(TabelSwap::class)
            ->set('status', 'ditolak')
            ->assertDontSee('Guru SPK')
            ->set('status', 'menunggu')
            ->assertSee('Guru SPK');

        Livewire::actingAs($kabid)->test(TabelAktivitas::class)
            ->assertViewHas('log', fn ($log) => $log->total() === 2)
            ->set('hasil', 'gagal')
            ->assertViewHas('log', fn ($log) => $log->total() === 1)
            ->assertSee('uji.gagal')
            ->set('hasil', 'sukses')
            ->assertViewHas('log', fn ($log) => $log->total() === 1)
            ->assertSee('uji.sukses');
    }
}
