<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);

        // Di balik reverse proxy (Traefik/Caddy milik Coolify) yang menangani
        // HTTPS, Laravel perlu mempercayai header X-Forwarded-* agar URL,
        // redirect, dan cookie "secure" memakai https serta IP pengunjung asli
        // terbaca. Diaktifkan lewat env TRUSTED_PROXIES (mis. "*" di Docker,
        // karena container hanya bisa dijangkau lewat proxy). Kosong = mati.
        if (filled($trustedProxies = env('TRUSTED_PROXIES'))) {
            $middleware->trustProxies(
                at: $trustedProxies === '*' ? '*' : array_map('trim', explode(',', $trustedProxies)),
            );
        }

        // Saat maintenance, panel admin tetap bisa dibuka (untuk menonaktifkan
        // maintenance tanpa URL bypass). livewire/* dibutuhkan panel admin.
        $middleware->preventRequestsDuringMaintenance(except: [
            'admin', 'admin/*', 'livewire/*', 'filament/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // "This page has expired" (419) karena token CSRF tidak cocok bawaannya
        // tidak pernah dicatat Laravel, sehingga penyebabnya tidak bisa dilacak
        // di production. Catat ringkas (tanpa nilai token) untuk diagnosis.
        $exceptions->stopIgnoring(\Illuminate\Session\TokenMismatchException::class);
        $exceptions->report(function (\Illuminate\Session\TokenMismatchException $e) {
            $request = request();
            $session = $request->hasSession() ? $request->session() : null;

            \Illuminate\Support\Facades\Log::warning('CSRF token mismatch (419)', [
                'path'                  => $request->path(),
                'user_id'               => $request->user()?->getAuthIdentifier(),
                'session_cookie_sent'   => $request->cookies->has(config('session.cookie')),
                'session_id_prefix'     => $session ? substr($session->getId(), 0, 8) : null,
                'session_token_present' => filled($session?->token()),
                'request_token_present' => filled($request->header('X-CSRF-TOKEN') ?: $request->input('_token')),
                'remember_cookie_sent'  => collect($request->cookies->keys())->contains(fn ($k) => str_starts_with($k, 'remember_')),
                'cookie_names'          => $request->cookies->keys(),
            ]);

            return false;
        });

        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e, $request) {
            return response()->view('errors.404', [], 404);
        });

        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\HttpException $e, $request) {
            if ($e->getStatusCode() === 503) {
                $rawEndTime = \App\Models\Setting::get('maintenance_end_time');

                // Kirim ISO 8601 dengan offset zona waktu eksplisit: input datetime-local /
                // Carbon toString tidak menyertakan zona waktu, sehingga `new Date(...)` di
                // browser pengunjung akan menafsirkannya sebagai zona waktu LOKAL PENGUNJUNG,
                // bukan zona waktu server — membuat hitung mundur meleset (atau langsung
                // "Segera online!") untuk siapa pun yang zona waktu browsernya beda dari server.
                $endTime = null;
                if (filled($rawEndTime)) {
                    try {
                        $endTime = \Illuminate\Support\Carbon::parse($rawEndTime, config('app.timezone'))->toIso8601String();
                    } catch (\Throwable) {
                        $endTime = null;
                    }
                }

                return response()->view('errors.maintenance', [
                    'endTime' => $endTime,
                ], 503);
            }
            return response()->view('errors.500', [], $e->getStatusCode());
        });
    })->create();
