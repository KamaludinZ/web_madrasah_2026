<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Cegah clickjacking
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // Cegah MIME-type sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Filter XSS bawaan browser lama sudah dihapus dan justru bisa
        // disalahgunakan; rekomendasi saat ini: matikan ("0"), andalkan CSP.
        $response->headers->set('X-XSS-Protection', '0');

        // Content-Security-Policy dasar yang tidak membatasi skrip/gambar
        // (konten berita lama memuat embed YouTube/Instagram & gambar luar),
        // tetapi menutup celah umum: <base> palsu yang membelokkan semua link,
        // plugin <object>/<embed>, form yang dikirim ke situs lain, dan
        // halaman yang dibingkai situs lain (clickjacking).
        if (! $response->headers->has('Content-Security-Policy')) {
            $csp = "base-uri 'self'; object-src 'none'; form-action 'self'; frame-ancestors 'self'";
            if (config('app.env') === 'production') {
                $csp .= '; upgrade-insecure-requests';
            }
            $response->headers->set('Content-Security-Policy', $csp);
        }

        // Paksa HTTPS (aktifkan jika production sudah full HTTPS)
        if (config('app.env') === 'production') {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        // Batasi informasi referrer
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Batasi akses fitur browser berbahaya
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=()');

        // Hapus header yang mengekspos teknologi server
        $response->headers->remove('X-Powered-By');
        $response->headers->remove('Server');

        return $response;
    }
}
