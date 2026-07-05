<?php

namespace App\Mail;

use App\Models\Ticket;
use App\Models\TicketMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** Destek talebine yeni yanit bildirimi (iki yonlu kullanilir). */
class TicketRepliedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Ticket $ticket,
        public readonly TicketMessage $message,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: sprintf('[Destek #%d] Yeni yanıt — %s', $this->ticket->id, $this->ticket->subject),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.tickets.replied',
            with: ['ticket' => $this->ticket, 'message' => $this->message],
        );
    }
}
