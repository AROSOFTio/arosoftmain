<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactFormThankYou extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * @param array<string, string> $formData
     */
    public function __construct(public array $formData)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(
                config('mail.from.address', 'no-reply@arosoft.io'),
                config('mail.from.name', 'Arosoft Innovations Ltd')
            ),
            subject: 'Thank you for contacting Arosoft Innovations Ltd'
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.contact.thank-you'
        );
    }
}
