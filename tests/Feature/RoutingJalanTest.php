<?php

namespace Tests\Feature;

use App\Models\JarakJalanCache;
use App\Services\RoutingJalan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RoutingJalanTest extends TestCase
{
    use RefreshDatabase;

    public function test_cache_dipakai_tanpa_hit_api(): void
    {
        JarakJalanCache::create([
            ...JarakJalanCache::kunci(-0.1234, 99.5678, -0.2345, 99.6789),
            'jarak_km' => 15.50,
            'waktu_menit' => 30,
            'sumber' => 'osrm',
        ]);

        // API tidak boleh dipanggil bila cache ada
        Http::preventStrayRequests();

        $hasil = app(RoutingJalan::class)->jarak(-0.1234, 99.5678, -0.2345, 99.6789);

        $this->assertEquals(15.50, $hasil['jarak_km']);
        $this->assertEquals('jalan', $hasil['sumber']);
        $this->assertTrue($hasil['cached']);
    }

    public function test_fallback_haversine_saat_api_gagal(): void
    {
        Http::fake(fn () => Http::response(null, 500));

        $hasil = app(RoutingJalan::class)->jarak(-0.1234, 99.5678, -0.2345, 99.6789);

        $this->assertEquals('lurus', $hasil['sumber']);
        $this->assertFalse($hasil['cached']);
        $this->assertGreaterThan(0, $hasil['jarak_km']);
        // Tidak ada cache yang ditulis saat fallback
        $this->assertEquals(0, JarakJalanCache::count());
    }

    public function test_api_osrm_disimpan_ke_cache(): void
    {
        Http::fake([
            'router.project-osrm.org/*' => Http::response([
                'routes' => [['distance' => 12500, 'duration' => 1800]],
            ], 200),
        ]);

        $hasil = app(RoutingJalan::class)->jarak(-0.1234, 99.5678, -0.2345, 99.6789);

        $this->assertEquals('jalan', $hasil['sumber']);
        $this->assertEquals(12.50, $hasil['jarak_km']);
        $this->assertEquals(30, $hasil['waktu_menit']);
        $this->assertEquals(1, JarakJalanCache::count());
    }

    public function test_kunci_cache_bulatkan_4_desimal(): void
    {
        $kunci = JarakJalanCache::kunci(-0.123456789, 99.567891234, -0.234567891, 99.678912345);

        $this->assertEquals(-0.1235, $kunci['lat_asal']);
        $this->assertEquals(99.5679, $kunci['lng_asal']);
    }
}
