<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\IpUtils;
use Symfony\Component\HttpFoundation\Response;

/**
 * IP pengunjung asli di balik Cloudflare → Traefik (Coolify) → nginx.
 *
 * Traefik membuang X-Forwarded-For dari Cloudflare dan menggantinya dengan IP
 * server Cloudflare (terlihat di log produksi: 162.158.x.x, 172.68.x.x, ...).
 * Akibatnya semua pengunjung yang lewat server Cloudflare yang sama dianggap
 * satu orang: batas percobaan login/pengaduan/komentar dipakai bersama (satu
 * orang bisa mengunci admin) dan statistik pengunjung salah.
 *
 * Header CF-Connecting-IP (IP asli dari Cloudflare) HANYA dipakai bila
 * permintaan benar-benar datang dari rentang IP resmi Cloudflare — kalau
 * tidak, siapa pun bisa memalsukan header itu.
 */
class UseCloudflareClientIp
{
    /** https://www.cloudflare.com/ips/ */
    public const CLOUDFLARE_RANGES = [
        '173.245.48.0/20', '103.21.244.0/22', '103.22.200.0/22', '103.31.4.0/22',
        '141.101.64.0/18', '108.162.192.0/18', '190.93.240.0/20', '188.114.96.0/20',
        '197.234.240.0/22', '198.41.128.0/17', '162.158.0.0/15', '104.16.0.0/13',
        '104.24.0.0/14', '172.64.0.0/13', '131.0.72.0/22',
        '2400:cb00::/32', '2606:4700::/32', '2803:f800::/32', '2405:b500::/32',
        '2405:8100::/32', '2a06:98c0::/29', '2c0f:f248::/32',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $visitorIp = $request->headers->get('CF-Connecting-IP');

        if ($visitorIp !== null
            && filter_var($visitorIp, FILTER_VALIDATE_IP)
            && IpUtils::checkIp((string) $request->ip(), self::CLOUDFLARE_RANGES)) {
            // Proxy terdekat (Traefik) tetap tepercaya — hanya daftar IP
            // diteruskan yang diganti, sehingga deteksi https tidak berubah.
            $request->headers->set('X-Forwarded-For', $visitorIp);
            $request->server->set('HTTP_X_FORWARDED_FOR', $visitorIp);
        }

        return $next($request);
    }
}
