<?php

namespace App\Mail;

use App\Models\News;
use App\Models\Subscriber;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewsPublishedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public News $news,
        public Subscriber $subscriber
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Berita Terbaru: ' . $this->news->title,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.news-published',
            with: [
                'newsTitle' => $this->news->title,
                'newsExcerpt' => $this->news->excerpt ?: 'Ada berita terbaru dari website kami.',
                'newsUrl' => route('berita.show', $this->news->slug),
                'subscriberName' => $this->subscriber->name ?: 'Sahabat MTsN 2 Kota Malang',
                'unsubscribeUrl' => route('subscription.unsubscribe', $this->subscriber->unsubscribe_token),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
