<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;

/**
 * Menerapkan konfigurasi email dari Pengaturan Website (tab Email) ke config
 * runtime Laravel. Dipakai oleh OTP login, email tes, dan newsletter supaya
 * semuanya memakai pengaturan yang sama — termasuk saat dijalankan di queue
 * worker (proses terpisah yang tidak melihat perubahan config dari request web).
 */
class MailSettings
{
    public static function apply(): void
    {
        Config::set('mail.default', Setting::get('mail_driver', config('mail.default', 'smtp')));
        Config::set('mail.mailers.smtp.host', Setting::get('mail_host', config('mail.mailers.smtp.host')));
        Config::set('mail.mailers.smtp.port', (int) Setting::get('mail_port', config('mail.mailers.smtp.port')));
        Config::set('mail.mailers.smtp.username', Setting::get('mail_username', config('mail.mailers.smtp.username')));
        Config::set('mail.mailers.smtp.password', Setting::getSecret('mail_password', config('mail.mailers.smtp.password')));
        Config::set('mail.mailers.smtp.encryption', Setting::get('mail_encryption', config('mail.mailers.smtp.encryption')) ?: null);
        Config::set('mail.from.address', Setting::get('mail_from_address', config('mail.from.address')) ?: config('mail.from.address'));
        Config::set('mail.from.name', Setting::get('mail_from_name', config('mail.from.name')) ?: config('mail.from.name'));

        // Mailer yang sudah dibuat sebelumnya masih memegang config lama.
        Mail::purge(config('mail.default'));
    }
}
