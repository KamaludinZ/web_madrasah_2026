<?php

namespace App\Services;

use App\Mail\NewsPublishedMail;
use App\Models\News;
use App\Models\Setting;
use App\Models\Subscriber;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;

class SubscriptionMailerService
{
    public function sendNewsPublished(News $news): int
    {
        if (! (bool) Setting::get('subscription_enabled', true)) {
            return 0;
        }

        $this->applyRuntimeMailConfig();

        $sent = 0;
        $subscribers = Subscriber::query()
            ->where('is_active', true)
            ->whereNotNull('email')
            ->get();

        foreach ($subscribers as $subscriber) {
            Mail::to($subscriber->email)->send(new NewsPublishedMail($news, $subscriber));
            $sent++;
        }

        return $sent;
    }

    protected function applyRuntimeMailConfig(): void
    {
        Config::set('mail.default', Setting::get('mail_mailer', config('mail.default', 'smtp')));
        Config::set('mail.mailers.smtp.host', Setting::get('mail_host', config('mail.mailers.smtp.host')));
        Config::set('mail.mailers.smtp.port', (int) Setting::get('mail_port', config('mail.mailers.smtp.port')));
        Config::set('mail.mailers.smtp.username', Setting::get('mail_username', config('mail.mailers.smtp.username')));
        Config::set('mail.mailers.smtp.password', Setting::get('mail_password', config('mail.mailers.smtp.password')));
        Config::set('mail.mailers.smtp.encryption', Setting::get('mail_encryption', config('mail.mailers.smtp.encryption')));
        Config::set('mail.from.address', Setting::get('mail_from_address', config('mail.from.address')));
        Config::set('mail.from.name', Setting::get('mail_from_name', config('mail.from.name')));
    }
}
