<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class LoginOtpMail extends Mailable
{
    use Queueable;

    public function __construct(
        public string $otp,
        public int $expiryMinutes,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Kode Verifikasi Login Admin: ' . $this->otp,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.login-otp',
        );
    }
}
