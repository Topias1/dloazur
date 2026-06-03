<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactMessage extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $firstname,
        public readonly string $lastname,
        public readonly string $email,
        public readonly string $phone,
        public readonly string $message,
    ) {}

    /** Nom complet « Prénom Nom » pour l'en-tête reply-to et l'affichage. */
    public function name(): string
    {
        return trim($this->firstname.' '.$this->lastname);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nouveau message — Dlo Azur Piscines',
            from: new Address(
                config('mail.from.address', 'contact@dloazurpiscines.com'),
                config('mail.from.name', 'Dlo Azur Piscines'),
            ),
            replyTo: [
                new Address($this->email, $this->name()),
            ],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.contact-message',
        );
    }
}
