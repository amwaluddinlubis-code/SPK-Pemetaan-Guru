<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrustProxiesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Di balik reverse proxy (ngrok di depan Herd), URL yang dibuat aplikasi
     * wajib mengikuti skema & host asli dari header X-Forwarded-*. Jika tidak,
     * URL form & aset menunjuk ke alamat lokal http dan diblokir browser
     * sebagai mixed content saat aplikasi dibuka lewat HTTPS.
     */
    public function test_url_login_mengikuti_header_forwarded(): void
    {
        $response = $this->withHeaders([
            'X-Forwarded-Proto' => 'https',
            'X-Forwarded-Host' => 'contoh.ngrok-free.dev',
        ])->get('/login');

        $response->assertOk();
        $response->assertSee('action="https://contoh.ngrok-free.dev/login"', false);
    }

    public function test_tanpa_header_forwarded_url_ikut_request_biasa(): void
    {
        $response = $this->get('/login');

        $response->assertOk();
        $this->assertMatchesRegularExpression(
            '/<form[^>]+action="http:\/\/[^"]+\/login"/',
            (string) $response->getContent(),
        );
    }
}
