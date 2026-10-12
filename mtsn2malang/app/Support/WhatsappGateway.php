<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Kirim pesan WhatsApp lewat gateway pihak ketiga yang dipilih di
 * Pengaturan Website → tab Notifikasi: Fonnte (api.fonnte.com) atau
 * BalesOtomatis.id (api.balesotomatis.id).
 */
class WhatsappGateway
{
    public const PROVIDERS = [
        'fonnte'        => 'Fonnte (fonnte.com)',
        'balesotomatis' => 'BalesOtomatis.id',
    ];

    public static function provider(): ?string
    {
        $provider = Setting::get('wa_provider');

        return array_key_exists((string) $provider, self::PROVIDERS) ? $provider : null;
    }

    public static function isConfigured(): bool
    {
        return match (static::provider()) {
            'fonnte'        => filled(Setting::getSecret('wa_fonnte_token')),
            'balesotomatis' => filled(Setting::getSecret('wa_balesotomatis_api_key')) && filled(Setting::get('wa_balesotomatis_number_id')),
            default         => false,
        };
    }

    /**
     * Ubah nomor ke format internasional tanpa tanda: 0812… / +62 812… → 62812….
     * Mengembalikan null jika bukan nomor yang masuk akal.
     */
    public static function normalizeNumber(?string $number): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $number);

        if (str_starts_with($digits, '0')) {
            $digits = '62' . substr($digits, 1);
        } elseif (str_starts_with($digits, '8')) {
            $digits = '62' . $digits;
        }

        return strlen($digits) >= 10 && strlen($digits) <= 15 ? $digits : null;
    }

    /**
     * @throws RuntimeException jika gateway belum diatur, nomor tidak valid,
     *                          atau gateway menolak pesan.
     */
    public static function send(string $number, string $message): void
    {
        $target = static::normalizeNumber($number);
        if ($target === null) {
            throw new RuntimeException("Nomor WhatsApp tidak valid: {$number}");
        }

        match (static::provider()) {
            'fonnte'        => static::sendViaFonnte($target, $message),
            'balesotomatis' => static::sendViaBalesOtomatis($target, $message),
            default         => throw new RuntimeException('Gateway WhatsApp belum dipilih di Pengaturan → Notifikasi.'),
        };
    }

    private static function sendViaFonnte(string $target, string $message): void
    {
        $token = Setting::getSecret('wa_fonnte_token');
        if (blank($token)) {
            throw new RuntimeException('Token Fonnte belum diisi.');
        }

        $response = Http::timeout(20)
            ->withHeaders(['Authorization' => $token])
            ->asForm()
            ->post('https://api.fonnte.com/send', [
                'target'      => $target,
                'message'     => $message,
                'countryCode' => '62',
            ]);

        // Fonnte selalu membalas HTTP 200; gagal ditandai "status": false + "reason".
        if ($response->failed() || $response->json('status') !== true) {
            throw new RuntimeException('Fonnte: ' . ($response->json('reason') ?? $response->json('detail') ?? 'HTTP ' . $response->status()));
        }
    }

    private static function sendViaBalesOtomatis(string $target, string $message): void
    {
        $apiKey   = Setting::getSecret('wa_balesotomatis_api_key');
        $numberId = Setting::get('wa_balesotomatis_number_id');
        if (blank($apiKey) || blank($numberId)) {
            throw new RuntimeException('API Key / Number ID BalesOtomatis.id belum diisi.');
        }

        $response = Http::timeout(20)
            ->asJson()
            ->post('https://api.balesotomatis.id/public/v1/send_personal_message', [
                'api_key'       => $apiKey,
                'number_id'     => (string) $numberId,
                'enable_typing' => '1',
                'method_send'   => 'async',
                'phone_no'      => $target,
                'country_code'  => '62',
                'message'       => $message,
            ]);

        $status = $response->json('status');
        if ($response->failed() || $status === false || $status === 'error') {
            throw new RuntimeException('BalesOtomatis.id: ' . ($response->json('message') ?? 'HTTP ' . $response->status()));
        }
    }
}
