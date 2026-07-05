<?php

namespace App\Mail;

use App\Models\DemoRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** Super admin'e yeni demo talebi bildirimi. */
class DemoRequestReceivedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public readonly DemoRequest $demoRequest) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'BooKıbrıs — Yeni Demo Talebi: '.$this->demoRequest->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.demo-request',
            with: ['demoRequest' => $this->demoRequest],
        );
    }
}
