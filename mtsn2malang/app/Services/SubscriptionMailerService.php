<?php

namespace App\Services;

use App\Jobs\SendNewsPublishedNewsletterJob;
use App\Mail\NewsPublishedMail;
use App\Models\News;
use App\Models\Setting;
use App\Models\Subscriber;
use App\Support\MailSettings;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SubscriptionMailerService
{
    /**
     * Antrekan newsletter jika berita sudah terbit dan belum pernah dikirim.
     * Dipanggil setiap kali berita disimpan/diterbitkan dari panel admin.
     */
    public function queueIfPublished(News $news): void
    {
        if ($news->status !== 'published'
            || $news->published_at === null
            || $news->published_at->isFuture()
            || $news->newsletter_sent_at !== null
            || ! (bool) Setting::get('subscription_enabled', true)) {
            return;
        }

        SendNewsPublishedNewsletterJob::dispatch($news->id)->afterCommit();
    }

    public function sendNewsPublished(int $newsId): int
    {
        // Tandai terkirim secara atomik lebih dulu: jika job ini terpanggil dua
        // kali (klik ganda, retry), hanya satu yang benar-benar mengirim.
        $claimed = News::whereKey($newsId)
            ->where('status', 'published')
            ->whereNull('newsletter_sent_at')
            ->update(['newsletter_sent_at' => now()]);

        if ($claimed === 0 || ! (bool) Setting::get('subscription_enabled', true)) {
            return 0;
        }

        $news = News::findOrFail($newsId);

        MailSettings::apply();

        $sent = 0;

        Subscriber::query()
            ->where('is_active', true)
            ->whereNotNull('email')
            ->each(function (Subscriber $subscriber) use ($news, &$sent) {
                try {
                    Mail::to($subscriber->email)->send(new NewsPublishedMail($news, $subscriber));
                    $sent++;
                } catch (\Throwable $e) {
                    Log::warning('Gagal mengirim newsletter', [
                        'news_id' => $news->id,
                        'subscriber_id' => $subscriber->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            });

        return $sent;
    }
}
