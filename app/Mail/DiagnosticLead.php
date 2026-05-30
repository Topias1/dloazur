<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Notification Pierre — nouveau lead issu du diagnostic piscine.
 *
 * Suit exactement le pattern ContactMessage (PATTERNS Pattern 7, D-03).
 * replyTo uniquement si l'email est présent (email est optionnel, Req6).
 *
 * Constructor params (promoted readonly) :
 *   prenom      string (requis)
 *   commune     string (requis)
 *   email       string|null (facultatif)
 *   siteWeb     string|null (facultatif)
 *   summary     string     résumé texte du diagnostic (pour WhatsApp pré-rempli)
 *   mesures     array      mesures brutes
 *   triedActions array     actions déjà tentées
 *   diagId      int|null   ID du Diagnostic créé
 */
class DiagnosticLead extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $prenom,
        public readonly string $commune,
        public readonly ?string $email,
        public readonly ?string $siteWeb,
        public readonly string $summary,
        public readonly array $mesures,
        public readonly array $triedActions,
        public readonly ?int $diagId,
    ) {}

    public function envelope(): Envelope
    {
        $replyTo = [];
        if ($this->email) {
            $replyTo[] = new Address($this->email, $this->prenom);
        }

        return new Envelope(
            subject: 'Nouveau diagnostic — Dlo Azur Piscines',
            from: new Address(
                config('mail.from.address', 'contact@dloazurpiscines.com'),
                config('mail.from.name', 'Dlo Azur Piscines'),
            ),
            replyTo: $replyTo,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.diagnostic-lead',
        );
    }
}
