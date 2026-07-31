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

        // Aktifkan XSS filter browser lama
        $response->headers->set('X-XSS-Protection', '1; mode=block');

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
