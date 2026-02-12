<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactFormToAdmin extends Mailable
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
            replyTo: [
                new Address($this->formData['email'], $this->formData['full_name']),
            ],
            subject: 'New contact inquiry: '.$this->formData['subject']
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.contact.to-admin'
        );
    }
}
