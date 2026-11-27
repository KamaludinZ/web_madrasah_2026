<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * Di produksi (Cloudflare → Traefik → nginx) Laravel hanya melihat IP server
 * Cloudflare. IP asli diambil dari CF-Connecting-IP — hanya bila permintaan
 * memang datang dari Cloudflare, agar header tidak bisa dipalsukan.
 */
class CloudflareClientIpTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['trustedproxy.proxies' => '*']); // sama dengan TRUSTED_PROXIES=* di Docker
        Route::get('/_uji-ip', fn () => response()->json(['ip' => request()->ip(), 'secure' => request()->secure()]));
    }

    private function ipFor(string $remoteAddr, array $headers): array
    {
        return $this->withServerVariables(['REMOTE_ADDR' => $remoteAddr])
            ->withHeaders($headers)
            ->getJson('/_uji-ip')->json();
    }

    public function test_visitor_ip_is_taken_from_cloudflare_header(): void
    {
        $result = $this->ipFor('10.0.1.5', [
            'X-Forwarded-For' => '162.158.19.46',   // IP server Cloudflare (dari Traefik)
            'X-Forwarded-Proto' => 'https',
            'CF-Connecting-IP' => '36.68.10.20',    // pengunjung asli
        ]);

        $this->assertSame('36.68.10.20', $result['ip']);
        $this->assertTrue($result['secure'], 'Deteksi https tetap berfungsi.');
    }

    public function test_forged_header_from_outside_cloudflare_is_ignored(): void
    {
        // Akses langsung tanpa Cloudflare dengan header palsu.
        $this->assertSame('36.80.1.1', $this->ipFor('10.0.1.5', [
            'X-Forwarded-For' => '36.80.1.1',
            'CF-Connecting-IP' => '1.2.3.4',
        ])['ip']);

        $this->assertSame('36.80.1.1', $this->ipFor('36.80.1.1', [
            'CF-Connecting-IP' => '1.2.3.4',
        ])['ip']);

        // Nilai bukan IP diabaikan.
        $this->assertSame('162.158.19.46', $this->ipFor('10.0.1.5', [
            'X-Forwarded-For' => '162.158.19.46',
            'CF-Connecting-IP' => 'bukan-ip',
        ])['ip']);
    }
}
