<?php

// Tes Pengaturan Pemda: halaman khusus administrator, simpan/lepas konteks,
// dan konteks ketat menyembunyikan sekolah/guru di luar wilayah terpilih —
// termasuk varian ejaan Dapodik ("Merapi") vs Kemendagri ("Marapi").

namespace Tests\Feature;

use App\Livewire\PengaturanPemdaLivewire;
use App\Models\Guru;
use App\Models\Sekolah;
use App\Models\User;
use App\Models\WilayahKabupaten;
use App\Models\WilayahKecamatan;
use App\Models\WilayahProvinsi;
use App\Services\KonteksPemda;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class PengaturanPemdaTest extends TestCase
{
    use RefreshDatabase;

    private int $seq = 0;

    private function fixtures(): array
    {
        WilayahProvinsi::create(['kode' => '12', 'nama' => 'SUMATERA UTARA']);
        WilayahKabupaten::create(['kode' => '1213', 'provinsi_kode' => '12', 'nama' => 'KAB. MANDAILING NATAL']);
        WilayahKabupaten::create(['kode' => '1203', 'provinsi_kode' => '12', 'nama' => 'KAB. TAPANULI SELATAN']);
        WilayahKecamatan::create(['kode' => '121301', 'kabupaten_kode' => '1213', 'nama' => 'Panyabungan']);
        WilayahKecamatan::create(['kode' => '121309', 'kabupaten_kode' => '1213', 'nama' => 'Lembah Sorik Marapi']);
        WilayahKecamatan::create(['kode' => '120301', 'kabupaten_kode' => '1203', 'nama' => 'Sipirok']);

        $kabid = User::create([
            'name' => 'Kabid', 'email' => 'kabid@t.local',
            'password' => Hash::make('rahasia'), 'role' => 'kabid_ptk',
        ]);
        $guruUser = User::create([
            'name' => 'Guru', 'email' => 'guru@t.local',
            'password' => Hash::make('rahasia'), 'role' => 'guru',
        ]);

        return compact('kabid', 'guruUser');
    }

    private function buatSekolah(string $nama, string $kecamatan): Sekolah
    {
        return Sekolah::create([
            'npsn' => 'NPSN'.++$this->seq, 'nama' => $nama,
            'alamat' => 'Jl Tes', 'kecamatan' => $kecamatan,
            'lat' => 0.86, 'lng' => 99.56,
        ]);
    }

    private function buatGuru(string $nama, string $kecamatan, ?int $sekolahId): Guru
    {
        return Guru::create([
            'nip' => 'NIP'.++$this->seq, 'nama' => $nama, 'mapel' => 'Matematika',
            'jenjang' => 'SD', 'domisili_alamat' => 'Jl Uji',
            'kecamatan' => $kecamatan, 'sekolah_id' => $sekolahId,
            'domisili_lat' => 0.86, 'domisili_lng' => 99.56,
            'sekolah_saat_ini' => 'Sekolah Uji',
        ]);
    }

    public function test_halaman_khusus_administrator(): void
    {
        $f = $this->fixtures();

        $this->get('/pengaturan/pemda')->assertRedirect('/login');
        $this->actingAs($f['guruUser'])->get('/pengaturan/pemda')->assertForbidden();
        $this->actingAs($f['kabid'])->get('/pengaturan/pemda')->assertOk()
            ->assertSee('Pengaturan Pemda');
    }

    public function test_konteks_kabupaten_menyembunyikan_data_luar_wilayah(): void
    {
        $f = $this->fixtures();
        $a = $this->buatSekolah('SD A', 'Panyabungan');
        $this->buatSekolah('SD B', 'Lembah Sorik Merapi'); // ejaan Dapodik
        $c = $this->buatSekolah('SD C', 'Sipirok');        // kabupaten lain
        $this->buatGuru('Guru A', 'Panyabungan', $a->id);
        $this->buatGuru('Guru C', 'Sipirok', $c->id);

        $this->assertFalse(KonteksPemda::aktif());
        $this->assertSame(3, Sekolah::count());

        $this->actingAs($f['kabid']);
        Livewire::test(PengaturanPemdaLivewire::class)
            ->set('level', 'kabupaten')->set('kodeProvinsi', '12')->set('kodeKabupaten', '1213')
            ->call('simpan')
            ->assertRedirect(route('pengaturan.pemda'));

        $this->assertTrue(KonteksPemda::aktif());
        $this->assertSame('Pemerintah Kabupaten Mandailing Natal', KonteksPemda::namaPemda());
        // SD C (Tapanuli Selatan) tersembunyi; SD B tetap tampil walau ejaannya
        // "Merapi" sedangkan referensi Kemendagri menulis "Marapi".
        $this->assertSame(2, Sekolah::count());
        $this->assertSame(3, Sekolah::withoutGlobalScopes()->count());
        $this->assertSame(1, Guru::count());
        // Peta desa mengenal kedua ejaan.
        $this->assertSame('121309', KonteksPemda::petaKodeKecamatan()['LEMBAH SORIK MERAPI'] ?? null);
    }

    public function test_konteks_provinsi_dan_lepas_konteks(): void
    {
        $f = $this->fixtures();
        $this->buatSekolah('SD A', 'Panyabungan');
        $this->buatSekolah('SD C', 'Sipirok');

        $this->actingAs($f['kabid']);
        Livewire::test(PengaturanPemdaLivewire::class)
            ->set('level', 'provinsi')->set('kodeProvinsi', '12')
            ->call('simpan')
            ->assertRedirect(route('pengaturan.pemda'));

        $this->assertSame('Pemerintah Provinsi Sumatera Utara', KonteksPemda::namaPemda());
        $this->assertSame(2, Sekolah::count()); // dua kabupaten, satu provinsi

        Livewire::test(PengaturanPemdaLivewire::class)
            ->call('lepas')
            ->assertRedirect(route('pengaturan.pemda'));
        $this->assertFalse(KonteksPemda::aktif());
        $this->assertSame(2, Sekolah::count());
    }

    public function test_validasi_pasangan_provinsi_kabupaten(): void
    {
        $f = $this->fixtures();
        WilayahProvinsi::create(['kode' => '11', 'nama' => 'ACEH']);
        WilayahKabupaten::create(['kode' => '1101', 'provinsi_kode' => '11', 'nama' => 'KAB. SIMEULUE']);

        // Kabupaten bukan anak provinsi terpilih → ditolak.
        $this->actingAs($f['kabid']);
        Livewire::test(PengaturanPemdaLivewire::class)
            ->set('level', 'kabupaten')->set('kodeProvinsi', '12')->set('kodeKabupaten', '1101')
            ->call('simpan')
            ->assertHasErrors('kodeKabupaten');
        $this->assertFalse(KonteksPemda::aktif());

        // Level kabupaten tanpa kabupaten → ditolak.
        Livewire::test(PengaturanPemdaLivewire::class)
            ->set('level', 'kabupaten')->set('kodeProvinsi', '12')->set('kodeKabupaten', '')
            ->call('simpan')
            ->assertHasErrors('kodeKabupaten');
    }
}
