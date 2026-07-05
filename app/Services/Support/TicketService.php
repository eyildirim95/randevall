<?php

namespace App\Services\Support;

use App\Enums\TicketStatus;
use App\Mail\TicketOpenedMail;
use App\Mail\TicketRepliedMail;
use App\Models\Business;
use App\Models\SystemSetting;
use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Destek talebi akisi: isletme acar, super admin yanitlar.
 * Durum gecisleri ve e-posta bildirimleri tek noktadan yonetilir.
 *
 *   isletme mesaji  → status: open      (super admin'e mail)
 *   admin mesaji    → status: answered  (talebi acana mail)
 *   kapatma         → status: closed
 */
class TicketService
{
    public function open(Business $business, User $user, array $data): Ticket
    {
        $ticket = DB::transaction(function () use ($business, $user, $data) {
            $ticket = new Ticket([
                'subject' => $data['subject'],
                'category' => $data['category'],
                'priority' => $data['priority'],
            ]);
            $ticket->business_id = $business->id;
            $ticket->user_id = $user->id;
            $ticket->last_reply_at = now();
            $ticket->save();

            $ticket->messages()->create([
                'user_id' => $user->id,
                'is_admin' => false,
                'body' => $data['body'],
            ]);

            return $ticket;
        });

        $this->notifyAdmin(new TicketOpenedMail($ticket));

        return $ticket;
    }

    public function reply(Ticket $ticket, User $user, string $body, bool $asAdmin): TicketMessage
    {
        $message = DB::transaction(function () use ($ticket, $user, $body, $asAdmin) {
            $message = $ticket->messages()->create([
                'user_id' => $user->id,
                'is_admin' => $asAdmin,
                'body' => $body,
            ]);

            $ticket->forceFill([
                'status' => $asAdmin ? TicketStatus::Answered : TicketStatus::Open,
                'last_reply_at' => now(),
                'closed_at' => null,
            ])->save();

            return $message;
        });

        if ($asAdmin) {
            // Panel bildirim merkezine dusur
            \App\Services\Notifications\PanelNotifier::notify(
                $ticket->business,
                'ticket_replied',
                sprintf('Destek talebinize yanıt geldi: %s', $ticket->subject),
                route('panel.tickets.show', [$ticket->business, $ticket]),
            );

            // Talebi acan isletme kullanicisina bildir
            $recipient = $ticket->user?->email ?? $ticket->business->email;

            if ($recipient) {
                try {
                    Mail::to($recipient)->queue(new TicketRepliedMail($ticket, $message));
                } catch (\Throwable $e) {
                    Log::warning('Ticket yanit maili gonderilemedi', ['error' => $e->getMessage()]);
                }
            }
        } else {
            $this->notifyAdmin(new TicketRepliedMail($ticket, $message));
        }

        return $message;
    }

    public function close(Ticket $ticket): void
    {
        $ticket->forceFill([
            'status' => TicketStatus::Closed,
            'closed_at' => now(),
        ])->save();
    }

    public function reopen(Ticket $ticket): void
    {
        $ticket->forceFill([
            'status' => TicketStatus::Open,
            'closed_at' => null,
            'last_reply_at' => now(),
        ])->save();
    }

    private function notifyAdmin(TicketOpenedMail|TicketRepliedMail $mail): void
    {
        $adminEmail = SystemSetting::get('notification_email');

        if (! $adminEmail) {
            return;
        }

        try {
            Mail::to($adminEmail)->queue($mail);
        } catch (\Throwable $e) {
            Log::warning('Ticket admin bildirimi gonderilemedi', ['error' => $e->getMessage()]);
        }
    }
}
