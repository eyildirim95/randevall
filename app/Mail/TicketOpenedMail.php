<?php

namespace App\Mail;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** Super admin'e yeni destek talebi bildirimi. */
class TicketOpenedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public readonly Ticket $ticket) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: sprintf('[Destek #%d] %s — %s', $this->ticket->id, $this->ticket->business->name, $this->ticket->subject),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.tickets.opened',
            with: ['ticket' => $this->ticket],
        );
    }
}
