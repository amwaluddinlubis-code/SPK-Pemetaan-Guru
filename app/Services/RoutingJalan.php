<?php

namespace App\Services;

use App\Models\JarakJalanCache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

// Layanan jarak jalan hybrid:
// - Cek cache database dulu (kunci: koordinat dibulatkan 4 desimal).
// - Jika miss, query layanan routing eksternal (OSRM / OpenRouteService).
// - Jika API gagal/timeout, fallback ke Haversine dengan flag sumber.
// Konfigurasi via .env: ROUTING_PROVIDER=osrm|ors, ROUTING_URL, ORS_API_KEY.
class RoutingJalan
{
    // Hasil: ['jarak_km' => float, 'waktu_menit' => ?int, 'sumber' => 'jalan'|'lurus', 'cached' => bool]
    public function jarak(float $latAsal, float $lngAsal, float $latTujuan, float $lngTujuan): array
    {
        // 1. Cek cache
        $cache = JarakJalanCache::cari($latAsal, $lngAsal, $latTujuan, $lngTujuan);
        if ($cache) {
            return [
                'jarak_km' => (float) $cache->jarak_km,
                'waktu_menit' => $cache->waktu_menit,
                'sumber' => 'jalan',
                'cached' => true,
            ];
        }

        // 2. Query API routing
        $hasil = $this->queryApi($latAsal, $lngAsal, $latTujuan, $lngTujuan);
        if ($hasil) {
            JarakJalanCache::create(array_merge(
                JarakJalanCache::kunci($latAsal, $lngAsal, $latTujuan, $lngTujuan),
                ['jarak_km' => $hasil['jarak_km'], 'waktu_menit' => $hasil['waktu_menit'], 'sumber' => $hasil['provider']]
            ));

            return array_merge($hasil, ['sumber' => 'jalan', 'cached' => false]);
        }

        // 3. Fallback: Haversine (garis lurus)
        Log::warning('Routing API gagal, fallback ke Haversine', compact('latAsal', 'lngAsal', 'latTujuan', 'lngTujuan'));

        return [
            'jarak_km' => $this->haversine($latAsal, $lngAsal, $latTujuan, $lngTujuan),
            'waktu_menit' => null,
            'sumber' => 'lurus',
            'cached' => false,
        ];
    }

    private function queryApi(float $latAsal, float $lngAsal, float $latTujuan, float $lngTujuan): ?array
    {
        $provider = config('services.routing.provider', 'osrm');

        try {
            return match ($provider) {
                'ors' => $this->queryOrs($latAsal, $lngAsal, $latTujuan, $lngTujuan),
                default => $this->queryOsrm($latAsal, $lngAsal, $latTujuan, $lngTujuan),
            };
        } catch (\Throwable $e) {
            Log::warning('Routing API error: '.$e->getMessage());

            return null;
        }
    }

    // OSRM public demo — tanpa API key, cocok untuk testing.
    // Untuk production, self-host OSRM atau pakai provider berbayar.
    private function queryOsrm(float $latAsal, float $lngAsal, float $latTujuan, float $lngTujuan): ?array
    {
        $base = rtrim(config('services.routing.osrm_url', 'https://router.project-osrm.org'), '/');
        $url = "{$base}/route/v1/driving/{$lngAsal},{$latAsal};{$lngTujuan},{$latTujuan}?overview=false";

        $res = Http::timeout(10)->get($url);
        if (! $res->successful()) {
            return null;
        }

        $route = $res->json('routes.0');
        if (! $route) {
            return null;
        }

        return [
            'jarak_km' => round($route['distance'] / 1000, 2),
            'waktu_menit' => (int) round($route['duration'] / 60),
            'provider' => 'osrm',
        ];
    }

    // OpenRouteService — butuh API key (gratis 2000 req/hari).
    private function queryOrs(float $latAsal, float $lngAsal, float $latTujuan, float $lngTujuan): ?array
    {
        $key = config('services.routing.ors_key');
        if (! $key) {
            return null;
        }

        $res = Http::timeout(10)
            ->withHeaders(['Authorization' => $key])
            ->post('https://api.openrouteservice.org/v2/directions/driving-car', [
                'coordinates' => [[$lngAsal, $latAsal], [$lngTujuan, $latTujuan]],
            ]);

        if (! $res->successful()) {
            return null;
        }

        $seg = $res->json('routes.0.summary');
        if (! $seg) {
            return null;
        }

        return [
            'jarak_km' => round($seg['distance'] / 1000, 2),
            'waktu_menit' => (int) round($seg['duration'] / 60),
            'provider' => 'ors',
        ];
    }

    // Haversine: garis lurus dalam km (fallback & komputasi massal).
    public function haversine(float $latAsal, float $lngAsal, float $latTujuan, float $lngTujuan): float
    {
        $r = 6371;
        $dLat = deg2rad($latTujuan - $latAsal);
        $dLng = deg2rad($lngTujuan - $lngAsal);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($latAsal)) * cos(deg2rad($latTujuan)) * sin($dLng / 2) ** 2;

        return round(2 * $r * asin(sqrt($a)), 2);
    }
}
