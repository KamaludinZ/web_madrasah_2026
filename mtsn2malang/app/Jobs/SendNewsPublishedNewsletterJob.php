<?php

namespace App\Jobs;

use App\Services\SubscriptionMailerService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Kirim newsletter berita baru di background — mengirim ke banyak pelanggan
 * langsung di dalam klik tombol "Publikasi" bisa timeout.
 */
class SendNewsPublishedNewsletterJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 900;
    public int $tries = 1;

    public function __construct(public readonly int $newsId)
    {
    }

    public function handle(SubscriptionMailerService $mailer): void
    {
        $mailer->sendNewsPublished($this->newsId);
    }
}
