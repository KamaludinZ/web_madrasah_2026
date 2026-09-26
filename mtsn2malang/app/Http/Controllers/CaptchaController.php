<?php

namespace App\Http\Controllers;

use App\Support\NumericCaptcha;

class CaptchaController extends Controller
{
    /** Gambar kode keamanan baru untuk formulir tertentu (login / komentar). */
    public function image(string $context)
    {
        [$body, $contentType] = NumericCaptcha::render($context);

        return response($body, 200, [
            'Content-Type' => $contentType,
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
        ]);
    }
}
