<?php

namespace App\Mail;

use App\Models\Website;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WebsiteDownAlert extends Mailable
{
    use Queueable, SerializesModels;

    public $website;

    public function __construct(Website $website)
    {
        $this->website = $website;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "{$this->website->url} is down!",
            from: 'do-not-reply@example.com',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.website-down',
            with: [
                'website' => $this->website,
                'timestamp' => now()->format('Y-m-d H:i:s'),
            ],
        );
    }
}