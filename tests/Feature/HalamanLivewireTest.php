<?php

// Tes Full Livewire: semua halaman modul utama dirender komponen Livewire
// halaman penuh (route → komponen), dan komponen halaman hasil konversi
// (Simulasi, Kualitas Data, Aksesibilitas) berperilaku reaktif.

namespace Tests\Feature;

use App\Livewire\AksesibilitasLivewire;
use App\Livewire\Dashboard;
use App\Livewire\DashboardPemda;
use App\Livewire\KualitasDataLivewire;
use App\Livewire\PengaturanAkses;
use App\Livewire\Proyeksi;
use App\Livewire\SimulasiLivewire;
use App\Livewire\TabelAktivitas;
use App\Livewire\TabelDesa;
use App\Livewire\TabelDistribusi;
use App\Livewire\TabelGuru;
use App\Livewire\TabelKabupaten;
use App\Livewire\TabelKecamatan;
use App\Livewire\TabelPengguna;
use App\Livewire\TabelProvinsi;
use App\Livewire\TabelSekolah;
use App\Livewire\TabelSpk;
use App\Livewire\TabelSwap;
use App\Livewire\TabelUsulan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class HalamanLivewireTest extends TestCase
{
    use RefreshDatabase;

    private function kabid(): User
    {
        return User::create([
            'name' => 'Kabid', 'email' => 'kabid@t.local',
            'password' => Hash::make('rahasia123'), 'role' => 'kabid_ptk',
        ]);
    }

    public function test_halaman_modul_dirender_komponen_livewire(): void
    {
        $kabid = $this->kabid();

        $halaman = [
            '/' => Dashboard::class,
            '/pemda' => DashboardPemda::class,
            '/guru' => TabelGuru::class,
            '/sekolah' => TabelSekolah::class,
            '/pengguna' => TabelPengguna::class,
            '/usulan' => TabelUsulan::class,
            '/spk' => TabelSpk::class,
            '/swap' => TabelSwap::class,
            '/distribusi' => TabelDistribusi::class,
            '/aktivitas' => TabelAktivitas::class,
            '/proyeksi' => Proyeksi::class,
            '/pengaturan/akses' => PengaturanAkses::class,
            '/wilayah/provinsi' => TabelProvinsi::class,
            '/wilayah/kabupaten' => TabelKabupaten::class,
            '/wilayah/kecamatan' => TabelKecamatan::class,
            '/wilayah/desa' => TabelDesa::class,
        ];

        foreach ($halaman as $url => $komponen) {
            $this->actingAs($kabid)->get($url)
                ->assertOk()
                ->assertSeeLivewire($komponen);
        }
    }

    public function test_simulasi_livewire_menghitung_tanpa_reload(): void
    {
        $this->actingAs($this->kabid());

        Livewire::test(SimulasiLivewire::class)
            ->assertSet('hasil', null)
            ->set('ambang_km', 15)
            ->call('hitung')
            ->assertSet('hasil.jumlah_kandidat', 0)
            ->assertSee('Hasil Simulasi')
            ->assertSee('simulasi hipotetis');
    }

    public function test_kualitas_data_livewire_filter_reaktif(): void
    {
        $this->actingAs($this->kabid());

        Livewire::test(KualitasDataLivewire::class)
            ->set('dampak', 'tinggi')
            ->assertSet('dampak', 'tinggi')
            ->call('resetFilter')
            ->assertSet('dampak', '')
            ->assertSet('jenis', '');
    }

    public function test_aksesibilitas_livewire_filter_reaktif(): void
    {
        $this->actingAs($this->kabid());

        Livewire::test(AksesibilitasLivewire::class)
            ->set('kategori_daerah', 'terpencil')
            ->assertSet('kategori_daerah', 'terpencil')
            ->assertSee('Haversine (garis lurus)')
            ->call('resetFilter')
            ->assertSet('kategori_daerah', '');
    }
}
