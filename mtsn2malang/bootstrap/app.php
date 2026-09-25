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

        // Saat maintenance, panel admin tetap bisa dibuka (untuk menonaktifkan
        // maintenance tanpa URL bypass). livewire/* dibutuhkan panel admin.
        $middleware->preventRequestsDuringMaintenance(except: [
            'admin', 'admin/*', 'livewire/*', 'filament/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
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
